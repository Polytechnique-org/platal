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

$DONT_FIX_GPC = 1;

require_once('xorg.inc.php');
new_admin_table_editor('profile_medals', 'id');
$page->assign('xorg_title','Polytechnique.org - Administration - Distinctions');

$editor->describe('type', 'type', true, 'set');
$editor->describe('text', 'intitulé',  true);
$editor->describe('img',  'nom de l\'image', false);

$editor->assign('title',  'Gestion des Distinctions');

if (Post::v('frm_id')) {
    $page->changeTpl('admin/gerer_decos.tpl');

    $mid = Post::i('frm_id');

    if (Post::v('act') == 'del') {
        XDB::execute('DELETE FROM profile_medals_grades WHERE mid={?} AND gid={?}', $mid, Post::i('gid'));
    } elseif (Post::v('act') == 'new') {
        XDB::execute('INSERT INTO profile_medals_grades (mid,gid) VALUES({?},{?})',
                $mid, max(array_keys(Post::v('grades', array(0))))+1);
    } else {
        foreach (Post::v('grades', array()) as $gid=>$text) {
            XDB::execute('UPDATE profile_medals_grades SET pos={?}, text={?} WHERE gid={?}', $_POST['pos'][$gid], $text, $gid);
        }
    }
    $res = XDB::iterator('SELECT gid, text, pos FROM profile_medals_grades WHERE mid={?} ORDER BY pos', $mid);
    $page->assign('grades', $res);
}

$editor->run();
?>
