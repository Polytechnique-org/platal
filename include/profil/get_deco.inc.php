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

require_once('validations.inc.php');

if (Env::has('medal_op')) {
    if (Env::v('medal_op')=='retirer' && Env::i('medal_id')) {
        XDB::execute("DELETE FROM profile_medals_sub WHERE uid = {?} AND mid = {?}", S::v('uid', -1), Env::i('medal_id', -1));
    }

    if (Env::v('medal_op')=='ajouter' && Env::i('medal_id')) {
        $req = new MedalReq(S::v('uid',-1),Env::i('medal_id'), Env::i('grade_id'));
//        if ($req->mid == 20) { // defnat
//        	$req->commit();
//        	unset($_REQUEST['medal_op']); // pour ne pas avoir le message d'attente de validation
//        } else {
	        $req->submit();
//		}    
    }

    if (Env::v('medal_op')=='annuler' && Env::i('medal_id')) {
        $req = Validate::get_typed_request(S::i('uid'), 'medal', Env::v('medal_id'));
        $req->clean();
    }
}
if (Post::has('grade')) {
    foreach (Post::v('grade') as $mid=>$gid) {
        XDB::execute('UPDATE profile_medals_sub SET gid={?} WHERE uid={?} AND mid={?}', $gid, S::v('uid'), $mid);
    }
} 

$res    = XDB::query(
	"SELECT  m.id, m.text AS medal, m.type, m.img, s.gid
           FROM  profile_medals_sub    AS s
     INNER JOIN  profile_medals        AS m ON ( s.mid = m.id )
          WHERE  s.uid = {?}", S::v('uid', -1));

$medals = $res->fetchAllAssoc();

$res	= XDB::query("SELECT profile_medals_pub FROM auth_user_quick WHERE user_id = {?}", S::v('uid', -1));
$medals_pub = $res->fetchOneCell();

$medals_valid = Validate::get_typed_requests(S::i('uid'), 'medal');

if(Env::has('modifier') || Env::has('suivant')) {
    $medals_pub = Env::has('medals_pub')?'public':'private';
}

// vim:set et sws=4 sw=4 sts=4:
?>
