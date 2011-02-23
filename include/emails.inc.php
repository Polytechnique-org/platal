<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

define('SUCCESS', 1);
define('ERROR_INACTIVE_REDIRECTION', 2);
define('ERROR_INVALID_EMAIL', 3);
define('ERROR_LOOP_EMAIL', 4);

function add_to_list_alias(User $user, $local_part, $domain)
{
    Platal::assert($user !== null);

    XDB::execute('INSERT IGNORE INTO  email_virtual (email, domain, redirect, type)
                              SELECT  {?}, id, {?}, {?}
                                FROM  email_virtual_domains
                               WHERE  name = {?}',
                 $local_part, $user->forlifeEmail(), $type, $domain);
}

function delete_from_list_alias(User $user, $local_part, $domain)
{
    Platal::assert($user !== null);

    XDB::execute('DELETE  v
                    FROM  email_virtual         AS v
              INNER JOIN  email_virtual_domains AS m ON (v.domain = m.id)
              INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                   WHERE  v.email = {?} AND d.name = {?} AND v.redirect = {?} AND type = {?}',
                 $local_part, $domain, $user->forlifeEmail(), $type);
}

function list_alias_members($local_part, $domain)
{
    $emails = XDB::fetchColumn('SELECT  DISTINCT(redirect)
                                  FROM  email_virtual         AS v
                            INNER JOIN  email_virtual_domains AS m ON (v.domain = m.id)
                            INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                                 WHERE  v.email = {?} AND d.name = {?} AND type = \'user\'',
                               $local_part, $domain);

    $members = array();
    foreach ($emails as $email) {
        $members[] = User::getSilent($email);
    }

    return $members;
}

function delete_list_alias($local_part, $domain)
{
    XDB::execute('DELETE  v
                    FROM  email_virtual         AS v
              INNER JOIN  email_virtual_domains AS m ON (v.domain = m.id)
              INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                   WHERE  v.email = {?} AND d.name = {?} AND type = \'user\'',
                 $local_part, $domain);
}

function iterate_list_alias($domain)
{
    return XDB::fetchColumn('SELECT  CONCAT(v.email, \'@\', m.name)
                               FROM  email_virtual         AS v
                         INNER JOIN  email_virtual_domains AS m ON (v.domain = m.id)
                              WHERE  m.name = {?} AND v.type = \'user\'
                           GROUP BY  v.email',
                            $domain);
}

function create_list($local_part, $domain)
{
    global $globals;

    $redirect = $domain . '_' . $local_part . '+';
    foreach(array('post', 'owner', 'admin', 'bounces', 'unsubscribe') as $suffix) {
        XDB::execute('INSERT IGNORE INTO  email_virtual (email, domain, redirect, type)
                                  SELECT  {?}, id, {?}, \'list\'
                                    FROM  email_virtual_domains
                                   WHERE  name = {?}',
                     ($suffix == 'post') ? $local_part : $local_part . '-' . $suffix,
                     $redirect . $suffix . '@' . $globals->lists->redirect_domain, $domain);
    }
}

function delete_list($local_part, $domain)
{
    global $globals;

    $redirect = $domain . '_' . $local_part . '+';
    foreach(array('post', 'owner', 'admin', 'bounces', 'unsubscribe') as $suffix) {
        XDB::execute('DELETE  email_virtual
                       WHERE  redirect = {?} AND type = \'list\'',
                     $redirect . $suffix . '@' . $globals->lists->redirect_domain);
    }
}

function list_exist($local_part, $domain)
{
    return XDB::fetchOneCell('SELECT  COUNT(*)
                                FROM  email_virtual         AS v
                          INNER JOIN  email_virtual_domains AS m ON (v.domain = m.id)
                          INNER JOIN  email_virtual_domains AS d ON (m.id = d.aliasing)
                               WHERE  v.email = {?} AND d.name = {?}',
                             $local_part, $domain);
}

// function mark_broken_email() {{{1
function mark_broken_email($email, $admin = false)
{
    $email = valide_email($email);
    if (empty($email) || $email == '@') {
        return;
    }

    $user = XDB::fetchOneAssoc('SELECT  r1.uid, r1.broken_level != 0 AS broken, a.hruid, COUNT(r2.uid) AS nb_mails, a.full_name, s.email AS alias
                                  FROM  email_redirect_account AS r1
                            INNER JOIN  accounts               AS a  ON (a.uid = r1.uid)
                            INNER JOIN  email_source_account   AS s  ON (a.uid = s.uid AND s.flags = \'bestalias\')
                             LEFT JOIN  email_redirect_account AS r2 ON (a.uid = r2.uid AND r1.redirect != r2.redirect AND
                                                                         r2.broken_level = 0 AND r2.flags = \'active\' AND
                                                                         (r2.type = \'smtp\' OR r2.type = \'googleapps\'))
                                 WHERE  r1.redirect = {?}
                              GROUP BY  r1.uid', $email);

    if ($user) {
        // Mark address as broken.
        if (!$user['broken']) {
            XDB::execute('UPDATE  email_redirect_account
                             SET  broken_date = NOW(), last = NOW(), broken_level = 1
                           WHERE  redirect = {?}', $email);
        } elseif ($admin) {
            XDB::execute('UPDATE  email_redirect_account
                             SET  last = CURDATE(), broken_level = IF(broken_level > 1, 3, 2)
                           WHERE  redirect = {?} AND DATE_ADD(last, INTERVAL 14 DAY) < CURDATE()',
                         $email);
        } else {
            XDB::execute('UPDATE  email_redirect_account
                             SET  broken_level = 1
                           WHERE  redirect = {?} AND broken_level = 0', $email);
        }
    }

    return $user;
}

// function fix_bestalias() {{{1
// Checks for an existing 'bestalias' among the the current user's aliases, and
// eventually selects a new bestalias when required.
function fix_bestalias(User $user)
{
    $count = XDB::fetchOneCell('SELECT  COUNT(*)
                                  FROM  email_source_account
                                 WHERE  uid = {?} AND FIND_IN_SET(\'bestalias\', flags)',
                               $user->id());

    if ($count == 1) {
        return;
    } elseif ($count > 1) {
        // If too many bestaliases, delete the bestalias flag from all this
        // user's emails (this should never happen).
        XDB::execute("UPDATE  email_source_account
                         SET  flags = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', flags, ','), ',bestalias,', ','))
                       WHERE  uid = {?}",
                     $user->id());
    }

    // If no bestalias is selected, we choose the shortest email which is not
    // related to a usage name and contains a '.'.
    XDB::execute("UPDATE  email_source_account
                     SET  flags = CONCAT_WS(',', IF(flags = '', NULL, flags), 'bestalias')
                   WHERE  uid = {?}
                ORDER BY  NOT FIND_IN_SET('usage', flags), email LIKE '%.%', LENGTH(email)
                   LIMIT  1",
                 $user->id());
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
    if (strpos($em, '@') === false) {
        return;
    }
    list($ident, $dom) = explode('@', $em);
    if ($dom == $globals->mail->domain || $dom == $globals->mail->domain2) {
        list($ident1) = explode('_', $ident);
        list($ident) = explode('+', $ident1);
    }
    return $ident . '@' . $dom;
}

// function isvalid_email_redirection() {{{1
/** Checks if an email is a suitable redirection.
 * @param $email the email to check
 * @return BOOL
 */
function isvalid_email_redirection($email)
{
    return isvalid_email($email) &&
        !preg_match("/@(polytechnique\.(org|edu)|melix\.(org|net)|m4x\.org)$/", $email);
}

// function ids_from_mails() {{{1
// Converts an array of emails to an array of email => uid, where email is the
// given email when we found a matching user.
function ids_from_mails(array $emails)
{
    global $globals;

    // Removes duplicates, if any.
    $emails = array_unique($emails);

    // Formats and splits by domain type (locally managed or external) emails.
    $domain_emails = array();
    $other_emails  = array();
    foreach ($emails as $email) {
        if (strpos($email, '@') === false) {
            $user = $email;
            $domain = $globals->mail->domain2;
        } else {
            list($user, $domain) = explode('@', $email);
        }
        if ($domain == $globals->mail->alias_dom || $domain == $globals->mail->alias_dom2
            || $domain == $globals->mail->domain || $domain == $globals->mail->domain2) {
            list($user) = explode('+', $user);
            list($user) = explode('_', $user);
            $domain_emails[$email] = strtolower($user . '@' . $domain);
        } else {
            $other_emails[$email] = strtolower($user . '@' . $domain);
        }
    }

    // Retrieves emails from our domains.
    $domain_uids = XDB::fetchAllAssoc('email',
                                      'SELECT  email, uid
                                         FROM  email_source_account
                                        WHERE  email IN {?}',
                                      array_unique($domain_emails));

    // Retrieves emails from redirections.
    $other_uids = XDB::fetchAllAssoc('redirect',
                                     'SELECT  redirect, uid
                                        FROM  email_redirect_account
                                       WHERE  redirect IN {?}',
                                     array_unique($other_emails));

    // Associates given emails with the corresponding uid.
    $uids = array();
    foreach (array_merge($domain_emails, $other_emails) as $email => $canonical_email) {
        if (array_key_exists($canonical_email, $domain_uids)) {
            $uids[$email] = $domain_uids[$canonical_email];
        } elseif (array_key_exists($canonical_email, $other_uids)) {
            $uids[$email] = $other_uids[$canonical_email];
        }
    }

    return $uids;
}

// class Bogo {{{1
// The Bogo class represents a spam filtering level in plat/al architecture.
class Bogo
{
    private static $states = array('let_spams', 'tag_spams', 'tag_and_drop_spams', 'drop_spams');

    private $user;
    private $state;

    public function __construct(User $user)
    {
        if (!$user) {
            return;
        }

        $this->user = &$user;
        $res = XDB::query('SELECT  action
                             FROM  email_redirect_account
                            WHERE  uid = {?} AND (type = \'smtp\' OR type = \'googleapps\')',
                          $user->id());
        if ($res->numRows() == 0) {
            return;
        }
        $this->state = $res->fetchOneCell();
    }

    public function change($state)
    {
        $this->state = is_int($state) ? self::$states[$state] : $state;
        XDB::execute('UPDATE  email_redirect_account
                         SET  action = {?}
                       WHERE  uid = {?} AND (type = \'smtp\' OR type = \'googleapps\')',
                     $this->state, $this->user->id());
    }

    public function level()
    {
        return array_search($this->state, self::$states);
    }
}

// class Email {{{1
// Represents an "email address" used as final recipient for plat/al-managed
// addresses.
class Email
{
    // Lists fields to load automatically.
    static private $field_names = array('rewrite', 'type', 'action', 'broken_date', 'broken_level', 'last', 'hash', 'allow_rewrite');

    // Shortname to realname mapping for known mail storage backends.
    static private $display_names = array(
        'imap'       => 'Accès de secours aux emails (IMAP)',
        'googleapps' => 'Compte Google Apps',
    );
    static private $storage_domains = array(
        'imap'       => 'imap',
        'googleapps' => 'g'
    );

    private $user;

    // Basic email properties; $sufficient indicates if the email can be used as
    // an unique redirection; $redirect contains the delivery email address.
    public $type;
    public $sufficient;
    public $email;
    public $display_email;
    public $domain;
    public $action;

    // Redirection status properties.
    public $active;
    public $inactive;
    public $broken;
    public $disabled;
    public $rewrite;
    public $allow_rewrite;
    public $hash;

    // Redirection bounces stats.
    public $last;
    public $broken_level;
    public $broken_date;

    public function __construct(User $user, array $row)
    {
        foreach (self::$field_names as $field) {
            if (array_key_exists($field, $row)) {
                $this->$field = $row[$field];
            }
        }
        $this->email = $row['redirect'];

        if (array_key_exists($this->type, Email::$display_names)) {
            $this->display_email = self::$display_names[$this->type];
        } else {
            $this->display_email = $this->email;
        }
        foreach (array('active', 'inactive', 'broken', 'disabled') as $status) {
            $this->$status = ($status == $row['flags']);
        }
        $this->sufficient = ($this->type == 'smtp' || $this->type == 'googleapps');
        $this->user = &$user;
    }

    // Activates the email address as a redirection.
    public function activate()
    {
        if ($this->inactive) {
            XDB::execute('UPDATE  email_redirect_account
                             SET  broken_level = IF(flags = \'broken\', broken_level - 1, broken_level), flags = \'active\'
                           WHERE  uid = {?} AND redirect = {?}',
                         $this->user->id(), $this->email);
            S::logger()->log('email_on', $this->email . ($this->user->id() != S::v('uid') ? "(admin on {$this->user->login()})" : ''));
            $this->inactive = false;
            $this->active   = true;
        }
    }

    // Deactivates the email address as a redirection.
    public function deactivate()
    {
        if ($this->active) {
            XDB::execute('UPDATE  email_redirect_account
                             SET  flags = \'inactive\'
                           WHERE  uid = {?} AND redirect = {?}',
                         $this->user->id(), $this->email);
            S::logger()->log('email_off', $this->email . ($this->user->id() != S::v('uid') ? "(admin on {$this->user->login()})" : "") );
            $this->inactive = true;
            $this->active   = false;
        }
    }


    // Sets the rewrite rule for the given address.
    public function set_rewrite($rewrite)
    {
        if ($this->type != 'smtp' || $this->rewrite == $rewrite) {
            return;
        }
        if (!$rewrite || !isvalid_email($rewrite)) {
            $rewrite = '';
        }
        XDB::execute('UPDATE  email_redirect_account
                         SET  rewrite = {?}
                       WHERE  uid = {?} AND redirect = {?} AND type = \'smtp\'',
                     $rewrite, $this->user->id(), $this->email);
        $this->rewrite = $rewrite;
        if (!$this->allow_rewrite) {
            global $globals;
            if (empty($this->hash)) {
                $this->hash = rand_url_id();
                XDB::execute('UPDATE  email_redirect_account
                                 SET  hash = {?}
                               WHERE  uid = {?} AND redirect = {?} AND type = \'smtp\'',
                             $this->hash, $this->user->id(), $this->email);
            }
            $mail = new PlMailer('emails/rewrite-in.mail.tpl');
            $mail->assign('mail', $this);
            $mail->assign('user', $this->user);
            $mail->assign('baseurl', $globals->baseurl);
            $mail->assign('sitename', $globals->core->sitename);
            $mail->assign('to', $this->email);
            $mail->send($this->user->isEmailFormatHtml());
        }
    }


    // Resets the error counts associated with the redirection.
    public function clean_errors()
    {
        if ($this->type != 'smtp') {
            return;
        }
        if (!S::admin()) {
            return false;
        }
        $this->broken       = 0;
        $this->broken_level = 0;
        $this->last         = 0;
        return XDB::execute('UPDATE  email_redirect_account
                                SET  broken_level = 0, broken_date = 0, last = 0
                              WHERE  uid = {?} AND redirect = {?} AND type = \'smtp\'',
                            $this->user->id(), $this->email);
    }


    // Email backend capabilities ('rewrite' refers to From: rewrite for mails
    // forwarded by Polytechnique.org's MXs; 'removable' indicates if the email
    // can be definitively removed; 'disable' indicates if the email has a third
    // status 'disabled' in addition to 'active' and 'inactive').
    public function has_rewrite()
    {
        return ($this->type == 'smtp');
    }

    public function is_removable()
    {
        return ($this->type == 'smtp');
    }

    public function has_disable()
    {
        return true;
    }

    public function is_redirection()
    {
        return ($this->type == 'smtp');
    }

    // Returns the list of allowed storages for the @p user.
    static private function get_allowed_storages(User $user)
    {
        global $globals;
        $storages = array();

        // Google Apps storage is available for users with valid Google Apps account.
        require_once 'googleapps.inc.php';
        if ($globals->mailstorage->googleapps_domain &&
            GoogleAppsAccount::account_status($user->id()) == 'active') {
            $storages[] = 'googleapps';
        }

        // IMAP storage is always visible to administrators, and is allowed for
        // everyone when the service is marked as 'active'.
        if ($globals->mailstorage->imap_active || S::admin()) {
            $storages[] = 'imap';
        }

        return $storages;
    }

    static public function activate_storage(User $user, $storage)
    {
        Platal::assert(in_array($storage, self::get_allowed_storages($user)));

        if (!self::is_active_storage($user, $storage)) {
            global $globals;

            XDB::execute('INSERT INTO  email_redirect_account (uid, type, redirect, flags)
                               VALUES  ({?}, {?}, {?}, \'active\')',
                         $user->id(), $storage,
                         $user->hruid . '@' . self::$storage_domains[$storage] . '.' . $globals->mail->domain);
        }
    }

    static public function deactivate_storage(User $user, $storage)
    {
        if (in_array($storage, self::$storage_domains)) {
            XDB::execute('DELETE FROM  email_redirect_account
                                WHERE  uid = {?} AND type = {?}',
                         $user->id(), $storage);
            }
    }

    static public function is_active_storage(User $user, $storage)
    {
        if (!in_array($storage, self::$storage_domains)) {
            return false;
        }
        $res = XDB::fetchOneCell('SELECT  COUNT(*)
                                    FROM  email_redirect_account
                                   WHERE  uid = {?} AND type = {?} AND flags = \'active\')',
                                 $user->id(), $storage);
        return !is_null($res) && $res > 0;
    }
}
// class Redirect {{{1
// Redirect is a placeholder class for an user's active redirections (third-party
// redirection email, or Polytechnique.org mail storages).
class Redirect
{
    private $flags = 'active';
    private $user;

    public $emails;
    public $bogo;

    public function __construct(User $user)
    {
        $this->user = &$user;
        $this->bogo = new Bogo($user);

        // Adds third-party email redirections.
        $res = XDB::iterator('SELECT  redirect, rewrite, type, action, broken_date, broken_level, last, flags, hash, allow_rewrite
                                FROM  email_redirect_account
                               WHERE  uid = {?} AND type != \'homonym\'',
                            $user->id());
        $this->emails = array();
        while ($row = $res->next()) {
            $this->emails[] = new Email($user, $row);
        }
    }

    public function other_active($email)
    {
        foreach ($this->emails as $mail) {
            if ($mail->email != $email && $mail->active && $mail->sufficient) {
                return true;
            }
        }
        return false;
    }

    public function delete_email($email)
    {
        if (!$this->other_active($email)) {
            return ERROR_INACTIVE_REDIRECTION;
        }
        XDB::execute('DELETE FROM  email_redirect_account
                            WHERE  uid = {?} AND redirect = {?} AND type != \'homonym\'',
                     $this->user->id(), $email);
        S::logger()->log('email_del', $email . ($this->user->id() != S::v('uid') ? " (admin on {$this->user->login()})" : ""));
        foreach ($this->emails as $i => $mail) {
            if ($email == $mail->email) {
                unset($this->emails[$i]);
            }
        }
        check_redirect($this);
        return SUCCESS;
    }

    public function add_email($email)
    {
        $email_stripped = strtolower(trim($email));
        if (!isvalid_email($email_stripped)) {
            return ERROR_INVALID_EMAIL;
        }
        if (!isvalid_email_redirection($email_stripped)) {
            return ERROR_LOOP_EMAIL;
        }
        // If the email was already present for this user, we reset it to the default values, we thus use REPLACE INTO.
        XDB::execute('REPLACE INTO  email_redirect_account (uid, redirect, flags)
                            VALUES  ({?}, {?}, \'active\')',
                     $this->user->id(), $email);
        if ($logger = S::v('log', null)) { // may be absent --> step4.php
            S::logger()->log('email_add', $email . ($this->user->id() != S::v('uid') ? " (admin on {$this->user->login()})" : ""));
        }
        foreach ($this->emails as $mail) {
            if ($mail->email == $email_stripped) {
                return SUCCESS;
            }
        }
        $this->emails[] = new Email($this->user, array(
                'redirect'      => $email,
                'rewrite'       => '',
                'type'          => 'smtp',
                'action'        => 'default',
                'broken_date'   => '0000-00-00',
                'broken_level'  => 0,
                'last'          => '0000-00-00',
                'flags'         => 'active',
                'hash'          => null,
                'allow_rewrite' => 0
        ));

        // security stuff
        check_email($email, "Ajout d'une adresse surveillée aux redirections de " . $this->user->login());
        check_redirect($this);
        return SUCCESS;
    }

    public function modify_email($emails_actifs, $emails_rewrite)
    {
        foreach ($this->emails as &$email) {
            if (in_array($email->email, $emails_actifs)) {
                $email->activate();
            } else {
                $email->deactivate();
            }
            $email->set_rewrite($emails_rewrite[$email->email]);
        }
        check_redirect($this);
        return SUCCESS;
    }

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
        }
        return SUCCESS;
    }

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

    public function disable()
    {
        XDB::execute("UPDATE  email_redirect_account
                         SET  flags = 'disable'
                       WHERE  flags = 'active' AND uid = {?}", $this->user->id());
        foreach ($this->emails as &$mail) {
            if ($mail->active && $mail->has_disable()) {
                $mail->disabled = true;
                $mail->active   = false;
            }
        }
        check_redirect($this);
    }

    public function enable()
    {
        XDB::execute("UPDATE  email_redirect_account
                         SET  flags = 'active'
                       WHERE  flags = 'disable' AND uid = {?}", $this->user->id());
        foreach ($this->emails as &$mail) {
            if ($mail->disabled) {
                $mail->disabled = false;
                $mail->active   = true;
            }
            check_redirect($this);
        }
    }

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

    public function get_uid()
    {
        return $this->user->id();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
