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


require_once("xorg.inc.php");
new_simple_page('fiche.tpl',AUTH_COOKIE);

require_once('applis.func.inc.php');
require_once('user.func.inc.php');

//$isnetscape = !empty($_SESSION['skin_compatible']);

if (!isset($_REQUEST['user']) && !isset($_REQUEST['mat'])) {
    $page->kill("cette page n'existe pas");
}

if (isset($_REQUEST['user'])) {
    $login = get_user_forlife($_REQUEST['user']);
    if ($login === false) {
        $page->kill("");
    }
}

if (isset($_REQUEST['mat'])) {
    $res = $globals->db->query("SELECT  alias 
                                  FROM  aliases       AS a
                            INNER JOIN  auth_user_md5 AS u ON (a.id=u.user_id AND a.type='a_vie')
                                 WHERE  matricule='{$_REQUEST['mat']}'");
    if (!(list($login) = mysql_fetch_row($res))) {
        $page->kill("cette page n'existe pas");
    }
    mysql_free_result($res);
}

$new = (isset($_REQUEST["modif"]) && $_REQUEST["modif"]=="new");

$reqsql = "SELECT  u.prenom, u.nom, u.epouse,
                   IF(gp.nat='',gp.pays,gp.nat) AS nationalite, gp.a2 AS iso3166,
		   u.user_id, a.alias AS forlife, a2.alias AS bestalias,
                   u.matricule, u.perms IN ('admin','user') AS inscrit,
		   FIND_IN_SET('femme', u.flags) AS sexe, u.deces != 0 AS dcd, u.deces,
		   u.date, u.cv, sections.text AS section, u.mobile, u.web,
		   u.libre, u.promo, c.uid IS NOT NULL AS is_contact, p.x, p.y,

                   m.expertise != '' AS is_referent
                   
	     FROM  auth_user_md5  AS u
       INNER JOIN  aliases        AS a  ON (u.user_id=a.id AND a.type='a_vie')
       INNER JOIN  aliases        AS a2 ON (u.user_id=a2.id AND FIND_IN_SET('bestalias',a2.flags))
        LEFT JOIN  contacts       AS c  ON (c.uid = {$_SESSION['uid']} and c.contact = u.user_id)
        LEFT JOIN  geoloc_pays    AS gp ON (gp.a2 = u.nationalite)
       INNER JOIN  sections             ON (sections.id = u.section)
        LEFT JOIN  photo          AS p  ON (p.uid = u.user_id) 
        LEFT JOIN  mentor         AS m  ON (m.uid = u.user_id)
            WHERE  a.alias = '$login'";

$result = $globals->db->query($reqsql);
$user = mysql_fetch_assoc($result);
mysql_free_result($result);

$title = $user['prenom'] . ' ' .  empty($user['epouse']) ? $user['nom'] : $user['epouse'];
$page->assign('xorg_title', $title);

// photo

$photo="getphoto.php?x=".$user['forlife'].($new ? '&amp;modif=new' : '');

if(!isset($user['y']) and !isset($user['x'])) {
    list($user['x'], $user['y']) = getimagesize("images/none.png");
}
if(!isset($user['y']) or $user['y'] < 1) $user['y']=1;
if(!isset($user['x']) or $user['x'] < 1) $user['x']=1;
if($user['x'] > 240){
    $user['y'] = (integer)($user['y']*240/$user['x']);
    $user['x'] = 240;
}
if($user['y'] > 300){
    $user['x'] = (integer)($user['x']*300/$user['y']);
    $user['y'] = 300;
}
if($user['x'] < 160){
    $user['y'] = (integer)($user['y']*160/$user['x']);
    $user['x'] = 160;
}
$page->assign('photo_url', $photo);
$page->assign_by_ref('x', $user);

//recuperation des infos professionnelles
$sql = "SELECT  e.entreprise, s.label as secteur , ss.label as sous_secteur , f.fonction_fr as fonction,
                e.poste, e.adr1, e.adr2, e.adr3, e.cp, e.ville,
                gp.pays, gr.name, e.tel, e.fax
          FROM  entreprises AS e
     LEFT JOIN  emploi_secteur AS s ON(e.secteur = s.id)
     LEFT JOIN  emploi_ss_secteur AS ss ON(e.ss_secteur = ss.id AND e.secteur = ss.secteur)
     LEFT JOIN  fonctions_def AS f ON(e.fonction = f.id)
     LEFT JOIN  geoloc_pays AS gp ON (gp.a2 = e.pays)
     LEFT JOIN  geoloc_region AS gr ON (gr.a2 = e.pays and gr.region = e.region)
         WHERE  e.uid = {$user['user_id']}
      ORDER BY  e.entrid";
$page->mysql_assign($sql, 'adr_pro');

//recuperation des adresses
$sql = "SELECT  a.adr1,a.adr2,a.adr3,a.cp,a.ville,
                gp.pays,gr.name AS region,a.tel,a.fax,
                FIND_IN_SET('active', a.statut) AS active,
                FIND_IN_SET('res-secondaire', a.statut) AS secondaire
          FROM  adresses AS a
     LEFT JOIN  geoloc_pays AS gp ON (gp.a2=a.pays)
     LEFT JOIN  geoloc_region AS gr ON (gr.a2=a.pays and gr.region=a.region)
         WHERE  uid={$user['user_id']} AND NOT FIND_IN_SET('pro',a.statut)
      ORDER BY  NOT FIND_IN_SET('active',a.statut), FIND_IN_SET('temporaire',a.statut), FIND_IN_SET('res-secondaire',a.statut)";
$page->mysql_assign($sql, 'adr');

// reformatage binets
$sql = "SELECT  text
          FROM  binets_ins
     LEFT JOIN  binets_def ON binets_ins.binet_id = binets_def.id
	 WHERE  user_id = {$user['user_id']}";
$res = $globals->db->query($sql);
if (list($binets) = mysql_fetch_row($res)){
    while (list($binet) = mysql_fetch_row($res)) {
        $binets .= ", $binet";
    }
} 
mysql_free_result($res);
$page->assign('binets', $binets);

// reformatage Groupes X
$res = $globals->db->query("SELECT  text, url
                              FROM  groupesx_ins
	                 LEFT JOIN  groupesx_def ON groupesx_ins.gid = groupesx_def.id
                             WHERE  guid = '{$user['user_id']}'");
$gxs = Array();
while (list($gxt,$gxu) = mysql_fetch_row($res)) {
    if ($gxu) {
        $gxs[] = "<a href=\"$gxu\" onclick=\"return popup(this)\">$gxt</a>";
    } else {
        $gxs[] = $gxt;
    }
} 
mysql_free_result($res);
$page->assign('groupes', join(', ', $gxs));

// reformatage appli
$res = $globals->db->query("SELECT  applis_def.text, applis_def.url, applis_ins.type
                              FROM  applis_ins
 		        INNER JOIN  applis_def ON applis_def.id = applis_ins.aid
		             WHERE  uid='{$user['user_id']}'
		          ORDER BY  ordre");
if (list($rapp_txt, $rapp_url, $rapp_type) = mysql_fetch_row($res)) {
    $applis = applis_fmt($rapp_type, $rapp_txt, $rapp_url);
    while (list($rapp_txt, $rapp_url, $rapp_type) = mysql_fetch_row($res)) {
        $applis .=", ";
        $applis .= applis_fmt($rapp_type, $rapp_txt, $rapp_url);
    }
    $page->assign('applis', $applis);
}
mysql_free_result($res);

$page->run();

?>
