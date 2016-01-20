<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

Platal::load('newsletter');

/**
 * Newsletter for community
 */
class ComLetterModule extends NewsletterModule
{
    function handlers()
    {
        return array(
            'comletter'                   => $this->make_hook('nl',              AUTH_COOKIE, 'user'),
            'comletter/submit'            => $this->make_hook('coml_submit',     AUTH_PASSWD, 'user'),
            'comletter/remaining'         => $this->make_hook('coml_remaining',  AUTH_PASSWD, 'user'),
            'comletter/out'               => $this->make_hook('out',             AUTH_COOKIE, 'user'),
            'comletter/show'              => $this->make_hook('nl_show',         AUTH_COOKIE, 'user'),
            'comletter/search'            => $this->make_hook('nl_search',       AUTH_COOKIE, 'user'),
            'comletter/admin'             => $this->make_hook('admin_nl',        AUTH_PASSWD, 'user'),
            'comletter/admin/edit'        => $this->make_hook('admin_nl_edit',   AUTH_PASSWD, 'user'),
            'comletter/admin/edit/valid'  => $this->make_hook('admin_nl_valid',  AUTH_PASSWD, 'user'),
            'comletter/admin/edit/cancel' => $this->make_hook('admin_nl_cancel', AUTH_PASSWD, 'user'),
            'comletter/admin/edit/delete' => $this->make_hook('admin_nl_delete', AUTH_PASSWD, 'user'),
            'comletter/admin/categories'  => $this->make_hook('admin_nl_cat',    AUTH_PASSWD, 'user'),
            'comletter/stat'              => $this->make_hook('stat_nl',         AUTH_PASSWD, 'user')
        );
    }

    protected function getNl()
    {
        require_once 'newsletter.inc.php';
        return NewsLetter::forGroup(NewsLetter::GROUP_COMMUNITY);
    }

    function handler_coml_submit($page)
    {
        $page->changeTpl('comletter/submit.tpl');

        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        $wp = new PlWikiPage('Xorg.LettreCommunaute');
        $wp->buildCache();

        if (Post::has('see') || (Post::has('valid') && (!trim(Post::v('title')) || !trim(Post::v('body'))))) {
            if (!Post::has('see')) {
                $page->trigError("L'article doit avoir un titre et un contenu");
            }
            require_once 'comletter.inc.php';
            $art = new ComLArticle(Post::v('title'), Post::v('body'), Post::v('append'));
            $page->assign('art', $art);
        } elseif (Post::has('valid')) {
            $art = new ComLReq(S::user(), Post::v('title'),
                               Post::v('body'), Post::v('append'));
            $art->submit();
            $page->assign('submited', true);
        }
        $page->addCssLink($nl->cssFile());
    }

    function handler_coml_remaining($page)
    {
        pl_content_headers('text/html');
        $page->changeTpl('newsletter/remaining.tpl', NO_SKIN);

        require_once 'comletter.inc.php';
        $article = new ComLArticle('', Post::t('body'), '');
        $rest = $article->remain();

        $page->assign('too_long', $rest['remaining_lines'] < 0);
        $page->assign('last_line', ($rest['remaining_lines'] == 0));
        $page->assign('remaining', ($rest['remaining_lines'] == 0) ? $rest['remaining_characters_for_last_line'] : $rest['remaining_lines']);
    }

    function handler_out($page, $hash = null, $issue_id = null)
    {
        $hash = ($hash == 'nohash') ? null : $hash;
        if (!$hash) {
            if (!S::logged()) {
                return PL_DO_AUTH;
            }
        }
        return $this->handler_nl($page, 'out', $hash, $issue_id);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
