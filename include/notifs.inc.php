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
 ***************************************************************************
        $Id: notifs.inc.php,v 1.1 2004-11-04 19:57:43 x2000habouzit Exp $
 ***************************************************************************/

require_once('diogenes.flagset.inc.php');
 
class Notifs {
    var $uid;
    var $flags;
    var $promos = Array();
    var $nonins = Array();

    function Notifs($uid) {
	global $globals;
	$this->uid = $uid;

	$res = $globals->db->query("SELECT watch FROM auth_user_md5 WHERE user_id = '$uid'");
	list($flags) = mysql_fetch_row($res);
	mysql_free_result($res);
	$this->flags = new FlagSet($flags);
	
	$res = $globals->db->query("SELECT  type,arg,prenom,nom,promo
				      FROM  watch
				INNER JOIN  auth_user_md5 USING(user_id)
				     WHERE  watch.user_id = '$uid'
				  ORDER BY  arg");
	while(list($type, $arg, $prenom, $nom, $promo) = mysql_fetch_row($res)) {
	    if($type=='promo') {
		$this->promos[$arg] = $arg;
	    } elseif($type =='non-inscrit') {
		$this->nonins[$arg] = Array('prenom'=>$prenom, 'nom'=>$nom, 'promo'=>$promo);
	    }
	}
    }

    function del_promo($p) {
	global $globals;
	unset($this->promos[$p]);
	$globals->db->query("DELETE FROM watch WHERE user_id='{$this->uid}' AND type='promo' AND arg='$p'");
    }

    function add_promo($p) {
	global $globals;
	$this->promos[$p] = $p;
	$globals->db->query("REPLACE INTO watch (user_id,type,arg) VALUES ('{$this->uid}','promo','$p')");
    }

    function saveFlags() {
	global $globals;
	$globals->db->query("UPDATE auth_user_md5 SET watch='{$this->flags->value}' WHERE user_id='{$this->uid}'");
    }
}
 
?>
