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
require_once('marketing.inc.php');
new_admin_page('marketing/relance.tpl');

/* une relance a été demandée - on envoit les mails correspondants */
if (Post::has('relancer')) {
    $res   = $globals->xdb->query("SELECT COUNT(*) FROM auth_user_md5 WHERE deces=0");
    $nbdix = $res->fetchOneCell();

    $sent  = Array();
    foreach (array_keys($_POST['relance']) as $uid) {
        if ($tmp = relance($uid, $nbdix)) {
            $sent[] = $tmp.' a été relancé';
        }
    }
    $page->assign('sent', $sent);
}

$sql = "SELECT  r.date, r.relance, r.uid, u.promo, u.nom, u.prenom
          FROM  register_pending AS r
    INNER JOIN  auth_user_md5    AS u ON r. uid = u.user_id
         WHERE  hash!='INSCRIT'
      ORDER BY  date DESC";
$page->assign('relance', $globals->xdb->iterator($sql));

$page->run();
?>
