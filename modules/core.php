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

class CoreModule extends PLModule
{
    function handlers()
    {
        return array(
            '400'           => $this->make_hook('400',           AUTH_PUBLIC),
            '403'           => $this->make_hook('403',           AUTH_PUBLIC),
            '404'           => $this->make_hook('404',           AUTH_PUBLIC),
            'login'         => $this->make_hook('login',         AUTH_COOKIE),
            'send_bug'      => $this->make_hook('bug',           AUTH_COOKIE),
            'purge_cache'   => $this->make_hook('purge_cache',   AUTH_COOKIE, 'admin'),
            'kill_sessions' => $this->make_hook('kill_sessions', AUTH_COOKIE, 'admin'),
            'sql_errors'    => $this->make_hook('siteerror',     AUTH_COOKIE, 'admin'),
            'assert_errors' => $this->make_hook('siteerror',     AUTH_COOKIE, 'admin'),
            'site_errors'   => $this->make_hook('siteerror',     AUTH_COOKIE, 'admin'),

            'embedded'      => $this->make_hook('embedded',      AUTH_PUBLIC),

            'wiki_help'     => $this->make_hook('wiki_help',     AUTH_PUBLIC),
            'wiki_preview'  => $this->make_hook('wiki_preview',  AUTH_COOKIE, 'user', NO_AUTH),

            'validator'     => $this->make_hook('validator',     AUTH_PUBLIC),
            'valid.html'    => $this->make_hook('valid',         AUTH_PUBLIC),
            'favicon.ico'   => $this->make_hook('favicon',       AUTH_PUBLIC),
            'robots.txt'    => $this->make_hook('robotstxt',     AUTH_PUBLIC, 'user', NO_HTTPS),
        );
    }

    function handler_valid($page)
    {
        readfile($page->compile_dir.'/valid.html');
        exit;
    }

    function handler_validator($page)
    {
        global $globals;
        exec($globals->spoolroot."/bin/devel/xhtml.validate.pl ".$page->compile_dir."/valid.html", $val);
        $url   = "<a href='http://validator.w3.org/check?uri={$globals->baseurl}/valid.html&amp;ss=1#result'>";
        $replc = '<span style="color: #080;">HTML OK</span>';
        foreach ($val as $h) {
            if (preg_match("/^X-W3C-Validator-Errors: (\d+)$/", $h, $m)) {
                if ($m[1]) {
                    $replc = "<span class='erreur'>$url{$m[1]} ERREUR(S) !!!</a></span>";
                }
                break;
            }
        }
        echo $replc;
        exit;
    }

    function handler_400(PlPage $page)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        $page->coreTpl('40x.tpl');
        $page->trigError('Ta requête est invalide.');
    }

    function handler_403(PlPage $page)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
        $page->coreTpl('40x.tpl');
        $page->trigError('Tu n\'as pas les permissions nécessaires pour accéder à cette page.');
    }

    function handler_404(PlPage $page)
    {
        global $platal;
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        $page->coreTpl('404.tpl');
        $page->assign('near', $platal->near_hook());
        $page->trigError('Cette page n\'existe pas !!!');
    }

    function handler_login($page)
    {
        $allkeys = func_get_args();
        unset($allkeys[0]);
        $url = join('/',$allkeys);
        pl_redirect($url);
    }

    function handler_favicon($page)
    {
        global $globals;
        pl_cached_content_headers("image/x-icon");
        readfile($globals->spoolroot . '/htdocs/images/favicon.ico');
        exit;
    }

    function handler_robotstxt($page)
    {
        global $globals;

        $disallowed_uris = array();
        if ($globals->core->restricted_platal) {
            $disallowed_uris[] = '/';
        } else if (!empty($globals->core->robotstxt_disallowed_uris)) {
            $disallowed_uris = preg_split('/[\s,]+/',
                                          $globals->core->robotstxt_disallowed_uris,
                                          -1, PREG_SPLIT_NO_EMPTY);
        }

        if (count($disallowed_uris) > 0) {
            pl_cached_content_headers("text/plain");
            echo "User-agent: *\n";
            foreach ($disallowed_uris as $uri) {
                echo "Disallow: $uri\n";
            }
            exit;
        }
        return PL_NOT_FOUND;
    }

    function handler_purge_cache($page)
    {
        S::assert_xsrf_token();

        $page->clear_compiled_tpl();
        PlWikiPage::clearCache();
        PlCache::clearAll();

        http_redirect(empty($_SERVER['HTTP_REFERER']) ? './' : $_SERVER['HTTP_REFERER']);
    }

    function handler_kill_sessions($page)
    {
        kill_sessions();
    }

    function handler_bug($page)
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
                  . "Utilisateur : " . S::user()->login() . "\n"
                  . "Navigateur  : " . $_SERVER['HTTP_USER_AGENT'] . "\n"
                  . "Skin        : " . S::v('skin') . "\n";
            $page->assign('bug_sent', 1);
            $page->trigSuccess('Ton message a bien été envoyé au support de ' . $globals->core->sitename
                             . ', tu devrais en recevoir une copie d\'ici quelques minutes. Nous allons '
                             . 'le traiter et y répondre dans les plus brefs délais.');
            $mymail = new PlMailer();
            $mymail->setFrom(sprintf('"%s" <%s>', S::user()->fullName(), S::user()->bestEmail()));
            $mymail->addCc(sprintf('"%s" <%s>', S::user()->fullName(), S::user()->bestEmail()));
            $mymail->addTo('support+platal@' . $globals->mail->domain);
            $mymail->setSubject('Plat/al '.Env::v('task_type').' : '.Env::v('item_summary'));
            $mymail->setTxtBody($body);
            $mymail->send();
        } elseif (Env::has('send')) {
            $page->trigError("Merci de remplir une explication du problème rencontré.");
        }
    }

    function handler_wiki_help($page, $action = 'title')
    {
        $page->coreTpl('wiki.help.tpl', SIMPLE);
        $page->assign('wiki_help', MiniWiki::help($action == 'title'));
    }

    /// Shared handler for wiki syntax result preview
    function handler_wiki_preview($page, $action = 'title')
    {
        pl_content_headers("text/html");
        $text = Env::v('text');
        echo MiniWiki::wikiToHtml($text, $action == 'title');
        exit;
    }

    function handler_siteerror($page) {
        global $globals;
        $page->coreTpl('site_errors.tpl');
        if (Post::has('clear')) {
            PlErrorReport::clear();
            $page->trigSuccess("Erreurs effacées.");
        }
        $page->assign('errors', PlErrorReport::iterate());
    }

    function handler_embedded($page)
    {
        global $platal, $globals;
        $allkeys = func_get_args();
        $mode = $allkeys[1];
        unset($allkeys[0]);
        unset($allkeys[1]);
        Env::set('display', $mode);
        $globals->baseurl .= '/embedded/' . $mode;
        $platal->path = join('/', $allkeys);
        $platal->run();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
