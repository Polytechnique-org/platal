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
/**
 * crée des demandes de validation pour les kill d'alias
 * une demande 10 jours avant pour un warning, puis une autre pour le robot
 */

require('connect.db.inc.php');
require_once('validations/homonymes.inc.php');

$resRobot = XDB::iterator(
        "SELECT  id, alias, expire
           FROM  aliases
          WHERE  (expire = NOW() + INTERVAL 7 DAY OR expire <= NOW())
                 AND type = 'alias'");
while ($old = $resRobot->next()) {
    $res = XDB::query(
            "SELECT  u.hruid
               FROM  homonymes AS h
         INNER JOIN  auth_user_md5 AS u USING (user_id)
              WHERE  homonyme_id = {?}",
            $old['id']);
    $hruids = $res->fetchColumn();

    $homonyme = User::getSilent($old['id']);
    $req = new HomonymeReq($homonyme, $old['alias'], $hruids, $old['expire'] > date("Y-m-d"));
    $req->submit();
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
