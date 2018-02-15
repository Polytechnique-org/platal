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

class ForumsModule extends PLModule
{
    function handlers()
    {
        return array(
            'banana'       => $this->make_hook('banana',      AUTH_COOKIE, 'forums'),
            'banana/rss'   => $this->make_hook('rss',         AUTH_PUBLIC, 'forums', NO_HTTPS),
            'admin/forums' => $this->make_hook('forums_bans', AUTH_PASSWD, 'admin'),
        );
    }

    function handler_banana($page, $group = null, $action = null, $artid = null)
    {
        $page->changeTpl('banana/index.tpl');
        $page->setTitle('Forums & PA');

        $get = Array();
        if (Post::has('updateall')) {
            $get['updateall'] = Post::v('updateall');
        }
        require_once 'banana/forum.inc.php';
        get_banana_params($get, $group, $action, $artid);
        run_banana($page, 'ForumsBanana', $get);
    }

    function handler_rss($page, $group, $alias, $hash, $file = null)
    {
        if (is_null($file)) {
            if (is_null($hash)) {
                return PL_FORBIDDEN;
            }
            $this->handler_rss($page, null, $group, $alias, $hash);
        }
        $user = Platal::session()->tokenAuth($alias, $hash);
        if (is_null($user)) {
            return PL_FORBIDDEN;
        }

        require_once 'banana/forum.inc.php';
        $banana = new ForumsBanana($user, array('group' => $group, 'action' => 'rss2'));
        $banana->run();
        exit;
    }

    function handler_forums_bans($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Bannissements des forums');
        $page->assign('title', 'Gestion des mises au ban');
        $table_editor = new PLTableEditor('admin/forums','forum_innd','id_innd');
        $table_editor->add_sort_field('priority', true, true);
        $table_editor->describe('read_perm','lecture',true);
        $table_editor->describe('write_perm','écriture',true);
        $table_editor->describe('priority','priorité',true);
        $table_editor->describe('comment','commentaire',true);
        $table_editor->apply($page, $action, $id);
        $page->changeTpl('forums/admin.tpl');
    }

    static function run_banana($page, $params = null)
    {
        $page->changeTpl('banana/index.tpl');
        $page->setTitle('Forums & PA');

        require_once 'banana/forum.inc.php';
        run_banana($page, 'ForumsBanana', $params);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
