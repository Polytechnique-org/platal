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
        $Id: notifs.inc.php,v 1.9 2004-11-06 18:18:44 x2000habouzit Exp $
 ***************************************************************************/

define("WATCH_FICHE", 1);
define("WATCH_INSCR", 2);
define("WATCH_DEATH", 3);

function register_watch_op($uid,$cid,$date='',$info='') {
    global $globals;
    $date = empty($date) ? 'NOW()' : "'$date'";
    $globals->db->query("REPLACE INTO watch_ops (uid,cid,known,date,info) VALUES('$uid','$cid',NOW(),$date,'$info')");
    if($cid == WATCH_FICHE) {
	$globals->db->query("UPDATE auth_user_md5 SET DATE=NOW() WHERE user_id='$uid'");
    }
}

class Notifs {
    var $_uid;
    var $_cats = Array();
    var $_data = Array();
    
    function Notifs($uid) {
	global $globals;
	$this->_uid = $uid;
	
	$res = $globals->db->query("SELECT * FROM watch_cat");
	while($tmp = mysql_fetch_assoc($res)) $this->_cats[$tmp['id']] = $tmp;
	mysql_free_result($res);

	$res = $globals->db->query("SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS forlife, wo.*
				      FROM  auth_user_quick AS q
				INNER JOIN  contacts        AS c  ON(q.user_id = c.uid)
	                        INNER JOIN  watch_ops       AS wo ON(wo.uid=c.contact)
				INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid AND ws.uid=c.uid)
				INNER JOIN  auth_user_md5   AS u  ON(u.user_id = wo.uid)
				 LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND a.type='a_vie')
				     WHERE  q.user_id = '$uid' AND q.watch_contacts=1
				  ORDER BY  wo.cid,promo,nom");
	while($tmp = mysql_fetch_assoc($res)) {
	    $this->_data[$tmp['cid']][$tmp['promo']][$tmp['uid']] = $tmp;
	}
	
	$res = $globals->db->query("SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS forlife, wo.*
				      FROM  watch_promo     AS w
				INNER JOIN  auth_user_md5   AS u  USING(promo)
	                        INNER JOIN  watch_ops       AS wo ON(wo.uid=u.user_id)
				INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid)
				INNER JOIN  watch_cat       AS wc ON(wc.id=wo.cid AND wc.frequent=0)
				 LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND a.type='a_vie')
				     WHERE  w.uid = '$uid'
				  ORDER BY  wo.cid,promo,nom");
	while($tmp = mysql_fetch_assoc($res)) {
	    $this->_data[$tmp['cid']][$tmp['promo']][$tmp['uid']] = $tmp;
	}
	
	$res = $globals->db->query("SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS forlife, wo.*
				      FROM  watch_nonins    AS w
				INNER JOIN  auth_user_md5   AS u  ON(w.ni_id=u.user_id)
	                        INNER JOIN  watch_ops       AS wo ON(wo.uid=u.user_id)
				INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid)
				INNER JOIN  watch_cat       AS wc ON(wc.id=wo.cid)
				 LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND a.type='a_vie')
				     WHERE  w.uid = '$uid'
				  ORDER BY  wo.cid,promo,nom");
	while($tmp = mysql_fetch_assoc($res)) {
	    $this->_data[$tmp['cid']][$tmp['promo']][$tmp['uid']] = $tmp;
	}
    }
}

class Watch {
    var $_uid;
    var $_promos;
    var $_nonins;
    var $_cats = Array();
    var $_subs;
    var $watch_contacts;
    
    function Watch($uid) {
	global $globals;
	$this->_uid = $uid;
	$this->_promos = new PromoNotifs($uid);
	$this->_nonins = new NoninsNotifs($uid);
	$this->_subs = new WatchSub($uid);
	$res = $globals->db->query("SELECT watch_contacts FROM auth_user_quick WHERE user_id='$uid'");
	list($this->watch_contacts) = mysql_fetch_row($res);
	mysql_free_result($res);
	
	$res = $globals->db->query("SELECT * FROM watch_cat");
	while($tmp = mysql_fetch_assoc($res)) $this->_cats[$tmp['id']] = $tmp;
	mysql_free_result($res);
    }

    function cats() {
	return $this->_cats;
    }

    function subs($i) {
	return $this->_subs->_data[$i];
    }
    
    function promos() {
	return $this->_promos->toRanges();
    }
    
    function nonins() {
	return $this->_nonins->_data;
    }
}

class WatchSub {
    var $_uid;
    var $_data = Array();

    function WatchSub($uid) {
	$this->_uid = $uid;
	global $globals;
	$res = $globals->db->query("SELECT cid FROM watch_sub WHERE uid='$uid'");
	while(list($c) = mysql_fetch_row($res)) $this->_data[$c] = $c;
	mysql_free_result($res);
    }

    function update($ind) {
	global $globals;
	$this->_data = Array();
	$globals->db->query("DELETE FROM watch_sub WHERE uid='{$this->_uid}'");
	foreach($_REQUEST[$ind] as $key=>$val) {
	    $globals->db->query("INSERT INTO  watch_sub
	                              SELECT  '{$this->_uid}',id
				        FROM  watch_cat
				       WHERE  id='$key'");
	    if(mysql_affected_rows()) $this->_data[$key] = $key;
	}
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
	$res = $globals->db->query("SELECT  prenom,IF(epouse='',nom,epouse) AS nom,promo,user_id
				      FROM  auth_user_md5
				     WHERE  user_id='$p'");
	$this->_data["$p"] = mysql_fetch_assoc($res);
	mysql_free_result($res);
    }
}
 
?>
