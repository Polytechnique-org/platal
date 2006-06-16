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

require_once("xorg.inc.php");
new_admin_page('admin/index.tpl');
$page->assign('xorg_title','Polytechnique.org - Administration');

$res = $globals->xdb->iterRow("
            SELECT  h1, h2, texte, url
              FROM  admin_a  AS a
        INNER JOIN  admin_h2 AS h2 USING(h2id)
        INNER JOIN  admin_h1 AS h1 USING(h1id)
          ORDER BY  h1.prio, h2.prio, a.prio");
$index = Array();
while(list($h1,$h2,$txt,$url) = $res->next()) {
    $index[$h1][$h2][] = Array('txt' => $txt, 'url'=>$url);
}
$page->assign_by_ref('index', $index);

$page->run();
?>
