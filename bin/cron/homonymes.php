#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
/* vim: set sw=4 ts=4 sts=4 tw=100:
 * crée des demandes de validation pour les kill d'alias
 * une demande 10 jours avant pour un warning, puis une autre pour le robot
*/

$W_PERIOD = "INTERVAL 7 DAY"; // temps d'envoi du warning avant la deadline

require('./connect.db.inc.php');

$resRobot = XDB::iterator("SELECT id, alias, expire FROM aliases WHERE (expire = NOW() + $W_PERIOD OR expire <= NOW()) AND type = 'alias'");

if ($resRobot->total()) {
    require_once('validations/homonymes.inc.php');
    while ($old = $resRobot->next()) {
    	$res = XDB::query("SELECT alias AS forlife FROM homonymes INNER JOIN aliases ON(user_id = id) WHERE homonyme_id = {?} AND type='a_vie'", $old['id']);
	$forlifes = $res->fetchColumn();
	$req = new HomonymeReq($old['id'], $old['alias'], $forlifes, $old['expire'] > date("Y-m-d"));
	$req->submit();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
