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

if (Env::has('medal_op')) {
    if (Env::get('medal_op')=='retirer' && Env::getInt('medal_id')) {
        $globals->xdb->execute("DELETE FROM profile_medals_sub WHERE uid = {?} AND mid = {?}", Session::getInt('uid', -1), Env::getInt('medal_id', -1));
    }

    if (Env::get('medal_op')=='ajouter' && Env::getInt('medal_id')) {
        $globals->xdb->execute("INSERT INTO profile_medals_sub (uid,mid) VALUES ({?}, {?})", Session::getInt('uid', -1), Env::getInt('medal_id'));
    }
}
if (Post::has('grade')) {
    foreach (Post::getMixed('grade') as $mid=>$gid) {
        $globals->xdb->execute('UPDATE profile_medals_sub SET gid={?} WHERE uid={?} AND mid={?}', $gid, Session::getInt('uid'), $mid);
    }
} 

$res    = $globals->xdb->query(
	"SELECT  m.id, m.text AS medal, m.type, m.img, s.gid
           FROM  profile_medals_sub    AS s
     INNER JOIN  profile_medals        AS m ON ( s.mid = m.id )
          WHERE  s.uid = {?}", Session::getInt('uid', -1));

$medals = $res->fetchAllAssoc();

?>
