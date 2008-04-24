<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

require_once("xorg.misc.inc.php");

define("SUCCESS", 1);
define("ERROR_INACTIVE_REDIRECTION", 2);
define("ERROR_INVALID_EMAIL", 3);
define("ERROR_LOOP_EMAIL", 4);

// function fix_bestalias() {{{1
// Checks for an existing 'bestalias' among the the current user's aliases, and
// eventually selects a new bestalias when required.
function fix_bestalias($uid)
{
    $res = XDB::query("SELECT  COUNT(*)
                         FROM  aliases
                        WHERE  id = {?} AND FIND_IN_SET('bestalias', flags) AND type != 'homonyme'",
                      $uid);
    if ($res->fetchOneCell()) {
        return;
    }

    XDB::execute("UPDATE  aliases
                     SET  flags=CONCAT(flags,',','bestalias')
                   WHERE  id={?} AND type!='homonyme'
                ORDER BY  !FIND_IN_SET('usage',flags),alias LIKE '%.%', LENGTH(alias)
                   LIMIT  1", $uid);
}

// function valide_email() {{{1
// Returns a cleaned-up version of the @p email string. It removes garbage
// characters, and determines the canonical form (without _ and +) for
// Polytechnique.org email addresses.
function valide_email($str)
{
    global $globals;

    $em = trim(rtrim($str));
    $em = str_replace('<', '', $em);
    $em = str_replace('>', '', $em);
    list($ident, $dom) = explode('@', $em);
    if ($dom == $globals->mail->domain or $dom == $globals->mail->domain2) {
        list($ident1) = explode('_', $ident);
        list($ident) = explode('+', $ident1);
    }
    return $ident . '@' . $dom;
}

// class Bogo {{{1
// The Bogo class represents a spam filtering level in plat/al architecture.
class Bogo
{
    // properties {{{2

    private $uid;
    private $state;
    private $_states = Array('let_spams', 'tag_spams', 'tag_and_drop_spams', 'drop_spams');

    // constructor {{{2

    public function __construct($uid)
    {
        if (!$uid) {
            return;
        }

        $this->uid = $uid;
        $res = XDB::query('SELECT email FROM emails WHERE uid={?} AND flags="filter"', $uid);
        if ($res->numRows()) {
            $this->state = $res->fetchOneCell();
        } else {
            $this->state = 'tag_and_drop_spams';
            $res = XDB::query("INSERT INTO emails (uid,email,rewrite,panne,flags)
                                    VALUES ({?},'tag_and_drop_spams','','0000-00-00','filter')", $uid);
        }
    }

    // public function change() {{{2

    public function change($state)
    {
        $this->state = is_int($state) ? $this->_states[$state] : $state;
        XDB::execute('UPDATE emails SET email={?} WHERE uid={?} AND flags = "filter"',
                     $this->state, $this->uid);
    }

    // pubic function level() {{{2

    public function level()
    {
        return array_search($this->state, $this->_states);
    }
}

// class Email {{{1
// Represents an "email address" used as final recipient for plat/al-managed
// addresses; it can be subclasses a Redirection emails (third-party) or as
// Storage emails (Polytechnique.org).
abstract class Email
{
    protected $uid;

    // Basic email properties; $sufficient indicates if the email can be used as
    // an unique redirection; $email contains the delivery email address.
    public $type;
    public $sufficient;
    public $email;
    public $display_email;

    // Redirection status properties.
    public $active;
    public $broken;
    public $disabled;
    public $rewrite;

    // Redirection bounces stats.
    public $panne;
    public $last;
    public $panne_level;

    // Activates the email address as a redirection.
    public abstract function activate();

    // Deactivates the email address as a redirection.
    public abstract function deactivate();

    // Sets the rewrite rule for the given address.
    public abstract function set_rewrite($rewrite);

    // Resets the error counts associated with the redirection.
    public abstract function clean_errors();

    // Email backend capabilities ('rewrite' refers to From: rewrite for mails
    // forwarded by Polytechnique.org's MXs; 'removable' indicates if the email
    // can be definitively removed; 'disable' indicates if the email has a third
    // status 'disabled' in addition to 'active' and 'inactive').
    public abstract function has_rewrite();
    public abstract function is_removable();
    public abstract function has_disable();
}

// class EmailRedirection {{{1
// Implementation of Email for third-party redirection (redirection of emails to
// external user-supplied addresses).
class EmailRedirection extends Email
{
    // constructor {{{2

    public function __construct($uid, $row)
    {
        $this->uid = $uid;
        $this->sufficient = true;

        list($this->email, $flags, $this->rewrite, $this->panne, $this->last, $this->panne_level) = $row;
        $this->display_email = $this->email;
        $this->active   = ($flags == 'active');
        $this->broken   = ($flags == 'panne');
        $this->disabled = ($flags == 'disable');
    }

    // public function activate() {{{2

    public function activate()
    {
        if (!$this->active) {
            XDB::execute("UPDATE  emails
                             SET  panne_level = IF(flags = 'panne', panne_level - 1, panne_level),
                                  flags = 'active'
                           WHERE  uid={?} AND email={?}", $this->uid, $this->email);
            $_SESSION['log']->log("email_on", $this->email.($this->uid!=S::v('uid') ? "(admin on {$this->uid})" : ""));
            $this->active = true;
            $this->broken = false;
        }
    }

    // public function deactivate() {{{2

    public function deactivate()
    {
        if ($this->active) {
            XDB::execute("UPDATE  emails SET flags =''
                           WHERE  uid={?} AND email={?}", $this->uid, $this->email);
            $_SESSION['log']->log("email_off",$this->email.($this->uid != S::v('uid') ? "(admin on {$this->uid})" : "") );
            $this->active = false;
        }
    }

    // public function set_rewrite() {{{2

    public function set_rewrite($rewrite)
    {
        if ($this->rewrite == $rewrite) {
            return;
        }
        if (!$rewrite || !isvalid_email($rewrite)) {
            $rewrite = '';
        }
        XDB::execute('UPDATE emails SET rewrite={?} WHERE uid={?} AND email={?}', $rewrite, $this->uid, $this->email);
        $this->rewrite = $rewrite;
        return;
    }

    // public function clean_errors() {{{2

    public function clean_errors()
    {
        if (!S::has_perms()) {
            return false;
        }
        $this->panne       = 0;
        $this->panne_level = 0;
        $this->last        = 0;
        return XDB::execute("UPDATE  emails
                                SET  panne_level = 0, panne = 0, last = 0
                              WHERE  uid = {?} AND email = {?}",
                            $this->uid, $this->email);
    }

    // public function has_rewrite() {{{2

    public function has_rewrite()
    {
        return true;
    }

    // public function is_removable() {{{2

    public function is_removable()
    {
        return true;
    }

    // public function has_disable() {{{2

    public function has_disable()
    {
        return true;
    }
}

// class EmailStorage {{{1
// Implementation of Email for email storage backends from Polytechnique.org.
class EmailStorage extends Email
{
    // Shortname to realname mapping for known mail storage backends.
    private $display_names = array(
        'imap'       => 'Accès de secours aux emails (IMAP)',
        'googleapps' => 'Compte Google Apps',
    );

    // Retrieves the current list of actives storages.
    private function get_storages()
    {
        $res = XDB::query("SELECT  mail_storage
                             FROM  auth_user_md5
                            WHERE  user_id = {?}", $this->uid);
        return new FlagSet($res->fetchOneCell());
    }

    // Updates the list of active storages.
    private function set_storages($storages)
    {
        XDB::execute("UPDATE  auth_user_md5
                         SET  mail_storage = {?}
                       WHERE  user_id = {?}", $storages->flags(), $this->uid);
    }

    // Returns the list of allowed storages for the @p user.
    static public function get_allowed_storages($uid)
    {
        global $globals;
        $storages = array();

        // Google Apps storage is available for users with valid Google Apps account.
        require_once 'googleapps.inc.php';
        if ($globals->mailstorage->googleapps_domain &&
            GoogleAppsAccount::account_status($uid) == 'active') {
            $storages[] = 'googleapps';
        }

        // IMAP storage is always visible to administrators, and is allowed for
        // everyone when the service is marked as 'active'.
        if ($globals->mailstorage->imap_active || S::has_perms()) {
            $storages[] = 'imap';
        }

        return $storages;
    }


    public function __construct($uid, $name)
    {
        $this->uid = $uid;
        $this->email = $name;
        $this->display_email = (isset($this->display_names[$name]) ? $this->display_names[$name] : $name);

        $storages = $this->get_storages();
        $this->sufficient = ($name == 'googleapps');
        $this->active = $storages->hasFlag($name);
        $this->broken = false;
        $this->disabled = false;
        $this->rewrite = '';
        $this->panne = $this->last = $this->panne_level = 0;
    }

    public function activate()
    {
        if (!$this->active) {
            $storages = $this->get_storages();
            $storages->addFlag($this->email);
            $this->set_storages($storages);
            $this->active = true;
        }
    }

    public function deactivate()
    {
        if ($this->active) {
            $storages = $this->get_storages();
            $storages->rmFlag($this->email);
            $this->set_storages($storages);
            $this->active = false;
        }

    }

    // Source rewrite can't be enabled for email storage addresses.
    public function set_rewrite($rewrite) {}

    // Email storage are not supposed to be broken, hence not supposed to be
    // cleaned-up.
    public function clean_errors() {}

    // Capabilities.
    public function has_rewrite() { return false; }
    public function is_removable() { return false; }
    public function has_disable() { return false; }
}

// class Redirect {{{1
// Redirect is a placeholder class for an user's active redirections (third-party
// redirection email, or Polytechnique.org mail storages).
class Redirect
{
    // properties {{{2

    private $flag_active = 'active';
    private $uid;

    public $emails;
    public $bogo;

    // constructor {{{2

    public function __construct($_uid)
    {
        $this->uid = $_uid;
        $this->bogo = new Bogo($_uid);

        // Adds third-party email redirections.
        $res = XDB::iterRow("SELECT  email, flags, rewrite, panne, last, panne_level
                               FROM  emails
                              WHERE  uid = {?} AND flags != 'filter'", $_uid);
        $this->emails = Array();
        while ($row = $res->next()) {
            $this->emails[] = new EmailRedirection($_uid, $row);
        }

        // Adds local email storage backends.
        foreach (EmailStorage::get_allowed_storages($_uid) as $storage) {
            $this->emails[] = new EmailStorage($_uid, $storage);
        }
    }

    // public function other_active() {{{2

    public function other_active($email)
    {
        foreach ($this->emails as $mail) {
            if ($mail->email != $email && $mail->active && $mail->sufficient) {
                return true;
            }
        }
        return false;
    }

    // public function delete_email() {{{2

    public function delete_email($email)
    {
        if (!$this->other_active($email)) {
            return ERROR_INACTIVE_REDIRECTION;
        }
        XDB::execute('DELETE FROM emails WHERE uid={?} AND email={?}', $this->uid, $email);
        $_SESSION['log']->log('email_del',$email.($this->uid!=S::v('uid') ? " (admin on {$this->uid})" : ""));
        foreach ($this->emails as $i => $mail) {
            if ($email == $mail->email) {
                unset($this->emails[$i]);
            }
        }
        check_redirect($this);
        return SUCCESS;
    }

    // public function add_email() {{{2

    public function add_email($email)
    {
        $email_stripped = strtolower(trim($email));
        if (!isvalid_email($email_stripped)) {
            return ERROR_INVALID_EMAIL;
        }
        if (!isvalid_email_redirection($email_stripped)) {
            return ERROR_LOOP_EMAIL;
        }
        XDB::execute('REPLACE INTO emails (uid,email,flags) VALUES({?},{?},"active")', $this->uid, $email);
        if ($logger = S::v('log', null)) { // may be absent --> step4.php
            $logger->log('email_add',$email.($this->uid!=S::v('uid') ? " (admin on {$this->uid})" : ""));
        }
        foreach ($this->emails as $mail) {
            if ($mail->email == $email_stripped) {
                return SUCCESS;
            }
        }
        $this->emails[] = new EmailRedirection($this->uid, array($email, 'active', '', '0000-00-00', '0000-00-00', 0));

        // security stuff
        check_email($email, "Ajout d'une adresse surveillée aux redirections de " . $this->uid);
        check_redirect($this);
        return SUCCESS;
    }

    // public function modify_email() {{{2

    public function modify_email($emails_actifs, $emails_rewrite)
    {
        foreach ($this->emails as &$mail) {
            if (in_array($mail->email, $emails_actifs)) {
                $mail->activate();
            } else {
                $mail->deactivate();
            }
            $mail->set_rewrite($emails_rewrite[$mail->email]);
        }
        check_redirect($this);
    }

    // public function modify_one_email() {{{2

    public function modify_one_email($email, $activate)
    {
        $allinactive = true;
        $thisone = false;
        foreach ($this->emails as $i=>$mail) {
            if ($mail->email == $email) {
                $thisone = $i;
            }
            $allinactive &= !$mail->active || !$mail->sufficient || $mail->email == $email;
        }
        if ($thisone === false) {
            return ERROR_INVALID_EMAIL;
        }
        if ($allinactive || $activate) {
            $this->emails[$thisone]->activate();
        } else {
            $this->emails[$thisone]->deactivate();
        }
        check_redirect($this);
        if ($allinactive && !$activate) {
            return ERROR_INACTIVE_REDIRECTION;
        } else {
            return SUCCESS;
        }
    }

    // public function modify_one_email_redirect() {{{2

    public function modify_one_email_redirect($email, $redirect)
    {
        foreach ($this->emails as &$mail) {
            if ($mail->email == $email) {
                $mail->set_rewrite($redirect);
                check_redirect($this);
                return;
            }
        }
    }

    // function clean_errors() {{{2

    public function clean_errors($email)
    {
        foreach ($this->emails as &$mail) {
            if ($mail->email == $email) {
                check_redirect($this);
                return $mail->clean_errors();
            }
        }
        return false;
    }

    // function disable() {{{2

    public function disable()
    {
        XDB::execute("UPDATE  emails
                         SET  flags = 'disable'
                       WHERE  flags = 'active' AND uid = {?}", $this->uid);
        foreach ($this->emails as &$mail) {
            if ($mail->active && $mail->has_disable()) {
                $mail->disabled = true;
                $mail->active   = false;
            }
        }
        check_redirect($this);
    }

    // function enable() {{{2

    public function enable()
    {
        XDB::execute("UPDATE  emails
                         SET  flags = 'active'
                       WHERE  flags = 'disable' AND uid = {?}", $this->uid);
        foreach ($this->emails as &$mail) {
            if ($mail->disabled) {
                $mail->active   = true;
                $mail->disabled = false;
            }
            check_redirect($this);
        }
    }

    // function get_broken_mx() {{{2

    public function get_broken_mx()
    {
        $res = XDB::query("SELECT  host, text
                             FROM  mx_watch
                            WHERE  state != 'ok'");
        if (!$res->numRows()) {
            return array();
        }
        $mxs = $res->fetchAllAssoc();
        $mails = array();
        foreach ($this->emails as &$mail) {
            if ($mail->active && strstr($mail->email, '@') !== false) {
                list(,$domain) = explode('@', $mail->email);
                getmxrr($domain, $lcl_mxs);
                if (empty($lcl_mxs)) {
                    $lcl_mxs = array($domain);
                }
                $broken = false;
                foreach ($mxs as &$mx) {
                    foreach ($lcl_mxs as $lcl) {
                        if (fnmatch($mx['host'], $lcl)) {
                            $broken = $mx['text'];
                            break;
                        }
                    }
                    if ($broken) {
                        $mails[] = array('mail' => $mail->email, 'text' => $broken);
                        break;
                    }
                }
            }
        }
        return $mails;
    }

    // function active_emails() {{{2

    public function active_emails()
    {
        $emails = array();
        foreach ($this->emails as $mail) {
            if ($mail->active) {
                $emails[] = $mail;
            }
        }
        return $emails;
    }

    // function get_uid() {{{2

    public function get_uid()
    {
        return $this->uid;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
