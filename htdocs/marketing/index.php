<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
new_admin_page('marketing/index.tpl');
$page->assign('xorg_title','Polytechnique.org - Marketing');

# Quelques statistiques

$res   = $globals->xdb->query(
          "SELECT COUNT(*) AS vivants,
                  COUNT(NULLIF(perms='admin' OR perms='user', 0)) AS inscrits,
                  100*COUNT(NULLIF(perms='admin' OR perms='user', 0))/COUNT(*) AS ins_rate,
                  COUNT(NULLIF(promo >= 1972, 0)) AS vivants72,
                  COUNT(NULLIF(promo >= 1972 AND (perms='admin' OR perms='user'), 0)) AS inscrits72,
                  100 * COUNT(NULLIF(promo >= 1972 AND (perms='admin' OR perms='user'), 0)) /
                      COUNT(NULLIF(promo >= 1972, 0)) AS ins72_rate,
                  COUNT(NULLIF(FIND_IN_SET('femme', flags), 0)) AS vivantes,
                  COUNT(NULLIF(FIND_IN_SET('femme', flags) AND (perms='admin' OR perms='user'), 0)) AS inscrites,
                  100 * COUNT(NULLIF(FIND_IN_SET('femme', flags) AND (perms='admin' OR perms='user'), 0)) /
                      COUNT(NULLIF(FIND_IN_SET('femme', flags), 0)) AS inse_rate
             FROM auth_user_md5
            WHERE deces = 0");
$stats = $res->fetchOneAssoc();
$page->assign('stats', $stats);

$res   = $globals->xdb->query("SELECT count(*) FROM auth_user_md5 WHERE date_ins > ".date('Ymd000000', strtotime('1 week ago')));
$page->assign('nbInsSem', $res->fetchOneCell());

$res = $globals->xdb->query("SELECT count(*) FROM register_pending WHERE hash != 'INSCRIT'");
$page->assign('nbInsEnCours', $res->fetchOneCell());

$res = $globals->xdb->query("SELECT count(*) FROM register_marketing");
$page->assign('nbInsMarket', $res->fetchOneCell());

$res = $globals->xdb->query("SELECT count(*) FROM register_mstats WHERE TO_DAYS(NOW()) - TO_DAYS(success) <= 7");
$page->assign('nbInsMarkOK', $res->fetchOneCell());

$page->run();
?>
