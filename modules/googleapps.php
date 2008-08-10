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

class GoogleAppsModule extends PLModule
{
    function handlers()
    {
        global $globals;
        if (!$globals->mailstorage->googleapps_domain) {
            return array();
        }

        return array(
            'googleapps'            => $this->make_hook('index', AUTH_MDP),
            'admin/googleapps'      => $this->make_hook('admin', AUTH_MDP. 'admin'),
            'admin/googleapps/job'  => $this->make_hook('admin_job', AUTH_MDP, 'admin'),
            'admin/googleapps/user' => $this->make_hook('admin_user', AUTH_MDP, 'admin'),
        );
    }

    function handler_index(&$page, $action = null, $subaction = null)
    {
        require_once("emails.inc.php");
        require_once("googleapps.inc.php");
        $page->changeTpl('googleapps/index.tpl');
        $page->addJsLink('motdepasse.js');
        $page->setTitle('Compte Google Apps');

        $account = new GoogleAppsAccount(S::v('uid'), S::v('forlife'));

        // Fills up the 'is Google Apps redirection active' variable.
        $page->assign('redirect_active', false);
        $page->assign('redirect_unique', true);

        if ($account->active()) {
            $redirect = new Redirect(S::v('uid'));
            $page->assign('redirect_unique', !$redirect->other_active('googleapps'));

            $storage = new EmailStorage(S::v('uid'), 'googleapps');
            $page->assign('redirect_active', $storage->active);
        }

        // Updates the Google Apps account as required.
        if ($action) {
            if ($action == 'password') {
                if ($subaction == 'sync') {
                    $account->set_password_sync(true);
                    $account->set_password(S::v('password'));
                    pl_redirect('googleapps#password');
                } else if ($subaction == 'nosync') {
                    $account->set_password_sync(false);
                } else if (Post::has('response2') && !$account->sync_password) {
                    S::assert_xsrf_token();
                    $account->set_password(Post::v('response2'));
                }
            }

            if ($action == 'suspend' && Post::has('suspend') && $account->active()) {
                S::assert_xsrf_token();

                if ($account->pending_update_suspension) {
                    $page->trigWarning("Ton compte est déjà en cours de désactivation.");
                } else {
                    if ($redirect->modify_one_email('googleapps', false) == SUCCESS) {
                        $account->suspend();
                        $page->trigSuccess("Ton compte Google Apps est dorénavant désactivé.");
                    } else {
                        $page->trigError("Ton compte Google Apps est ta seule adresse de redirection. Ton compte ne peux pas être désactivé.");
                    }
                }
            } elseif ($action == 'unsuspend' && Post::has('unsuspend') && $account->suspended()) {
                $account->unsuspend(Post::b('redirect_mails', true));
                $page->trigSuccess("Ta demande de réactivation a bien été prise en compte.");
            }

            if ($action == 'create') {
                $page->assign('has_password_sync', Get::has('password_sync'));
                $page->assign('password_sync', Get::b('password_sync', true));
            }
            if ($action == 'create' && Post::has('password_sync') && Post::has('redirect_mails')) {
                S::assert_xsrf_token();

                $password_sync = Post::b('password_sync');
                $redirect_mails = Post::b('redirect_mails');
                if ($password_sync) {
                    $password = S::v('password');
                } else {
                    $password = Post::v('response2');
                }

                $account->create($password_sync, $password, $redirect_mails);
                $page->trigSuccess("La demande de création de ton compte Google Apps a bien été enregistrée.");
            }
        }

        $page->assign('account', $account);
    }

    function handler_admin(&$page, $action = null) {
        require_once("googleapps.inc.php");
        $page->changeTpl('googleapps/admin.tpl');
        $page->setTitle('Administration Google Apps');
        $page->assign('googleapps_admin', GoogleAppsAccount::is_administrator(S::v('uid')));

        if ($action == 'ack') {
            $qid = @func_get_arg(2);
            if ($qid) {
                XDB::execute(
                    "DELETE FROM  gapps_queue
                           WHERE  q_id = {?} AND p_status = 'hardfail'", $qid);
                $page->trigSuccess("La requête échouée a bien été retirée.");
            }
        }

        // Retrieves latest pending administrative requests from the gappsd queue.
        $res = XDB::iterator(
            "SELECT  q_id, q_recipient_id, a.alias, j_type, j_parameters,
                     UNIX_TIMESTAMP(q.p_entry_date) AS p_entry_date
               FROM  gapps_queue AS q
          LEFT JOIN  aliases AS a ON (a.id = q_recipient_id AND a.type = 'a_vie')
              WHERE  p_status IN ('idle', 'active', 'softfail') AND
                     p_admin_request IS TRUE
           ORDER BY  p_entry_date");
        while ($request = $res->next()) {
            $j_parameters = json_decode($request['j_parameters'], true);
            unset($j_parameters['username']);
            $parameters = array_keys($j_parameters);
            $request['parameters'] = implode(', ', $parameters);

            $page->append('admin_requests', $request);
        }

        // Retrieves latest failed requests from the gappsd queue.
        $res = XDB::iterator(
            "SELECT  q.q_id, q.q_recipient_id, a.alias, q.j_type, q.r_result,
                     UNIX_TIMESTAMP(q.p_entry_date) AS p_entry_date
               FROM  gapps_queue AS q
          LEFT JOIN  aliases AS a ON (a.id = q.q_recipient_id AND a.type = 'a_vie')
              WHERE  q.p_status = 'hardfail'
           ORDER BY  p_entry_date DESC
              LIMIT  20");
        $page->assign('failed_requests', $res);
    }

    function handler_admin_job(&$page, $job = null) {
        require_once("googleapps.inc.php");
        $page->changeTpl('googleapps/admin.job.tpl');
        $page->setTitle('Administration Google Apps');
        $page->assign('googleapps_admin', GoogleAppsAccount::is_administrator(S::v('uid')));

        if ($job) {
            $res = XDB::query(
                "SELECT  q.*, ao.alias AS q_owner, ar.alias AS q_recipient
                   FROM  gapps_queue AS q
              LEFT JOIN  aliases AS ao ON (ao.id = q.q_owner_id AND ao.type = 'a_vie')
              LEFT JOIN  aliases AS ar ON (ar.id = q.q_recipient_id AND ar.type = 'a_vie')
                  WHERE  q_id = {?}", $job);
            $sql_job = $res->fetchOneAssoc();
            $sql_job['decoded_parameters'] = var_export(json_decode($sql_job['j_parameters'], true), true);
            $page->assign('job', $sql_job);
        }
    }

    function handler_admin_user(&$page, $user = null, $action = null) {
        require_once("emails.inc.php");
        require_once("googleapps.inc.php");
        $page->changeTpl('googleapps/admin.user.tpl');
        $page->setTitle('Administration Google Apps');
        $page->assign('googleapps_admin', GoogleAppsAccount::is_administrator(S::v('uid')));

        if (!$user && Post::has('login')) {
            $user = Post::v('login');
        }
        if ($user && !is_numeric($user)) {
            $res = XDB::query("SELECT id FROM aliases WHERE alias = {?} AND type != 'homonyme'", $user);
            $user = $res->fetchOneCell();
        }

        if ($user) {
            $account = new GoogleAppsAccount($user);
            $storage = new EmailStorage($user, 'googleapps');

            // Force synchronization of plat/al and Google Apps passwords.
            if ($action == 'forcesync' && $account->sync_password) {
                $res = XDB::query("SELECT password FROM auth_user_md5 WHERE user_id = {?}", $user);
                $account->set_password($res->fetchOneCell());
                $page->trigSuccess('Le mot de passe a été synchronisé.');
            }

            // Displays basic account information.
            $page->assign('account', $account);
            $page->assign('admin_account', GoogleAppsAccount::is_administrator($user));
            $page->assign('googleapps_storage', $storage->active);
            $page->assign('user', $user);

            // Retrieves user's pending requests.
            $res = XDB::iterator(
                "SELECT  q_id, q_recipient_id, p_status, j_type, UNIX_TIMESTAMP(p_entry_date) AS p_entry_date
                   FROM  gapps_queue
                  WHERE  q_recipient_id = {?}
               ORDER BY  p_entry_date DESC", $user);
            $page->assign('requests', $res);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
