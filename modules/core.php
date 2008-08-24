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

class CoreModule extends PLModule
{
    function handlers()
    {
        return array(
            '403'         => $this->make_hook('403', AUTH_PUBLIC),
            '404'         => $this->make_hook('404', AUTH_PUBLIC),
            'login'       => $this->make_hook('login',      AUTH_COOKIE),
            'send_bug'    => $this->make_hook('bug', AUTH_COOKIE),
            'purge_cache' => $this->make_hook('purge_cache', AUTH_COOKIE, 'admin'),
            'kill_sessions' => $this->make_hook('kill_sessions', AUTH_COOKIE, 'admin'),
            'get_rights'  => $this->make_hook('get_rights', AUTH_MDP, 'admin'),

            'wiki_help'    => $this->make_hook('wiki_help', AUTH_PUBLIC),
            'wiki_preview' => $this->make_hook('wiki_preview', AUTH_COOKIE, 'user', NO_AUTH),

            'valid.html'  => $this->make_hook('valid', AUTH_PUBLIC),
            'favicon.ico' => $this->make_hook('favicon', AUTH_PUBLIC),
            'robots.txt'  => $this->make_hook('robotstxt', AUTH_PUBLIC, 'user', NO_HTTPS),
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
        $page->coreTpl('403.tpl');
    }

    function handler_404(&$page)
    {
        global $globals, $platal;
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        $page->coreTpl('404.tpl');
        $page->assign('near', $platal->near_hook());
    }

    function handler_login(&$page)
    {
        $allkeys = func_get_args();
        unset($allkeys[0]);
        $url = join('/',$allkeys);
        pl_redirect($url);
    }

    function handler_favicon(&$page)
    {
        $data = file_get_contents(dirname(__FILE__).'/../htdocs/images/favicon.ico');
        header('Content-Type: image/x-icon');
        echo $data;
        exit;
    }

    function handler_robotstxt(&$page)
    {
        global $globals;
        if (!$globals->core->restricted_platal) {
            return PL_NOT_FOUND;
        }

        header('Content-Type: text/plain');
        echo "User-agent: *\n";
        echo "Disallow: /\n";
        exit;
    }

    function handler_purge_cache(&$page)
    {
        S::assert_xsrf_token();

        $page->clear_compiled_tpl();
        PlWikiPage::clearCache();

        http_redirect(empty($_SERVER['HTTP_REFERER']) ? './' : $_SERVER['HTTP_REFERER']);
    }

    function handler_kill_sessions(&$page)
    {
        kill_sessions();
    }

    function handler_get_rights(&$page, $level)
    {
        if (S::has('suid')) {
            $page->kill('Déjà en SUID');
        }

        if (isset($_SESSION['log'])) {
            if (S::user()) {
                S::logger()->log("suid_start", "login by " . S::user()->login());
            } else {
                // TODO(vzanotti): trash that code when support of forlife will be gone.
                S::logger()->log("suid_start", "login by ".S::v('forlife'));
            }
        }
        Platal::session()->startSUID(S::i('uid'));
        Platal::session()->makePerms($level);

        pl_redirect('/');
    }

    function handler_bug(&$page)
    {
        global $globals;

        if (empty($_SERVER['HTTP_REFERER'])) {
            // We don't have a valid referer, we need to use the url
            list($currentPage, $location) = explode('//', $_SERVER['REQUEST_URI'], 2);

            $location = 'http'.(empty($_SERVER['HTTPS']) ? '' : 's').'://'.$_SERVER['SERVER_NAME'].'/'.$location;
        } else {
            $location = $_SERVER['HTTP_REFERER'];
        }

        $page->coreTpl('bug.tpl', SIMPLE);
        $page->assign('location', $location);
        $page->addJsLink('close_on_esc.js');

        if (Env::has('send') && trim(Env::v('detailed_desc'))) {
            S::assert_xsrf_token();

            $body = wordwrap(Env::v('detailed_desc'), 78) . "\n\n"
                  . "----------------------------\n"
                  . "Page        : " . Env::v('page') . "\n\n"
                  . "Utilisateur : " . S::v('forlife') . "\n"
                  . "Navigateur  : " . $_SERVER['HTTP_USER_AGENT'] . "\n"
                  . "Skin        : " . S::v('skin') . "\n";
            $page->assign('bug_sent',1);
            $mymail = new PlMailer();
            $mymail->setFrom('"'.S::v('prenom').' '.S::v('nom').'" <'.S::v('bestalias').'@' . $globals->mail->domain . '>');
            $mymail->addTo('support+platal@' . $globals->mail->domain);
            $mymail->addCc('"'.S::v('prenom').' '.S::v('nom').'" <'.S::v('bestalias').'@' . $globals->mail->domain . '>');
            $mymail->setSubject('Plat/al '.Env::v('task_type').' : '.Env::v('item_summary'));
            $mymail->setTxtBody($body);
            $mymail->send();
        } elseif (Env::has('send')) {
            $page->trigError("Merci de remplir une explication du problème rencontré");
        }
    }

    function handler_wiki_help(&$page, $action = 'title')
    {
        $page->coreTpl('wiki.help.tpl', SIMPLE);
        $page->assign('wiki_help', MiniWiki::help($action == 'title'));
    }

    /// Shared handler for wiki syntax result preview
    function handler_wiki_preview(&$page, $action = 'title')
    {
        header('Content-Type: text/html; charset=utf-8');
        $text = Get::v('text');
        echo MiniWiki::wikiToHtml($text, $action == 'title');
        exit;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
