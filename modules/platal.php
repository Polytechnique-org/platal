<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

class PlatalModule extends PLModule
{
    function handlers()
    {
        return array(
            'preferences' => $this->make_hook('prefs', AUTH_COOKIE),
            'skin'        => $this->make_hook('skin', AUTH_COOKIE),
        );
    }

    function handler_prefs(&$page)
    {
        global $globals;

        $page->changeTpl('preferences.tpl');
        $page->assign('xorg_title','Polytechnique.org - Mes préférences');

        if (Env::has('mail_fmt')) {
            $fmt = Env::get('mail_fmt');
            if ($fmt != 'texte') $fmt = 'html';
            $globals->xdb->execute("UPDATE auth_user_quick
                                       SET core_mail_fmt = '$fmt'
                                     WHERE user_id = {?}",
                                     Session::getInt('uid'));
            $_SESSION['mail_fmt'] = $fmt;
            redirect('preferences');
        }

        if (Env::has('rss')) {
            if (Env::getBool('rss')) {
                $_SESSION['core_rss_hash'] = rand_url_id(16);
                $globals->xdb->execute('UPDATE  auth_user_quick
                                           SET  core_rss_hash={?} WHERE user_id={?}',
                                       Session::get('core_rss_hash'),
                                       Session::getInt('uid'));
            } else {
                $globals->xdb->execute('UPDATE  auth_user_quick
                                           SET  core_rss_hash="" WHERE user_id={?}',
                                       Session::getInt('uid'));
                Session::kill('core_rss_hash');
            }
            redirect('preferences');
        }

        $page->assign('prefs', $globals->hook->prefs());

        return PL_OK;
    }

    function handler_skin(&$page)
    {
        global $globals;

        if (!$globals->skin->enable) {
            redirect('./');
        }

        $page->changeTpl('skins.tpl');
        $page->assign('xorg_title','Polytechnique.org - Skins');

        if (Env::has('newskin'))  {  // formulaire soumis, traitons les données envoyées
            $globals->xdb->execute('UPDATE auth_user_quick
                                       SET skin={?} WHERE user_id={?}',
                                    Env::getInt('newskin'),
                                    Session::getInt('uid'));
            set_skin();
        }

        $sql = "SELECT s.*,auteur,count(*) AS nb
                  FROM skins AS s
             LEFT JOIN auth_user_quick AS a ON s.id=a.skin
                 WHERE skin_tpl != '' AND ext != ''
              GROUP BY id ORDER BY s.date DESC";
        $page->assign_by_ref('skins', $globals->xdb->iterator($sql));
        return PL_OK;
    }
}

?>
