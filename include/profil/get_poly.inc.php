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


//declaration des fonctions msarty pour les binets et groupex

$sql = "SELECT u.nom, u.prenom".
    ", u.promo, epouse, u.flags, section".
    " FROM auth_user_md5 AS u".
    " WHERE user_id = {?}";

$result = $globals->xdb->query($sql, Session::getInt('uid', -1));
list($nom, $prenom, $promo, $epouse, $flags, $section) = $result->fetchOneRow();

replace_ifset($section,'section');

/************* gestion des binets ************/
if (Env::has('binet_op')) {
    // retrait binet
    if( (Env::get('binet_op', '')=='retirer')&&(Env::getInt('binet_id', 0) != 0)) {
        $globals->xdb->execute("DELETE FROM binets_ins WHERE user_id = {?} AND binet_id = {?}", Session::getInt('uid', -1), Env::getInt('binet_id', -1));
    }
    // ajout binet
    if (Env::get('binet_op')=="ajouter" && (Env::getInt('binet_id', 0) != 0)) {
        $globals->xdb->execute("INSERT INTO binets_ins (user_id,binet_id) VALUES({?}, {?})", Session::getInt('uid', -1), Env::getInt('binet_id', -1));
    }
}
/************* gestion des groupes X ************/
if (Env::has('groupex_op')) {
    // retrait groupe X
    if (Env::get('groupex_op')=="retirer" && (Env::getInt('groupex_id', 0) != 0)) {
        $globals->xdb->execute("DELETE FROM groupesx_ins WHERE guid = {?} AND gid = {?}", Session::getInt('uid', -1), Env::getInt('groupex_id', -1));
    }
    // ajout groupe X
    if (Env::get('groupex_op')=="ajouter" && (Env::getInt('groupex_id', 0) != 0)) {
        $globals->xdb->execute("INSERT INTO groupesx_ins (guid, gid) VALUES ({?}, {?})", Session::getInt('uid', -1), Env::getInt('groupex_id', -1));
    }
}

?>
