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

define("WATCH_FICHE", 1);
define("WATCH_INSCR", 2);
define("WATCH_DEATH", 3);

function inscription_notifs_base($uid) {
    global $globals;
    $globals->xdb->execute('REPLACE INTO  watch_sub (uid,cid) SELECT {?},id FROM watch_cat', $uid);
}

function register_watch_op($uid,$cid,$date='',$info='') {
    global $globals;
    $date = empty($date) ? 'NOW()' : "'$date'";
    $globals->xdb->execute('REPLACE INTO watch_ops (uid,cid,known,date,info) VALUES({?},{?},NOW(),{?},{?})',
            $uid, $cid, $date, $info);
    if($cid == WATCH_FICHE) {
	$globals->xdb->execute('UPDATE auth_user_md5 SET DATE=NOW() WHERE user_id={?}', $uid);
    } elseif($cid == WATCH_INSCR) {
	$globals->xdb->execute('REPLACE INTO  contacts (uid,contact)
				      SELECT  uid,ni_id
				        FROM  watch_nonins
			               WHERE  ni_id={?}', $uid);
	$globals->xdb->execute('DELETE FROM watch_nonins WHERE ni_id={?}', $uid);
    }
}

function select_notifs_base($base_watch, $champ_w, $champ_u, $watcher, $watcher_detail, $is_contact, $not_frequent, $recent, $where_clause) {
	// base_watch : la base dans laquelle on regarde
	// is_contact  : test si la personne regardée est un contact
	// not_frequent : watch seulement les evts nons fréquents
	// recent : evenement recent
	
	// les réponses sont-elles des contacts
	if ($champ_w == 'contact')
		$contact = '1';
	elseif ($is_contact)
		$contact = 'NOT (c.contact IS NULL)';
	else
		$contact = '0';

	if ($watcher_detail) $watcher = true;
	// traduction de la clause not_frequent
	if ($not_frequent)
		$not_frequent = " AND wc.frequent=0";
		
	// traduction de la clause recent
	if ($recent)
		$recent = " AND wo.known > q.watch_last";

	$sql = "
	  ( SELECT u.promo,
	           u.prenom,
		   IF(u.epouse='',u.nom,u.epouse) AS nom,
		   a.alias AS bestalias,
		   wo.*,
		   $contact AS contact,
		   (u.perms IN('admin','user')) AS inscrit";
	if ($watcher_detail) $sql.="
	           w.uid AS aid,
		   v.prenom AS aprenom,
		   IF(v.epouse='',v.nom,v.prenom) AS anom,
		   b.alias AS abestalias,
		   (v.flags='femme') AS sexe"; 
	$sql .= "
	      FROM ".$base_watch." AS w
	INNER JOIN auth_user_md5   AS u  ON(u.$champ_u = w.$champ_w)";
	if ($watcher) $sql .="
	INNER JOIN auth_user_quick AS q  ON(q.user_id = w.uid)";
	if ($watcher_detail) $sql .="
	INNER JOIN auth_user_md5   AS v  ON(v.user_id = q.user_id)
	INNER JOIN aliases         AS b  ON(b.id = q.user_id AND FIND_IN_SET('bestalias', b.flags))";
	if ($is_contact) $sql .="
	 LEFT JOIN contacts        AS c  ON(c.uid = w.uid AND c.contact = u.user_id)";
	$sql .="
	INNER JOIN watch_ops       AS wo ON(wo.uid = u.user_id".$recent.")
	INNER JOIN watch_sub       AS ws ON(ws.cid = wo.cid AND ws.uid = w.uid)
	INNER JOIN watch_cat       AS wc ON(wc.id = wo.cid".$not_frequent.")
	 LEFT JOIN aliases         AS a  ON(a.id = u.user_id AND FIND_IN_SET('bestalias', a.flags))
	     WHERE $where_clause )";
	 return $sql;
}

// récupère les evenements de $wuid, avec ou sans details, depuis $last, sous surveillance ou pas, ordonnés ou pas
function select_notifs($wuid, $details, $last, $wflag, $order) {
	$recent = ($last == 'watch_last');

	// équivalent de FIND_IN_SET('contacts', q.watch_flags) mais en plus rapide
	$contactflag = "(q.watch_flags=1 OR q.watch_flags=3)";

	$where_clause = "";
	if ($wuid != 'all')
		$where_clause .= "w.uid = $wuid AND ";
	if (!$recent)
		$where_clause .= "wo.known > $last AND ";
	if ($wflag)
		$where_clause .= $contactflag." AND ";
	$where_clause = substr($where_clause, 0, -5);

	$sql = 
	select_notifs_base('contacts','contact','user_id','q',$details,false,false,$recent, $where_clause.($wflag?'':" AND $contactflag"))."
    UNION DISTINCT ".
	select_notifs_base('watch_promo','promo','promo',false,$details,'is contact','not frequent',$recent, $where_clause)."
    UNION DISTINCT ".
    	select_notifs_base("watch_nonins",'ni_id', 'user_id',false,$details,false,false,$recent, $where_clause);
	
	if ($order) $sql.="
	  ORDER BY cid, promo, nom";

	return $sql;
}

function getNbNotifs() {
    global $globals;
    if (!Session::has('uid')) {
        return 0;
    }
    $uid       = Session::getInt('uid', -1);
    $watchlast = Session::get('watch_last');

    // selectionne les notifs de uid, sans detail sur le watcher, depuis $watchlast, meme ceux sans surveillance, non ordonnés
    $res = $globals->xdb->query(select_notifs('{?}', false, '{?}', false, false), $uid, $watchlast, $uid, $watchlast, $uid, $watchlast);
    $n   = $res->numRows();
    $res->free();
    $url = smarty_modifier_url('carnet/panel.php');
    if($n==0) { return; }
    if($n==1) { return "<a href='$url'>1 évènement !</a>"; }
    return "<a href='$url'>$n évènements !</a>";
}

class AllNotifs {
    var $_cats = Array();
    var $_data = Array();

    function AllNotifs() {
	global $globals;
	
	$res = $globals->xdb->iterator("SELECT * FROM watch_cat");
	while($tmp = $res->next()) {
            $this->_cats[$tmp['id']] = $tmp;
        }

	// recupère tous les watchers, avec détails des watchers, a partir du watch_last de chacun, seulement ceux qui sont surveillés, ordonnés
	$res = $globals->xdb->iterator(select_notifs('all', true, 'watch_last', 'watch_flag', 'order'));

	while($tmp = $res->next()) {
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
	
	$res = $globals->xdb->iterator("SELECT * FROM watch_cat");
	while($tmp = $res->next()) {
            $this->_cats[$tmp['id']] = $tmp;
        }

	$lastweek = date('YmdHis',mktime() - 7*24*60*60);

	// recupere les notifs du watcher $uid, sans detail sur le watcher, depuis la semaine dernière, meme ceux sans surveillance, ordonnés
	$res = $globals->xdb->iterator(select_notifs('{?}', false, $lastweek, false, 'order'), $uid, $uid, $uid);
	while($tmp = $res->next()) {
	    $this->_data[$tmp['cid']][$tmp['promo']][] = $tmp;
	}

	if($up) {
	    $globals->xdb->execute('UPDATE auth_user_quick SET watch_last=NOW() WHERE user_id={?}', $uid);
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
	$res = $globals->xdb->query("SELECT  FIND_IN_SET('contacts',watch_flags),FIND_IN_SET('mail',watch_flags)
				       FROM  auth_user_quick
				      WHERE  user_id={?}", $uid);
	list($this->watch_contacts,$this->watch_mail) = $res->fetchOneRow();
	
	$res = $globals->xdb->iterator("SELECT * FROM watch_cat");
	while($tmp = $res->next()) {
            $this->_cats[$tmp['id']] = $tmp;
        }
    }

    function saveFlags() {
	global $globals;
	$flags = "";
	if($this->watch_contacts) $flags = "contacts";
	if($this->watch_mail) $flags .= ($flags ? ',' : '')."mail";
	$globals->xdb->execute('UPDATE auth_user_quick SET watch_flags={?} WHERE user_id={?}', $flags, $this->_uid);
	
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
	$res = $globals->xdb->iterRow('SELECT cid FROM watch_sub WHERE uid={?}', $uid);
	while(list($c) = $res->next()) {
            $this->_data[$c] = $c;
        }
    }

    function update($ind) {
	global $globals;
	$this->_data = Array();
	$globals->xdb->execute('DELETE FROM watch_sub WHERE uid={?}', $this->_uid);
	foreach(Env::getMixed($ind) as $key=>$val) {
	    $globals->xdb->query('INSERT INTO watch_sub SELECT {?},id FROM watch_cat WHERE id={?}', $this->_uid, $key);
	    if(mysql_affected_rows()) {
                $this->_data[$key] = $key;
            }
	}
    }
}

class PromoNotifs {
    var $_uid;
    var $_data = Array();

    function PromoNotifs($uid) {
	$this->_uid = $uid;
	global $globals;
	$res = $globals->xdb->iterRow('SELECT promo FROM watch_promo WHERE uid={?} ORDER BY promo', $uid);
	while (list($p) = $res->next()) {
            $this->_data[intval($p)] = intval($p);
        }
    }

    function add($p) {
	global $globals;
	$promo = intval($p);
	$globals->xdb->execute('REPLACE INTO watch_promo (uid,promo) VALUES({?},{?})', $this->_uid, $promo);
	$this->_data[$promo] = $promo;
	asort($this->_data);
    }
    
    function del($p) {
	global $globals;
	$promo = intval($p);
	$globals->xdb->execute('DELETE FROM watch_promo WHERE uid={?} AND promo={?}', $this->_uid, $promo);
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
	$globals->xdb->execute('REPLACE INTO watch_promo (uid,promo) VALUES '.join(',',$values));
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
	$globals->xdb->execute('DELETE FROM watch_promo WHERE uid={?} AND ('.join(' OR ',$where).')', $this->_uid);
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
	$res = $globals->xdb->iterator("SELECT  u.prenom,IF(u.epouse='',u.nom,u.epouse) AS nom, u.promo, u.user_id
                                          FROM  watch_nonins  AS w
                                    INNER JOIN  auth_user_md5 AS u ON (u.user_id = w.ni_id)
                                         WHERE  w.uid = {?}
				      ORDER BY  promo,nom", $uid);
	while($tmp = $res->next()) {
            $this->_data[$tmp['user_id']] = $tmp;
        }
    }

    function del($p) {
	global $globals;
	unset($this->_data["$p"]);
	$globals->xdb->execute('DELETE FROM watch_nonins WHERE uid={?} AND ni_id={?}', $this->_uid, $p);
    }

    function add($p) {
	global $globals;
	$globals->xdb->execute('INSERT INTO watch_nonins (uid,ni_id) VALUES({?},{?})', $this->_uid, $p);
	$res = $globals->xdb->query('SELECT  prenom,IF(epouse="",nom,epouse) AS nom,promo,user_id
                                       FROM  auth_user_md5
                                      WHERE  user_id={?}', $p);
	$this->_data["$p"] = $res->fetchOneAssoc();
    }
}
 
?>
