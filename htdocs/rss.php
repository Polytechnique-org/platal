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

require_once('xorg.inc.php');
new_nonhtml_page('rss.tpl', AUTH_PUBLIC);

$requete='SELECT e.id,e.titre,e.texte FROM evenements AS e WHERE FIND_IN_SET(flags, 'valide') AND peremption >= NOW()';

if (Env::has('promo')) {
    $promo    = Env::getInt('promo');
    $requete .= " AND (e.promo_min = 0 || e.promo_min <= $promo) AND (e.promo_max = 0 || e.promo_max >= $promo)";
    $page->assign('promo', $promo);
}

$requete.=' ORDER BY (e.promo_min != 0 AND e.promo_max != 0) DESC,  e.peremption';
$page->mysql_assign($requete, 'rss');

header('Content-Type: text/xml');
$page->run();
?> 
