#!/usr/bin/php4 -q
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
        $Id: watch.notifs.php,v 1.2 2004-11-05 13:30:12 x2000habouzit Exp $
 ***************************************************************************/
/*
 * verifie qu'il n'y a pas d'incoherences dans les tables de jointures
 * 
 * $Id: watch.notifs.php,v 1.2 2004-11-05 13:30:12 x2000habouzit Exp $
*/ 

require('./connect.db.inc.php');

mysql_query("LOCK TABLE watch_ops");

// be smart here


function search_notifs($sql, &$array, &$ops) {
    $res = mysql_query($sql);
    while(list($uid,$prenom,$nom,$forlife,$nuid,$nprenom,$nnom,$npromo,$nforlife,$nop,$ndate) = mysql_fetch_row($res)) {
	$array[$uid]['prenom'] = $prenom;
	$array[$uid]['nom']    = $nom;
	$array[$uid]['forlife']= $forlife;
        $array[$uid][$nop][$nuid] = $npromo.strtolower($nnom);
	$ops[$nop][$nuid] = Array('prenom'=>$nprenom,'nom'=>$nnom,'forlife'=>$nforlife,'promo'=>$npromo,'date'=>$ndate);
    }
    mysql_free_result($res);
}

function print_notif($uid,$op) {
    global $ops;
    echo "    - ({$ops[$op][$uid]['promo']}) {$ops[$op][$uid]['prenom']} {$ops[$op][$uid]['nom']}, le {$ops[$op][$uid]['date']}\n";
}

function do_notif(&$not) {
    echo "Notifications pour {$not['prenom']} {$not['nom']} <{$not['forlife']}@polytechnique.org>\n";
    if(isset($not['ins'])) {
	asort($not['ins']);
	echo "  Ces camarades viennent de s'inscrire :\n";
	foreach($not['ins'] as $uid=>$foo) print_notif($uid,'ins');
    }
    if(isset($not['fiche'])) {
	asort($not['fiche']);
	echo "  Ces camarades ont mis leur fiche à jour :\n";
	foreach($not['fiche'] as $uid=>$foo) print_notif($uid,'fiche');
    }
    if(isset($not['death'])) {
	asort($not['death']);
	echo "  Ces camarades sont décédés :\n";
	foreach($not['death'] as $uid=>$foo) print_notif($uid,'death');
    }
    echo "\n";
}

$notifs = Array();
$ops = Array();

$sql = "SELECT  u.user_id, u.prenom, IF(u.epouse='',u.nom,u.epouse), a.alias,
                v.user_id, v.prenom, IF(v.epouse='',v.nom,v.epouse) AS nom, v.promo, b.alias,
                o.op, IF(o.op='death',v.deces,IF(o.op='fiche',v.date,DATE_FORMAT(v.date_ins,'%Y-%m-%d')))
          FROM  auth_user_md5 AS u
    INNER JOIN  aliases       AS a ON (u.user_id = a.id AND a.type='a_vie')
    INNER JOIN  contacts      AS c ON (u.user_id = c.uid)
    INNER JOIN  watch_ops     AS o ON (c.contact = o.user_id)
    INNER JOIN  auth_user_md5 AS v ON (o.user_id = v.user_id)
    INNER JOIN  aliases       AS b ON (o.user_id = b.id AND b.type='a_vie')
         WHERE  FIND_IN_SET('contacts',u.watch) AND u.user_id!=v.user_id
";
search_notifs($sql,$notifs,$ops);

$sql = "SELECT  u.user_id, u.prenom, IF(u.epouse='',u.nom,u.epouse), a.alias,
                v.user_id, v.prenom, IF(v.epouse='',v.nom,v.epouse) AS nom, v.promo, b.alias,
                o.op, IF(o.op='death',v.deces,IF(o.op='fiche',v.date,DATE_FORMAT(v.date_ins,'%Y-%m-%d')))
          FROM  auth_user_md5 AS u
    INNER JOIN  aliases       AS a ON (u.user_id = a.id AND a.type='a_vie')
    INNER JOIN  watch         AS w ON (u.user_id = w.user_id AND w.type='promo')
    INNER JOIN  auth_user_md5 AS v ON (v.promo = w.arg)
    INNER JOIN  watch_ops     AS o ON (v.user_id = o.user_id AND o.op != 'fiche')
    LEFT  JOIN  aliases       AS b ON (o.user_id = b.id AND b.type='a_vie')
         WHERE  u.user_id!=v.user_id
";
search_notifs($sql,$notifs,$ops);

$sql = "SELECT  u.user_id, u.prenom, IF(u.epouse='',u.nom,u.epouse), a.alias,
                v.user_id, v.prenom, IF(v.epouse='',v.nom,v.epouse) AS nom, v.promo, b.alias,
                o.op, IF(o.op='death',v.deces,IF(o.op='fiche',v.date,DATE_FORMAT(v.date_ins,'%Y-%m-%d')))
          FROM  auth_user_md5 AS u
    INNER JOIN  aliases       AS a ON (u.user_id = a.id AND a.type='a_vie')
    INNER JOIN  watch         AS w ON (u.user_id = w.user_id AND w.type='non-inscrit')
    INNER JOIN  auth_user_md5 AS v ON (v.user_id = w.arg)
    INNER JOIN  watch_ops     AS o ON (v.user_id = o.user_id)
    LEFT  JOIN  aliases       AS b ON (o.user_id = b.id AND b.type='a_vie')
	 WHERE  u.user_id!=v.user_id
";
search_notifs($sql,$notifs,$ops);


/*******************************************************************************
 * DELETE dead pple from contacts for those who wants it, and from the watch
 */
mysql_query("DELETE FROM  contacts
                   USING  contacts      AS c
	      INNER JOIN  auth_user_md5 AS u ON (u.user_id = c.uid AND FIND_IN_SET('deaths',u.watch))
	      INNER JOIN  auth_user_md5 AS v ON (c.contact = v.user_id AND v.deces!='0000-00-00')");

mysql_query("DELETE FROM  watch
                   USING  watch         AS w
	      INNER JOIN  auth_user_md5 AS u ON (u.user_id = w.arg AND w.type='non-inscrit' AND u.deces!='0000-00-00')");


/*******************************************************************************
 * INSERT watched nonins into contacts
 */

mysql_query("INSERT INTO  contacts (uid,contact)
                  SELECT  w.user_id,w.arg
                    FROM  watch         AS w
	      INNER JOIN  auth_user_md5 AS u ON (u.user_id = w.arg AND w.type='non-inscrit' AND u.perms!='non-inscrit')");

mysql_query("DELETE FROM  watch
                   USING  watch         AS w
	      INNER JOIN  auth_user_md5 AS u ON (u.user_id = w.arg AND w.type='non-inscrit' AND u.perms!='non-inscrit')");

//mysql_query("DELETE FROM watch_ops");
mysql_query("UNLOCK TABLE watch_ops");

/*******************************************************************************
 * DO notifications HERE ONLY (slow)
 */

foreach($notifs as $user=>$notiflist) do_notif($notiflist);

?>
