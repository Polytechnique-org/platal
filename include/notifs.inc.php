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
        $Id: notifs.inc.php,v 1.20 2004-11-18 14:24:02 x2000habouzit Exp $
 ***************************************************************************/

define("WATCH_FICHE", 1);
define("WATCH_INSCR", 2);
define("WATCH_DEATH", 3);

function inscription_notifs_base($uid) {
    global $globals;
    $globals->db->query("REPLACE INTO  watch_sub (uid,cid)
                               SELECT  '$uid',id
			         FROM  watch_cat");
}

function register_watch_op($uid,$cid,$date='',$info='') {
    global $globals;
    $date = empty($date) ? 'NOW()' : "'$date'";
    $globals->db->query("REPLACE INTO watch_ops (uid,cid,known,date,info) VALUES('$uid','$cid',NOW(),$date,'$info')");
    if($cid == WATCH_FICHE) {
	$globals->db->query("UPDATE auth_user_md5 SET DATE=NOW() WHERE user_id='$uid'");
    } elseif($cid == WATCH_INSCR) {
	$globals->db->query("REPLACE INTO  contacts (uid,contact)
				   SELECT  uid,ni_id
				     FROM  watch_nonins
			            WHERE  ni_id='$uid'");
	$globals->db->query("DELETE FROM watch_nonins WHERE ni_id='$uid'");
    }
}

function getNbNotifs() {
    global $globals;
    if(!isset($_SESSION['uid'])) return 0;
    $uid = $_SESSION['uid'];

    $watchlast = isset($_SESSION['watch_last']) ? $_SESSION['watch_last'] : 0;

    $res = $globals->db->query("
    (
	    SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
		    wo.*, 1 AS contact, (u.perms IN ('admin','user')) AS inscrit
	      FROM  auth_user_quick AS q
	INNER JOIN  contacts        AS c  ON(q.user_id = c.uid)
	INNER JOIN  watch_ops       AS wo ON(wo.uid=c.contact)
	INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid AND ws.uid=c.uid)
	INNER JOIN  auth_user_md5   AS u  ON(u.user_id = wo.uid)
	 LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
	     WHERE  q.user_id = '$uid' AND FIND_IN_SET('contacts',q.watch_flags) AND wo.known > $watchlast
    ) UNION DISTINCT (
	    SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
		    wo.*, NOT (c.contact IS NULL) AS contact, (u.perms IN ('admin','user')) AS inscrit
	      FROM  watch_promo     AS w
	INNER JOIN  auth_user_md5   AS u  USING(promo)
	INNER JOIN  auth_user_quick AS q  ON(q.user_id = w.uid)
	 LEFT JOIN  contacts        AS c  ON(w.uid = c.uid AND c.contact=u.user_id)
	INNER JOIN  watch_ops       AS wo ON(wo.uid=u.user_id)
	INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid AND ws.uid=w.uid)
	INNER JOIN  watch_cat       AS wc ON(wc.id=wo.cid AND wc.frequent=0)
	 LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
	     WHERE  w.uid = '$uid' AND wo.known > $watchlast
    ) UNION DISTINCT (
	    SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
		    wo.*, 0 AS contact, (u.perms IN ('admin','user')) AS inscrit
	      FROM  watch_nonins    AS w
	INNER JOIN  auth_user_quick AS q  ON(q.user_id = w.uid)
	INNER JOIN  auth_user_md5   AS u  ON(w.ni_id=u.user_id)
	INNER JOIN  watch_ops       AS wo ON(wo.uid=u.user_id)
	INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid AND ws.uid=w.uid)
	INNER JOIN  watch_cat       AS wc ON(wc.id=wo.cid)
	 LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
	     WHERE  w.uid = '$uid' AND wo.known > $watchlast
    )");
    $n = mysql_num_rows($res);
    mysql_free_result($res);
    $url = smarty_modifier_url('carnet/panel.php');
    if($n==0) {
	return;
    }
    if($n==1) {
	return "<a href='$url'>1 évènement !</a>";
    }
    return "<a href='$url'>$n évènements !</a>";
}

class AllNotifs {
    var $_cats = Array();
    var $_data = Array();

    function AllNotifs() {
	global $globals;
	
	$res = $globals->db->query("SELECT * FROM watch_cat");
	while($tmp = mysql_fetch_assoc($res)) $this->_cats[$tmp['id']] = $tmp;
	mysql_free_result($res);
	
	$res = $globals->db->query("
	(
		SELECT  q.user_id AS aid, v.prenom AS aprenom, IF(v.epouse='',v.nom,v.prenom) AS anom,
			b.alias AS abestalias, (v.flags='femme') AS sexe,
			u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
			wo.*, 1 AS contact, (u.perms IN ('admin','user')) AS inscrit
	          FROM  auth_user_quick AS q
	    INNER JOIN  auth_user_md5   AS v  USING(user_id)
	    INNER JOIN  aliases         AS b  ON(q.user_id = b.id AND FIND_IN_SET('bestalias',b.flags))
	    INNER JOIN  contacts        AS c  ON(q.user_id = c.uid)
	    INNER JOIN  watch_ops       AS wo ON(wo.uid=c.contact AND wo.known > q.watch_last)
	    INNER JOIN  watch_sub       AS ws ON(ws.uid=q.user_id AND wo.cid=ws.cid)
	    INNER JOIN  auth_user_md5   AS u  ON(u.user_id = wo.uid)
	     LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
	         WHERE  q.watch_flags=3
	) UNION DISTINCT (
		SELECT  q.user_id AS aid, v.prenom AS aprenom, IF(v.epouse='',v.nom,v.prenom) AS anom,
			b.alias AS abestalias, (v.flags='femme') AS sexe,
			u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
			wo.*, NOT (c.contact IS NULL) AS contact, (u.perms IN ('admin','user')) AS inscrit
	          FROM  auth_user_quick AS q
	    INNER JOIN  auth_user_md5   AS v  USING(user_id)
	    INNER JOIN  aliases         AS b  ON(q.user_id = b.id AND FIND_IN_SET('bestalias',b.flags))
	    INNER JOIN  watch_promo     AS w  ON(w.uid=q.user_id)
	    INNER JOIN  auth_user_md5   AS u  USING(promo)
	     LEFT JOIN  contacts        AS c  ON(w.uid = c.uid AND c.contact=u.user_id)
	    INNER JOIN  watch_sub       AS ws ON(ws.uid=w.uid)
	    INNER JOIN  watch_cat       AS wc ON(wc.id=wo.cid AND wc.frequent=0)
            INNER JOIN  watch_ops       AS wo ON(wo.cid=ws.cid AND wo.uid=u.user_id AND wo.known > q.watch_last)
	     LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
		 WHERE  q.watch_flags=3 OR q.watch_flags=1
	) UNION DISTINCT (
		SELECT  q.user_id AS aid, v.prenom AS aprenom, IF(v.epouse='',v.nom,v.prenom) AS anom,
			b.alias AS abestalias, (v.flags='femme') AS sexe,
			u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
			wo.*, 0 AS contact, (u.perms IN ('admin','user')) AS inscrit
	          FROM  auth_user_quick AS q
	    INNER JOIN  auth_user_md5   AS v  USING(user_id)
	    INNER JOIN  aliases         AS b  ON(q.user_id = b.id AND FIND_IN_SET('bestalias',b.flags))
	    INNER JOIN  watch_nonins    AS w  ON(w.uid=q.user_id)
	    INNER JOIN  auth_user_md5   AS u  ON(w.ni_id=u.user_id)
	    INNER JOIN  watch_sub       AS ws ON(ws.uid=w.uid)
	    INNER JOIN  watch_cat       AS wc ON(wc.id=wo.cid)
	    INNER JOIN  watch_ops       AS wo ON(wo.cid=ws.cid AND wo.uid=u.user_id AND wo.known > q.watch_last)
	     LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
		 WHERE  q.watch_flags=3 OR q.watch_flags=1
	)
	ORDER BY  cid,promo,nom");

	while($tmp = mysql_fetch_assoc($res)) {
	    $aid = $tmp['aid'];
	    $this->_data[$aid] = Array("prenom" => $tmp['aprenom'], 'nom' => $tmp['anom'],
				       'bestalias'=>$tmp['abestalias'], 'sexe' => $tmp['sexe']);
	    unset($tmp['aprenom'],$tmp['anom'],$tmp['abestalias'],$tmp['aid'],$tmp['sexe']);
	    $this->_data[$aid]['data'][$tmp['cid']][] = $tmp;
	}
    }
}

class Notifs {
    var $_uid;
    var $_cats = Array();
    var $_data = Array();
    
    function Notifs($uid,$up=false) {
	global $globals;
	$this->_uid = $uid;
	
	$res = $globals->db->query("SELECT * FROM watch_cat");
	while($tmp = mysql_fetch_assoc($res)) $this->_cats[$tmp['id']] = $tmp;
	mysql_free_result($res);

	$lastweek = date('YmdHis',mktime() - 7*24*60*60);

	$res = $globals->db->query("
	(
		SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
			wo.*, 1 AS contact, (u.perms IN ('admin','user')) AS inscrit
		  FROM  auth_user_quick AS q
	    INNER JOIN  contacts        AS c  ON(q.user_id = c.uid)
	    INNER JOIN  watch_ops       AS wo ON(wo.uid=c.contact)
	    INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid AND ws.uid=q.user_id)
	    INNER JOIN  auth_user_md5   AS u  ON(u.user_id = wo.uid)
	     LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
		 WHERE  q.user_id = '$uid' AND FIND_IN_SET('contacts',q.watch_flags) AND wo.known > $lastweek
	) UNION DISTINCT (
		SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
			wo.*, NOT (c.contact IS NULL) AS contact, (u.perms IN ('admin','user')) AS inscrit
		  FROM  watch_promo     AS w
	    INNER JOIN  auth_user_md5   AS u  USING(promo)
	     LEFT JOIN  contacts        AS c  ON(w.uid = c.uid AND c.contact=u.user_id)
	    INNER JOIN  watch_ops       AS wo ON(wo.uid=u.user_id)
	    INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid AND ws.uid=w.uid)
	    INNER JOIN  watch_cat       AS wc ON(wc.id=wo.cid AND wc.frequent=0)
	     LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
		 WHERE  w.uid = '$uid' AND wo.known > $lastweek
	) UNION DISTINCT (
		SELECT  u.promo, u.prenom, IF(u.epouse='',u.nom,u.epouse) AS nom, a.alias AS bestalias,
			wo.*, 0 AS contact, (u.perms IN ('admin','user')) AS inscrit
		  FROM  watch_nonins    AS w
	    INNER JOIN  auth_user_md5   AS u  ON(w.ni_id=u.user_id)
	    INNER JOIN  watch_ops       AS wo ON(wo.uid=u.user_id)
	    INNER JOIN  watch_sub       AS ws ON(wo.cid=ws.cid AND ws.uid=w.uid)
	    INNER JOIN  watch_cat       AS wc ON(wc.id=wo.cid)
	     LEFT JOIN  aliases         AS a  ON(u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
		 WHERE  w.uid = '$uid' AND wo.known > $lastweek
	)
	ORDER BY  cid,promo,nom");
	while($tmp = mysql_fetch_assoc($res)) {
	    $this->_data[$tmp['cid']][$tmp['promo']][] = $tmp;
	}

	if($up) {
	    $globals->db->query("UPDATE auth_user_quick SET watch_last=NOW() WHERE user_id='$uid'");
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
    var $watch_mail;
    
    function Watch($uid) {
	global $globals;
	$this->_uid = $uid;
	$this->_promos = new PromoNotifs($uid);
	$this->_nonins = new NoninsNotifs($uid);
	$this->_subs = new WatchSub($uid);
	$res = $globals->db->query("SELECT  FIND_IN_SET('contacts',watch_flags),FIND_IN_SET('mail',watch_flags)
				      FROM  auth_user_quick
				     WHERE  user_id='$uid'");
	list($this->watch_contacts,$this->watch_mail) = mysql_fetch_row($res);
	mysql_free_result($res);
	
	$res = $globals->db->query("SELECT * FROM watch_cat");
	while($tmp = mysql_fetch_assoc($res)) $this->_cats[$tmp['id']] = $tmp;
	mysql_free_result($res);
    }

    function saveFlags() {
	global $globals;
	$flags = "";
	if($this->watch_contacts) $flags = "contacts";
	if($this->watch_mail) $flags .= ($flags ? ',' : '')."mail";
	$globals->db->query("UPDATE auth_user_quick SET watch_flags='$flags' WHERE user_id='{$this->_uid}'");
	
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
