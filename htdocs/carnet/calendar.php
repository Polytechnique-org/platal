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
new_nonhtml_page('carnet/calendar.tpl', AUTH_PUBLIC);

if (preg_match(',^/([^/]+)/([^/_]+)(_all)?\.ics$,', $_SERVER['PATH_INFO'], $m)) {
    $alias = $m[1];
    $hash  = $m[2];
    $all = $m[3];
} else {
    $alias = Env::get('alias');
    $hash = Env::get('hash');
    $all = Env::has('all');
}
if ($alias && $hash) {
    $res = $globals->xdb->query(
        'SELECT  a.id
           FROM  aliases         AS a
     INNER JOIN  auth_user_quick AS q ON ( a.id = q.user_id AND q.core_rss_hash = {?} )
          WHERE  a.alias = {?} AND a.type != "homonyme"', $hash, $alias);
    $uid = $res->fetchOneCell();
}

require_once('notifs.inc.php');
$notifs = new Notifs($uid, true);

$annivcat = false;
foreach ($notifs->_cats as $cat)
    if (preg_match('/anniv/i', $cat['short']))
        $annivcat = $cat['id'];

if ($annivcat !== false) {
    $annivs = array();
    foreach ($notifs->_data[$annivcat] as $promo) foreach ($promo as $notif)
    if ($all || $notif['contact']) {
        $annivs[] = array(
            'timestamp' => $notif['known'],
            'date' => strtotime($notif['date']),
            'tomorrow' => strtotime("+1 day", strtotime($notif['date'])),
            'bestalias' => $notif['bestalias'],
            'summary' => 'Anniversaire de '.$notif['prenom'].' '.$notif['nom'].' - x '.$notif['promo'],
        );        
    }
    $page->assign('events', $annivs);
}

header('Content-Type: text/calendar; charset=utf-8');

$page->run();
?>