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
            'googleapps' => $this->make_hook('index', AUTH_MDP),
        );
    }

    function handler_index(&$page, $action = null, $subaction = null)
    {
        require_once("emails.inc.php");
        require_once("googleapps.inc.php");
        $page->changeTpl('googleapps/index.tpl');
        $page->addJsLink('motdepasse.js');
        $page->assign('xorg_title', 'Polytechnique.org - Compte Google Apps');

        $account = new GoogleAppsAccount(S::v('uid'), S::v('forlife'));

        // Fills up the 'is Google Apps redirection active' variable.
        $page->assign('redirect_active', false);
        $page->assign('redirect_unique', true);

        if ($account->g_status == 'active') {
            $redirect = new Redirect(S::v('uid'));
            $page->assign('redirect_unique', !$redirect->other_active(NULL));

            $storage = new MailStorageGoogleApps(S::v('uid'));
            $page->assign('redirect_active', $storage->active());
        }

        // Updates the Google Apps account as required.
        if ($action) {
            if ($action == 'password') {
                if ($subaction == 'sync') {
                    $account->set_password_sync(true);
                    $account->set_password($_SESSION['password']);
                    $page->trig("Ton mot de passe Google Apps sera dorénavant synchronisé avec ton mot de passe Polytechnique.org.");
                } else if ($subaction == 'nosync') {
                    $account->set_password_sync(false);
                } else if (Post::has('response2') && !$account->sync_password) {
                    $account->set_password(Post::v('response2'));
                }
            }

            if ($action == 'suspend' && Post::has('suspend') && $account->g_status == 'active') {
                if ($account->pending_update_suspension) {
                    $page->trig("Ton compte est déjà en cours de désactivation.");
                } else {
                    $storage = new MailStorageGoogleApps(S::v('uid'));
                    if ($storage->disable()) {
                        $account->suspend();
                        $page->trig("Ton compte Google Apps est dorénavant désactivé.");
                    } else {
                        $page->trig("Ton compte Google Apps est ta seule adresse de redirection. Ton compte ne peux pas être désactivé.");
                    }
                }
            } elseif ($action == 'unsuspend' && Post::has('unsuspend') && $account->g_status == 'disabled') {
                $account->unsuspend(Post::b('redirect_mails', true));
                $page->trig("Ta demande de réactivation a bien été prise en compte.");
            }

            if ($action == 'create') {
                $page->assign('has_password_sync', Get::has('password_sync'));
                $page->assign('password_sync', Get::b('password_sync', true));
            }
            if ($action == 'create' && Post::has('password_sync') && Post::has('redirect_mails')) {
                $password_sync = Post::b('password_sync');
                $redirect_mails = Post::b('redirect_mails');
                if ($password_sync) {
                    $password = $_SESSION['password'];
                } else {
                    $password = Post::v('response2');
                }

                $account->create($password_sync, $password, $redirect_mails);
                $page->trig("La demande de création de ton compte Google Apps a bien été enregistrée.");
            }
        }

        $page->assign('account', $account);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
