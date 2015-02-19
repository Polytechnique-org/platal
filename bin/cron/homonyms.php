#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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
 * Requires destruction of aliases: a first notification 7 days before
 * destruction, a second on the date.
 */
require 'connect.db.inc.php';

$resRobot = XDB::iterator("SELECT  uid, email, expire
                             FROM  email_source_account
                            WHERE  expire <= NOW() + INTERVAL 7 DAY");
while ($old = $resRobot->next()) {
    $res = XDB::query('SELECT  a.hruid
                         FROM  homonyms_list AS h
                   INNER JOIN  accounts      AS a ON (h.uid = a.uid)
                        WHERE  h.hrmid = {?}',
                      User::makeHomonymHrmid($old['email']));
    $hruids = $res->fetchColumn();

    $homonym = User::getSilent($old['uid']);
    $req = new HomonymeReq($homonym, $old['email'], $hruids, $old['expire'] > date("Y-m-d"));
    $req->submit();
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
