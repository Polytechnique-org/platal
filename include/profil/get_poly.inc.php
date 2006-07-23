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


//declaration des fonctions msarty pour les binets et groupex

$sql = "SELECT section".
    " FROM auth_user_md5 AS u".
    " WHERE user_id = {?}";

$result = XDB::query($sql, S::v('uid', -1));
$section = $result->fetchOneCell();

replace_ifset($section,'section');

/************* gestion des binets ************/
if (Env::has('binet_op')) {
    // retrait binet
    if( (Env::v('binet_op', '')=='retirer')&&(Env::i('binet_id', 0) != 0)) {
        XDB::execute("DELETE FROM binets_ins WHERE user_id = {?} AND binet_id = {?}", S::v('uid', -1), Env::i('binet_id', -1));
    }
    // ajout binet
    if (Env::v('binet_op')=="ajouter" && (Env::i('binet_id', 0) != 0)) {
        XDB::execute("INSERT INTO binets_ins (user_id,binet_id) VALUES({?}, {?})", S::v('uid', -1), Env::i('binet_id', -1));
    }
}
/************* gestion des groupes X ************/
if (Env::has('groupex_op')) {
    // retrait groupe X
    if (Env::v('groupex_op')=="retirer" && (Env::i('groupex_id', 0) != 0)) {
        XDB::execute("DELETE FROM groupesx_ins WHERE guid = {?} AND gid = {?}", S::v('uid', -1), Env::i('groupex_id', -1));
    }
    // ajout groupe X
    if (Env::v('groupex_op')=="ajouter" && (Env::i('groupex_id', 0) != 0)) {
        XDB::execute("INSERT INTO groupesx_ins (guid, gid) VALUES ({?}, {?})", S::v('uid', -1), Env::i('groupex_id', -1));
    }
}

?>
