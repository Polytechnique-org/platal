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
        $Id: notifs.inc.php,v 1.6 2004-11-06 16:08:27 x2000habouzit Exp $
 ***************************************************************************/

require_once('diogenes.flagset.inc.php');

class Watch {
    var $_uid;
    var $_promos;
    var $_nonins;
    var $_cats;
    var $_subs;
    var $watch_contacts;
    var $watch_last;
    
    function Watch($uid) {
	global $globals;
	$this->_uid = $uid;
	$this->_promos = new PromoNotifs($uid);
	$this->_nonins = new NoninsNotifs($uid);
	$this->_cats = new WatchCat();
	$this->_subs = new WatchSub($uid);
	$res = $globals->db->query("SELECT watch_contacts,watch_last FROM auth_user_quick WHERE user_id='$uid'");
	list($this->watch_contacts, $this->watch_last) = mysql_fetch_row($res);
	mysql_free_result($res);
    }

    function promos() {
	return $this->_promos->toRanges();
    }
}

class WatchCat {
    var $_data = Array();
    
    function WatchCat() {
	global $globals;
	$res = $globals->db->query("SELECT * FROM watch_cat");
	while($tmp = mysql_fetch_assoc($res)) $this->_data[$tmp['id']] = $tmp;
	mysql_free_result($res);
    }
}

class WatchSub {
    var $_uid;
    var $_data;

    function WatchSub($uid) {
	$this->_uid = $uid;
	global $globals;
	$res = $globals->db->query("SELECT cid FROM watch_sub WHERE uid='$uid'");
	while(list($c) = mysql_fetch_row($res)) $this->_data[$c] = $c;
	mysql_free_result($res);
    }
}

class PromoNotifs {
    var $_uid;
    var $_data = Array();

    function PromoNotifs($uid) {
	$this->_uid = $uid;
	global $globals;
	$res = $globals->db->query("SELECT promo FROM watch_promo WHERE uid='$uid' ORDER BY promo");
	while(list($p) = mysql_fetch_row($res)) $this->_data[intval($p)] = intval($p);
	mysql_free_result($res);
    }

    function add($p) {
	global $globals;
	$promo = intval($p);
	$globals->db->query("REPLACE INTO watch_promo (uid,promo) VALUES('{$this->_uid}',$promo)");
	$this->_data[$promo] = $promo;
	asort($this->_data);
    }
    
    function del($p) {
	global $globals;
	$promo = intval($p);
	$globals->db->query("DELETE FROM watch_promo WHERE uid='{$this->_uid}' AND promo=$promo");
	unset($this->_data[$promo]);
    }
    
    function addRange($_p1,$_p2) {
	global $globals;
	$p1 = intval($_p1);
	$p2 = intval($_p2);
	$values = Array();
	for($i = min($p1,$p2); $i<=max($p1,$p2); $i++) {
	    $values[] = "('{$this->_uid}',$i)";
	    $this->_data[$i] = $i;
	}
	$globals->db->query("REPLACE INTO watch_promo (uid,promo) VALUES ".join(',',$values));
	asort($this->_data);
    }

    function delRange($_p1,$_p2) {
	global $globals;
	$p1 = intval($_p1);
	$p2 = intval($_p2);
	$where = Array();
	for($i = min($p1,$p2); $i<=max($p1,$p2); $i++) {
	    $where[] = "promo=$i";
	    unset($this->_data[$i]);
	}
	$globals->db->query("DELETE FROM watch_promo WHERE uid='{$this->_uid}' AND (".join(' OR ',$where).')');
    }

    function toRanges() {
	$ranges = Array();
	$I = Array();
	foreach($this->_data as $promo) {
	    if(!isset($I[0])) {
		$I = Array($promo,$promo);
	    }
	    elseif($I[1]+1 == $promo) {
		$I[1] ++;
	    }
	    else {
		$ranges[] = $I;
		$I = Array($promo,$promo);
	    }
	}
	if(isset($I[0])) $ranges[] = $I;
	return $ranges;
    }
}


class NoninsNotifs {
    var $_uid;
    var $_data = Array();

    function NoninsNotifs($uid) {
	global $globals;
	$this->_uid = $uid;
	$res = $globals->db->query("SELECT  u.prenom,IF(u.epouse='',u.nom,u.epouse) AS nom, u.promo, u.user_id
				      FROM  watch_nonins  AS w
				INNER JOIN  auth_user_md5 AS u ON (u.user_id = w.ni_id)
                                     WHERE  w.uid = '$uid'
				  ORDER BY  promo,nom");
	while($tmp = mysql_fetch_assoc($res)) $this->_data[$tmp['user_id']] = $tmp;
	mysql_free_result($res);
    }

    function del($p) {
	global $globals;
	unset($this->_data["$p"]);
	$globals->db->query("DELETE FROM watch_nonins WHERE uid='{$this->_uid}' AND ni_id='$p'");
    }

    function add($p) {
	global $globals;
	$globals->db->query("INSERT INTO watch_nonins (uid,ni_id) VALUES('{$this->_uid}','$p')");
	$res = $globals->db->query("SELECT  prenom,IF(u.epouse='',u.nom,u.epouse),promo,user_id
				      FROM  auth_user_md5
				     WHERE  user_id='$p'");
	$this->_data["$p"] = mysql_fetch_assoc($res);
	mysql_free_result($res);
    }
}
 
?>
