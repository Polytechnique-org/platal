<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

class CoreModule extends PLModule
{
    function handlers()
    {
        return array(
            '403'         => $this->make_hook('403', AUTH_PUBLIC),
            '404'         => $this->make_hook('404', AUTH_PUBLIC),
            'send_bug'       => $this->make_hook('bug', AUTH_COOKIE),
            'purge_cache' => $this->make_hook('purge_cache', AUTH_COOKIE, 'admin'),
            'get_rights'  => $this->make_hook('get_rights', AUTH_MDP, 'admin'),

            'valid.html'  => $this->make_hook('valid', AUTH_PUBLIC),
            'favicon.ico' => $this->make_hook('favicon', AUTH_PUBLIC),
        );
    }

    function handler_valid(&$page)
    {
        readfile($page->compile_dir.'/valid.html');
        exit;
    }

    function handler_403(&$page)
    {
        global $globals;
        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
        if (!empty($GLOBALS['IS_XNET_SITE']) && $globals->asso()) {
            new_skinned_page('core/403.tpl');
        } else {
            $page->changeTpl('core/403.tpl');
        }
    }

    function handler_404(&$page)
    {
        global $globals, $platal;
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        if (!empty($GLOBALS['IS_XNET_SITE']) && $globals->asso()) {
            new_group_open_page('core/404.tpl');
        } else {
            $page->changeTpl('core/404.tpl');
        }
        $page->assign('near', $platal->near_hook());
    }

    function handler_favicon(&$page)
    {
        $data = file_get_contents(dirname(__FILE__).'/../htdocs/images/favicon.ico');
        header('Content-Type: image/x-icon');
        echo $data;
        exit;
    }

    function handler_purge_cache(&$page)
    {
        require_once 'wiki.inc.php';

        $page->clear_compiled_tpl();
        wiki_clear_all_cache();

        http_redirect(empty($_SERVER['HTTP_REFERER']) ? './' : $_SERVER['HTTP_REFERER']);
    }

    function handler_get_rights(&$page, $level)
    {
        if (S::has('suid')) {
            $page->kill('Déjà en SUID');
        }

        if (isset($_SESSION['log'])) {
            $_SESSION['log']->log("suid_start", "login by ".S::v('forlife'));
        }    
        $_SESSION['suid'] = $_SESSION;
        $_SESSION['perms'] = $level;

        pl_redirect('/');
    }

    function handler_bug(&$page)
    {
        $page->changeTpl('core/bug.tpl',SIMPLE);
        $page->addJsLink('close_on_esc.js');
        if (Env::has('send') && trim(Env::v('detailed_desc'))) {
            $body = wordwrap(Env::v('detailed_desc'), 78) . "\n\n"
                  . "----------------------------\n"
                  . "Page        : " . Env::v('page') . "\n\n"
                  . "Utilisateur : " . S::v('forlife') . "\n"
                  . "Navigateur  : " . Env::v('browser') . "\n"
                  . "Skin        : " . Env::v('skin') . "\n";
            $page->assign('bug_sent',1);
            $mymail = new PlMailer();
            $mymail->setFrom('"'.S::v('prenom').' '.S::v('nom').'" <'.S::v('bestalias').'@polytechnique.org>');
            $mymail->addTo('support+platal@polytechnique.org');
            $mymail->setSubject('Plat/al '.Env::v('task_type').' : '.Env::v('item_summary'));
            $mymail->setTxtBody($body);
            $mymail->send();
        } elseif (Env::has('send')) {
            $page->trig("Merci de remplir une explication du problème rencontré");
        }
    }
}

?>
