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
        $Id: fiche.php,v 1.23 2004-11-17 18:16:23 x2000habouzit Exp $
 ***************************************************************************/


require("auto.prepend.inc.php");
new_simple_page('fiche.tpl',AUTH_COOKIE);

require_once('applis.func.inc.php');

//$isnetscape = !empty($_SESSION['skin_compatible']);

if (!isset($_REQUEST['user']) && !isset($_REQUEST['mat']))
  exit;

if (isset($_REQUEST["modif"]) && $_REQUEST["modif"]=="new") {
    $new = true;
} else {
    $new = false;
}

if (isset($_REQUEST['user']))
    $where_clause = "INNER JOIN aliases AS a1 ON (a1.id=u.user_id AND a1.alias = '{$_REQUEST['user']}' AND a.type!='homonyne')";
else
    $where_clause = " WHERE u.matricule = '{$_REQUEST['mat']}'";

$reqsql = "SELECT  u.prenom, u.nom, u.epouse, IF(gp.nat='',gp.pays,gp.nat) AS text, gp.a2,
		   u.user_id, a.alias, a2.alias, u.matricule, u.deces != 0 as dcd,
		   u.deces, u.date, u.cv, sections.text, u.mobile, u.web,
		   u.libre, u.promo, c.uid IS NOT NULL, p.x, p.y
	     FROM  auth_user_md5  AS u
       INNER JOIN  aliases        AS a  ON (u.user_id=a.id AND a.type='a_vie')
       INNER JOIN  aliases        AS a2 ON (u.user_id=a2.id AND FIND_IN_SET('bestalias',a2.flags))
        LEFT JOIN  contacts       AS c  ON (c.uid = {$_SESSION['uid']} and c.contact = u.user_id)
        LEFT JOIN  geoloc_pays    AS gp ON (gp.a2 = u.nationalite)
       INNER JOIN  sections             ON(sections.id = u.section)
        LEFT JOIN  photo          AS p  ON(p.uid = u.user_id) ".$where_clause;

$result = $globals->db->query($reqsql);

if (mysql_num_rows($result)!=1)
        exit;

if (list($prenom, $nom, $epouse, $nationalite, $iso3166,
        $user_id, $forlife, $bestalias, $matricule, $dcd, $deces, 
        $date,
        $cv, $section, 
        $mobile, $web, $libre, $promo,
        $is_contact, $size_x, $size_y) = mysql_fetch_row($result)) {

$page->assign('user_id', $user_id);
$page->assign('prenom', $prenom);
$page->assign('nom', $nom);
$page->assign('promo', $promo);
$page->assign('cv', $cv);
$page->assign('forlife', $forlife);
$page->assign('bestalias', $bestalias);
$page->assign('epouse', $epouse);
$page->assign('nationalite', $nationalite);
$page->assign('iso3166', $iso3166);
$page->assign('user_id', $user_id);
$page->assign('matricule', $matricule);
$page->assign('dcd', $dcd);
$page->assign('deces', $deces);
$page->assign('date', $date);
$page->assign('section', $section);
$page->assign('mobile', $mobile);
$page->assign('web', $web);
$page->assign('libre', $libre);

if (empty($epouse))
	$page->assign('xorg_title', "$prenom $nom");
else
	$page->assign('xorg_title', "$prenom $epouse");


// reformatage is_contact
$is_contact = (bool) $is_contact;
$page->assign('is_contact', $is_contact);

// photo

$photo="getphoto.php?x=".$forlife.(SID == '' ? '' : '&amp;'.SID).($new ? '&amp;modif=new' : '');
if(!isset($size_y) and !isset($size_x)) list($size_x, $size_y) = getimagesize("images/none.png");
if(!isset($size_y) or $size_y < 1) $size_y=1;
if(!isset($size_x) or $size_x < 1) $size_x=1;
if($size_x > 240){
    $size_y = (integer)($size_y*240/$size_x);
    $size_x = 240;
}
if($size_y > 300){
    $size_x = (integer)($size_x*300/$size_y);
    $size_y = 300;
}
if($size_x < 160){
    $size_y = (integer)($size_y*160/$size_x);
    $size_x = 160;
}
$page->assign('photo_url', $photo);
$page->assign('size_x', $size_x);
$page->assign('size_y', $size_y);


mysql_free_result($result);

//check si le gars est un referent :
$reqsql = "SELECT 1 FROM mentor WHERE uid = '$user_id' AND expertise <> ''";
$result = $globals->db->query($reqsql);
if (list($is_referent) = mysql_fetch_row($result)){
  mysql_free_result($result);
}
$page->assign('is_referent', $is_referent);

//recuperation des infos professionnelles
$reqsql = 
   "SELECT e.entreprise, s.label as secteur , ss.label as sous_secteur , f.fonction_fr as fonction,
           e.poste, e.adr1, e.adr2, e.adr3, e.cp, e.ville,
	   gp.pays, gr.name, e.tel, e.fax
   FROM entreprises AS e
   LEFT JOIN emploi_secteur AS s ON(e.secteur = s.id)
   LEFT JOIN emploi_ss_secteur AS ss ON(e.ss_secteur = ss.id AND e.secteur = ss.secteur)
   LEFT JOIN fonctions_def AS f ON(e.fonction = f.id)
   LEFT JOIN geoloc_pays AS gp ON (gp.a2 = e.pays)
   LEFT JOIN geoloc_region AS gr ON (gr.a2 = e.pays and gr.region = e.region)
   WHERE e.uid = $user_id
   ORDER BY e.entrid
   ";

$result = $globals->db->query($reqsql);

$i = 0;
while(list($adr_pro[$i]['entreprise'], $adr_pro[$i]['secteur'], $adr_pro[$i]['ss_secteur'],
           $adr_pro[$i]['fonction'], $adr_pro[$i]['poste'],
	   $adr_pro[$i]['adr1'], $adr_pro[$i]['adr2'], $adr_pro[$i]['adr3'],
	   $adr_pro[$i]['cp'], $adr_pro[$i]['ville'],
	   $adr_pro[$i]['pays'], $adr_pro[$i]['region'],
	   $adr_pro[$i]['tel'], $adr_pro[$i]['fax']) = mysql_fetch_row($result)){
    if(!empty($adr_pro[$i]['entreprise']) || !empty($adr_pro[$i]['secteur']) ||
       !empty($adr_pro[$i]['fonction']) || !empty($adr_pro[$i]['poste']) ||
       !empty($adr_pro[$i]['adr1']) || !empty($adr_pro[$i]['adr2']) || !empty($adr_pro[$i]['adr3']) ||
       !empty($adr_pro[$i]['cp']) || !empty($adr_pro[$i]['ville']) ||
       !empty($adr_pro[$i]['pays']) || !empty($adr_pro[$i]['tel']) || !empty($adr_pro[$i]['fax'])
      ){
    $i++;
   }
}
unset($adr_pro[$i]);
$nb_infos_pro = $i;
$page->assign('nb_infos_pro', $nb_infos_pro);
$page->assign_by_ref('adr_pro', $adr_pro);
mysql_free_result($result);

//recuperation des adresses
$reqsql =
   "SELECT a.adr1,a.adr2,a.adr3,a.cp,a.ville,
           gp.pays,gr.name AS region,a.tel,a.fax,
           FIND_IN_SET('active', a.statut),
           FIND_IN_SET('res-secondaire', a.statut)
    FROM adresses AS a
    LEFT JOIN geoloc_pays AS gp ON (gp.a2=a.pays)
    LEFT JOIN geoloc_region AS gr ON (gr.a2=a.pays and gr.region=a.region)
    WHERE uid=$user_id AND NOT FIND_IN_SET('pro',a.statut)
    ORDER BY NOT FIND_IN_SET('active',a.statut), FIND_IN_SET('temporaire',a.statut), FIND_IN_SET('res-secondaire',a.statut)";

$result = $globals->db->query($reqsql);

$nbadr=mysql_num_rows($result);

for ($i=0;$row = mysql_fetch_row($result);$i++) {

    list(  $adr[$i]['adr1'], $adr[$i]['adr2'], $adr[$i]['adr3'],
	   $adr[$i]['cp'], $adr[$i]['ville'],
	   $adr[$i]['pays'], $adr[$i]['region'],
	   $adr[$i]['tel'], $adr[$i]['fax'], 
           $adr[$i]['active'], $adr[$i]['secondaire']) = $row;

    if ($adr[$i]['active'])
            $adr[$i]['title'] = "Mon adresse actuelle :";
    elseif ($adr[$i]['secondaire'])
            $adr[$i]['title'] = "Adresse secondaire :";
    else
            $adr[$i]['title'] = "Adresse principale :";

}
$page->assign_by_ref('adr', $adr);
mysql_free_result($result);


// reformatage binets
$result = $globals->db->query("SELECT text
                       FROM binets_ins
                       LEFT JOIN binets_def
                       ON binets_ins.binet_id = binets_def.id
		       WHERE user_id = '$user_id'");
if (list($binets) = mysql_fetch_row($result)){
        while (list($binet) = mysql_fetch_row($result))
                $binets .= ", $binet";
        } 
}
mysql_free_result($result);
$page->assign('binets', $binets);

// reformatage Groupes X
$result = $globals->db->query("SELECT text, url
                       FROM groupesx_ins
		       LEFT JOIN groupesx_def ON groupesx_ins.gid = groupesx_def.id
		       WHERE guid = '$user_id'");
$gxs = "";
while (list($gxt,$gxu) = mysql_fetch_row($result)) {
        if ($gxs) $gxs .= ", ";
        if ($gxu) $gxs .= "<a href=\"$gxu\" onclick=\"return popup(this)\">";
        $gxs .= $gxt;
        if ($gxu) $gxs .= "</a>";
} 
mysql_free_result($result);
$page->assign('groupes', $gxs);

// reformatage appli
$result = $globals->db->query("SELECT applis_def.text, applis_def.url, applis_ins.type
                       FROM applis_ins
		       INNER JOIN applis_def ON applis_def.id = applis_ins.aid
		       WHERE uid='$user_id'
		       ORDER by ordre");
if (list($rapp_txt, $rapp_url, $rapp_type) = mysql_fetch_row($result)) {
        $applis = applis_fmt($rapp_type, $rapp_txt, $rapp_url);
        while (list($rapp_txt, $rapp_url, $rapp_type) = mysql_fetch_row($result)) {
                $applis .=", ";
                $applis .= applis_fmt($rapp_type, $rapp_txt, $rapp_url);
        }
} else {
    $applis = '';
}
mysql_free_result($result);
$page->assign('applis', $applis);

$page->run();

?>
