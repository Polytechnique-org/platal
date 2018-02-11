<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

// Post-processes the successful Google Apps account creation queue job.
function post_queue_u_create($job) {
    global $globals;

    // Retrieves the user parameters (GoogleApps username and uid).
    $parameters = json_decode($job['j_parameters'], true);
    $username = isset($parameters['username']) ? $parameters['username'] : null;
    if (!($user = User::getSilent($username))) {
        return;
    }

    // Adds a redirection to the Google Apps delivery address, if requested by
    // the user at creation time.
    $account = new GoogleAppsAccount($user);
    if ($account->activate_mail_redirection) {
        require_once 'emails.inc.php';
        Email::activate_storage($user, 'googleapps');
    }

    // Sends the 'account created' email to the user, with basic documentation.
    $mailer = new PlMailer('googleapps/create.mail.tpl');
    $mailer->assign('account', $account);
    $mailer->assign('email', $user->bestEmail());
    $mailer->assign('googleapps_domain', $globals->mailstorage->googleapps_domain);
    $mailer->assign('prenom', $user->displayName());
    $mailer->assign('sexe', $user->isFemale());
    $mailer->send();
}

// Post-processes the successful Google Apps account update queue job.
function post_queue_u_update($job) {
    global $globals;

    // If the u_update job was an unsuspend request, re-adds the redirection
    // to the Google Apps delivery address, provided the account is active (it might
    // have been deleted between the unsuspension and the post-queue processing).
    $parameters = json_decode($job['j_parameters'], true);
    $username = isset($parameters['username']) ? $parameters['username'] : null;
    if (!($user = User::getSilent($username))) {
        return;
    }

    if (isset($parameters['suspended']) && $parameters['suspended'] == false) {
        require_once 'emails.inc.php';
        $account = new GoogleAppsAccount($user);
        if ($account->active()) {
            // Re-adds the email redirection (if the user did request it).
            if ($account->activate_mail_redirection) {
                Email::activate_storage($user, 'googleapps');
            }

            // Sends an email to the account owner.
            $mailer = new PlMailer('googleapps/unsuspend.mail.tpl');
            $mailer->assign('account', $account);
            $mailer->assign('email', $user->bestEmail());
            $mailer->assign('prenom', $user->displayName());
            $mailer->assign('sexe', $user->isFemale());
            $mailer->send();
        }
    }
}

// Reprensentation of an SQL-stored Google Apps account.
// This class is the interface with the gappsd SQL tables: gappsd is the python
// daemon which deals with Google Apps provisioning APIs.
// TODO(vincent.zanotti): add the url of gappsd, when available.
class GoogleAppsAccount
{
    // User identification: user id, and hruid.
    private $user;
    public $g_account_name;

    // Local account parameters.
    public $sync_password;
    public $activate_mail_redirection;

    // Account status, obtained from Google Apps provisioning & reporting APIs.
    public $g_account_id;
    public $g_status;
    public $g_suspension;
    public $r_disk_usage;
    public $r_creation;
    public $r_last_login;
    public $r_last_webmail;
    public $reporting_date;

    // Nicknames (aliases) registered for that user, lazily loaded.
    public $nicknames;

    // Pending requests in the gappsd job queue (cf. top note).
    public $pending_create;
    public $pending_delete;
    public $pending_update;
    public $pending_update_admin;
    public $pending_update_other;
    public $pending_update_password;
    public $pending_update_suspension;

    // Pending requests in plat/al validation queue.
    public $pending_validation_unsuspend;

    // Constructs the account object, by retrieving all informations from the
    // GApps account table, from GApps job queue, and from plat/al validation queue.
    public function __construct(User $user)
    {
        $this->user = &$user;
        if (!$this->user || !$this->user->login()) {
            return;
        }

        // TODO: switch to multi-domain Google Apps, and use $this->user->forlifeEmail()
        // as Google Apps idenfiant (requires changes in gappsd).
        $this->g_account_name = $this->user->login();
        $this->g_status = NULL;

        $res = XDB::query(
            "SELECT  l_sync_password, l_activate_mail_redirection,
                     g_account_name, g_account_id, g_status, g_suspension, r_disk_usage,
                     UNIX_TIMESTAMP(r_creation) as r_creation,
                     UNIX_TIMESTAMP(r_last_login) as r_last_login,
                     UNIX_TIMESTAMP(r_last_webmail) as r_last_webmail
               FROM  gapps_accounts
              WHERE  g_account_name = {?}", $this->g_account_name);
        if ($account = $res->fetchOneAssoc()) {
            $this->sync_password = $account['l_sync_password'];
            $this->activate_mail_redirection = $account['l_activate_mail_redirection'];
            $this->g_account_id = $account['g_account_id'];
            $this->g_status = $account['g_status'];
            $this->g_suspension = $account['g_suspension'];
            $this->r_disk_usage = $account['r_disk_usage'];
            $this->r_creation = $account['r_creation'];
            $this->r_last_login = $account['r_last_webmail'];
            $this->r_last_webmail = $account['r_last_webmail'];

            $this->load_pending_counts();
            $this->load_pending_validations();
            if ($this->pending_update) {
                $this->load_pending_updates();
            }

            $res = XDB::query("SELECT MAX(date) FROM gapps_reporting");
            $this->reporting_date = $res->fetchOneCell();
        }
    }

    // Determines if changes to the Google Account are currently waiting in the
    // GApps job queue, and initializes the local values accordingly.
    private function load_pending_counts()
    {
        $res = XDB::query(
            "SELECT  SUM(j_type = 'u_create') AS pending_create,
                     SUM(j_type = 'u_update') AS pending_update,
                     SUM(j_type = 'u_delete') AS pending_delete
               FROM  gapps_queue
              WHERE  q_recipient_id = {?} AND
                     p_status IN ('idle', 'active', 'softfail')
           GROUP BY  j_type", $this->user->id());
        $pending = $res->fetchOneAssoc();
        $this->pending_create = $pending['pending_create'];
        $this->pending_update = $pending['pending_update'];
        $this->pending_delete = $pending['pending_delete'];

        $this->pending_update_admin = false;
        $this->pending_update_other = false;
        $this->pending_update_password = false;
        $this->pending_update_suspension = false;
    }

    // Checks for unsuspend requests waiting for validation in plat/al
    // validation queue.
    private function load_pending_validations()
    {
        $this->pending_validation_unsuspend =
            Validate::get_typed_requests_count($this->user->id(), 'gapps-unsuspend');
    }

    // Retrieves all the pending update job in the gappsd queue for the current
    // user, and analyzes the scope of the update (ie. the fields in the user
    // account which are going to be updated).
    private function load_pending_updates()
    {
        $res = XDB::iterator(
            "SELECT  j_parameters
               FROM  gapps_queue
              WHERE  q_recipient_id = {?} AND
                     p_status IN ('idle', 'active', 'softfail') AND
                     j_type = 'u_update'", $this->user->id());
        while ($update = $res->next()) {
            $update_data = json_decode($update["j_parameters"], true);

            if (isset($update_data["suspended"])) {
                $this->pending_update_suspension = true;
            } elseif (isset($update_data["password"])) {
                $this->pending_update_password = true;
            } elseif (isset($update_data["admin"])) {
                $this->pending_update_admin = true;
            } else {
                $this->pending_update_other = true;
            }
        }
    }

    // Creates a queue job of the @p type, for the user represented by this
    // GoogleAppsAccount object, using @p parameters. @p parameters is supposed
    // to be a one-dimension array of key-value mappings.
    // The created job as a 'immediate' priority, and is scheduled for immediate
    // execution.
    private function create_queue_job($type, $parameters) {
        $parameters["username"] = $this->g_account_name;
        XDB::execute(
            "INSERT  INTO gapps_queue
                SET  q_owner_id = {?}, q_recipient_id = {?},
                     p_entry_date = NOW(), p_notbefore_date = NOW(),
                     p_priority = 'immediate',
                     j_type = {?}, j_parameters = {?}",
            S::v('uid'),
            $this->user->id(),
            $type,
            json_encode($parameters));
    }


    // Returns true if the account is currently active.
    public function active()
    {
        return $this->g_status == 'active';
    }

    // Returns true if the account exists in Google Apps.
    public function provisioned()
    {
        return $this->g_status == 'active' or $this->g_status == 'disabled';
    }

    // Returns true if the account exists, but cannot be used (user-requested
    // suspension, or Google-requested suspension).
    public function suspended()
    {
        return $this->g_status == 'disabled';
    }

    // Loads and returns the list of nicknames for the user.
    public function nicknames()
    {
        if ($this->nicknames == null) {
            $res = XDB::query(
                "SELECT  g_nickname
                   FROM  gapps_nicknames
                  WHERE  g_account_name = {?}
               ORDER BY  g_nickname",
                $this->g_account_name);
            $this->nicknames = $res->fetchColumn();
        }
        return $this->nicknames;
    }


    // Changes the GoogleApps password.
    public function set_password($password) {
        if (!$this->provisioned()) {
            return;
        }

        if (!$this->pending_update_password) {
            $this->create_queue_job('u_update', array('password' => $password));
            $this->pending_update_password = true;
        }
    }


    // Changes the password synchronization status ("sync = true" means that the
    // Polytechnique.org password will be replicated to the Google Apps account).
    public function set_password_sync($sync) {
        if (!$this->provisioned()) {
            return;
        }

        $this->sync_password = $sync;
        XDB::execute(
            "UPDATE  gapps_accounts
                SET  l_sync_password = {?}
              WHERE  g_account_name = {?}",
            $sync,
            $this->g_account_name);
    }

    // Suspends the Google Apps account.
    public function suspend() {
        if (!$this->provisioned()) {
            return;
        }

        if (!$this->pending_update_suspension) {
            $this->create_queue_job('u_update', array('suspended' => true));
            $this->pending_update_suspension = true;
            XDB::execute(
                "UPDATE  gapps_accounts
                    SET  g_status = 'disabled'
                  WHERE  g_account_name = {?} AND g_status = 'active'",
                $this->g_account_name);
        }
    }

    // Adds an unsuspension request to the validation queue (used on user-request).
    public function unsuspend($activate_mail_redirection = NULL) {
        if (!$this->provisioned()) {
            return;
        }
        if ($activate_mail_redirection !== NULL) {
            $this->activate_mail_redirection = $activate_mail_redirection;
            XDB::execute(
                "UPDATE  gapps_accounts
                    SET  l_activate_mail_redirection = {?}
                  WHERE  g_account_name = {?}",
                $activate_mail_redirection,
                $this->g_account_name);
        }

        if (!$this->pending_update_suspension && !$this->pending_validation_unsuspend) {
            $unsuspend = new GoogleAppsUnsuspendReq($this->user);
            $unsuspend->submit();
            $this->pending_validation_unsuspend = true;
        }
    }

    // Unsuspends the Google Apps account (used on admin-request, or on validation of
    // an user-request).
    public function do_unsuspend() {
        if (!$this->provisioned()) {
            return;
        }

        if (!$this->pending_update_suspension) {
            if ($this->sync_password) {
                $this->create_queue_job('u_update', array('suspended' => false, 'password' => $this->user->password()));
            } else {
                $this->create_queue_job('u_update', array('suspended' => false));
            }
            $this->pending_update_suspension = true;
            return true;
        }
        return false;
    }

    // Creates a new Google Apps account with the @p local parameters.
    public function create($password_sync, $password, $redirect_mails)
    {
        if ($this->g_status != NULL) {
            return;
        }

        if (!$this->pending_create) {
            // Retrieves information on the new account.
            if (!$this->user->hasProfile()) {
                $prenom = $this->user->displayName();
                $nom    = $this->user->fullName();
            } else {
                $prenom = $this->user->profile()->firstName();
                $nom    = $this->user->profile()->lastName();
            }

            // Adds an 'unprovisioned' entry in the gapps_accounts table.
            XDB::execute(
                "INSERT  INTO gapps_accounts
                    SET  l_userid = {?},
                         l_sync_password = {?},
                         l_activate_mail_redirection = {?},
                         g_account_name = {?},
                         g_first_name = {?},
                         g_last_name = {?},
                         g_status = 'unprovisioned'",
                $this->user->id(),
                $password_sync,
                $redirect_mails,
                $this->g_account_name,
                $prenom, $nom);

            // Adds the creation job in the GApps queue.
            $this->create_queue_job(
                'u_create',
                array(
                    'username' => $this->g_account_name,
                    'first_name' => $prenom,
                    'last_name' => $nom,
                    'password' => $password,
                ));

            // Updates the GoogleAppsAccount status.
            $this->__construct($this->user);
        }
    }


    // Returns the status of the Google Apps account for @p user, or false
    // when no account exists.
    static public function account_status($uid) {
        $res = XDB::query(
            "SELECT  g_status
               FROM  gapps_accounts
              WHERE  l_userid = {?}", $uid);
        return ($res->numRows() > 0 ? $res->fetchOneCell() : false);
    }

    // Returns true if the @p user is an administrator of the Google Apps domain.
    static public function is_administrator($uid) {
        $res = XDB::query(
            "SELECT  g_admin
               FROM  gapps_accounts
              WHERE  l_userid = {?} AND g_status = 'active'", $uid);
        return ($res->numRows() > 0 ? (bool)$res->fetchOneCell() : false);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
