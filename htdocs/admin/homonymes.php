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
new_admin_page('admin/homonymes.tpl');
$page->assign('xorg_title','Polytechnique.org - Administration - Homonymes');
require_once("homonymes.inc.php");

$op     = Env::get('op', 'list');
$target = Env::getInt('target');

if ($target) {
    if (! list($prenom,$nom,$forlife,$loginbis) = select_if_homonyme($target)) {
        $target=0;
    } else {
        $page->assign('nom',$nom);
        $page->assign('prenom',$prenom);
        $page->assign('forlife',$forlife);
        $page->assign('loginbis',$loginbis);
    }
}

$page->assign('op',$op);
$page->assign('target',$target);
$page->assign('baseurl',$globals->baseurl);

// on a un $target valide, on prepare les mails
if ($target) {
    
    // on examine l'op a effectuer
    switch ($op) {
        case 'mail':
	    send_warning_homonyme($prenom, $nom, $forlife, $loginbis);
	    switch_bestalias($target, $loginbis);
            $op = 'list';
            break;
        case 'correct':
	    switch_bestalias($target, $loginbis);
            $globals->xdb->execute("UPDATE aliases SET type='homonyme',expire=NOW() WHERE alias={?}", $loginbis);
            $globals->xdb->execute("REPLACE INTO homonymes (homonyme_id,user_id) VALUES({?},{?})", $target, $target);
	    send_robot_homonyme($prenom, $nom, $forlife, $loginbis);
            $op = 'list';
            break;
    }
}

if ($op == 'list') {
    $res = $globals->xdb->iterator(
            "SELECT  a.alias AS homonyme,s.id AS user_id,s.alias AS forlife,
                     promo,prenom,nom,
                     IF(h.homonyme_id=s.id, a.expire, NULL) AS expire,
                     IF(h.homonyme_id=s.id, a.type, NULL) AS type
               FROM  aliases       AS a
          LEFT JOIN  homonymes     AS h ON (h.homonyme_id = a.id)
         INNER JOIN  aliases       AS s ON (s.id = h.user_id AND s.type='a_vie')
         INNER JOIN  auth_user_md5 AS u ON (s.id=u.user_id)
              WHERE  a.type='homonyme' OR a.expire!=''
           ORDER BY  a.alias,promo");
    $hnymes = Array();
    while ($tab = $res->next()) {
        $hnymes[$tab['homonyme']][] = $tab;
    }
    $page->assign_by_ref('hnymes',$hnymes);
}

$page->run();
?>
