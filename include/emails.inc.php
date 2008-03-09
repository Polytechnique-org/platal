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

function fix_bestalias($uid)
{
    $res = XDB::query("SELECT COUNT(*) FROM aliases WHERE id={?} AND FIND_IN_SET('bestalias',flags) AND type!='homonyme'", $uid);
    if ($n = $res->fetchOneCell()) {
        return;
    }
    XDB::execute("UPDATE  aliases
                     SET  flags=CONCAT(flags,',','bestalias')
                   WHERE  id={?} AND type!='homonyme'
                ORDER BY  !FIND_IN_SET('usage',flags),alias LIKE '%.%', LENGTH(alias)
                   LIMIT  1", $uid);
}

// function valide_email() {{{1

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

class Bogo
{
    // properties {{{2

    private $state;
    private $_states = Array('let_spams', 'tag_spams', 'tag_and_drop_spams', 'drop_spams');

    // constructor {{{2

    public function __construct($uid)
    {
        if (!$uid) {
            return;
        }
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

    public function change($uid, $state)
    {
        $this->state = is_int($state) ? $this->_states[$state] : $state;
        XDB::execute('UPDATE emails SET email={?} WHERE uid={?} AND flags = "filter"',
            $this->state, $uid);
    }

    // pubic function level() {{{2

    public function level()
    {
        return array_search($this->state, $this->_states);
    }
}

// class Email {{{1

class Email
{
    // properties {{{2

    public $email;
    public $active;
    public $broken;
    public $disabled;
    public $rewrite;
    public $panne;
    public $last;
    public $panne_level;

    // constructor {{{2

    public function __construct($row)
    {
        list($this->email, $flags, $this->rewrite, $this->panne, $this->last, $this->panne_level) = $row;
        $this->active   = ($flags == 'active');
        $this->broken   = ($flags == 'panne');
        $this->disabled = ($flags == 'disable');
    }

    // public function activate() {{{2

    public function activate($uid)
    {
        if (!$this->active) {
            XDB::execute("UPDATE  emails
                             SET  panne_level = IF(flags = 'panne', panne_level - 1, panne_level),
                                  flags = 'active'
                           WHERE  uid={?} AND email={?}", $uid, $this->email);
            $_SESSION['log']->log("email_on", $this->email.($uid!=S::v('uid') ? "(admin on $uid)" : ""));
            $this->active = true;
            $this->broken = false;
        }
    }

    // public function deactivate() {{{2

    public function deactivate($uid)
    {
        if ($this->active) {
            XDB::execute("UPDATE  emails SET flags =''
                           WHERE  uid={?} AND email={?}", $uid, $this->email);
            $_SESSION['log']->log("email_off",$this->email.($uid!=S::v('uid') ? "(admin on $uid)" : "") );
            $this->active = false;
        }
    }

    // public function rewrite() {{{2

    public function rewrite($rew, $uid)
    {
        if ($this->rewrite == $rew) {
            return;
        }
        if (!$rew || !isvalid_email($rew)) {
            $rew = '';
        }
        XDB::execute('UPDATE emails SET rewrite={?} WHERE uid={?} AND email={?}', $rew, $uid, $this->email);
        $this->rewrite = $rew;
        return;
    }

    // function cleanErrors() {{{2

    public function cleanErrors($uid)
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
                            $uid, $this->email);
    }
}

// class Redirect {{{1

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
        $this->uid=$_uid;
        $res = XDB::iterRow("SELECT  email, flags, rewrite, panne, last, panne_level
                               FROM  emails
                              WHERE  uid = {?} AND flags != 'filter'", $_uid);
        $this->emails=Array();
        while ($row = $res->next()) {
            $this->emails[] = new Email($row);
        }
        $this->bogo = new Bogo($_uid);
    }

    // public function other_active() {{{2

    public function other_active($email)
    {
        foreach ($this->emails as $mail) {
            if ($mail->email!=$email && $mail->active) {
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
        foreach ($this->emails as $i=>$mail) {
            if ($email==$mail->email) {
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
        $this->emails[] = new Email(array($email, 'active', '', '0000-00-00', '0000-00-00', 0));

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
                $mail->activate($this->uid);
            } else {
                $mail->deactivate($this->uid);
            }
            $mail->rewrite($emails_rewrite[$mail->email], $this->uid);
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
            $allinactive &= !$mail->active || $mail->email == $email;
        }
        if ($thisone === false) {
            return ERROR_INVALID_EMAIL;
        }
        if ($allinactive || $activate) {
            $this->emails[$thisone]->activate($this->uid);
        } else {
            $this->emails[$thisone]->deactivate($this->uid);
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
                $mail->rewrite($redirect, $this->uid);
                check_redirect($this);
                return;
            }
        }
    }

    // function cleanErrors() {{{2

    public function cleanErrors($email)
    {
        foreach ($this->emails as &$mail) {
            if ($mail->email == $email) {
                check_redirect($this);
                return $mail->cleanErrors($this->uid);
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
            if ($mail->active) {
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
            if ($mail->active) {
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
}

// class MailStorage {{{1
class MailStorage {
    protected $uid;
    protected $name;
    protected $storage;

    public function __construct($_uid, $_name)
    {
        $this->uid = $_uid;
        $this->name = $_name;

        $res = XDB::query("SELECT  mail_storage
                             FROM  auth_user_md5
                            WHERE  user_id = {?}", $this->uid);
        $this->storages = new FlagSet($res->fetchOneCell());
    }

    public function disable()
    {
        $this->storages->rmFlag($this->name);
        XDB::execute("UPDATE  auth_user_md5
                         SET  mail_storage = {?}
                       WHERE  user_id = {?}", $this->storages->flags(), $this->uid);
        return true;
    }

    public function enable()
    {
        $this->storages->addFlag($this->name);
        XDB::execute("UPDATE  auth_user_md5
                         SET  mail_storage = {?}
                       WHERE  user_id = {?}", $this->storages->flags(), $this->uid);
        return true;
    }

    public function active()
    {
        return $this->storages->hasFlag($this->name);
    }
}

class MailStorageIMAP extends MailStorage {
    public function __construct($_uid)
    {
        parent::__construct($_uid, 'imap');
    }
}

class MailStorageGoogleApps extends MailStorage {
    public function __construct($_uid)
    {
        parent::__construct($_uid, 'googleapps');
    }
    
    public function disable() {
        $redirect = new Redirect(S::v('uid'));
        if (!$redirect->other_active(NULL)) {
            return false;
        }
        
        return parent::disable();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
