<?php

require("auto.prepend.inc.php");
new_simple_page('fiche.tpl',AUTH_COOKIE, false, 'add_fiche_css.tpl');

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
    $where_clause = " WHERE username = '{$_REQUEST['user']}'";
else
    $where_clause = " WHERE u.matricule = '{$_REQUEST['mat']}'";

$reqsql = "SELECT u.prenom, u.nom, u.epouse, nationalites.text"
  .", u.user_id, u.username, u.alias, u.matricule, i.deces != 0 as dcd"
  .", i.deces"
  .", u.date"
  .", u.cv, sections.text"
  .", u.mobile, u.web, u.libre, u.promo"
  .", c.uid IS NOT NULL"
  .", p.x, p.y"
  ." FROM auth_user_md5 as u"
  ." LEFT JOIN contacts as c ON (c.uid = {$_SESSION['uid']} and c.contact = u.user_id)"
  ." INNER JOIN nationalites ON(nationalites.id = u.nationalite)"
  ." INNER JOIN sections ON(sections.id = u.section)"
  ." INNER JOIN identification AS i ON(u.matricule = i.matricule)"
  ." LEFT  JOIN photo as p ON(p.uid = u.user_id)"
// conversion du username en user_id si nécessaire
  .$where_clause;
$result = mysql_query($reqsql);

if (mysql_num_rows($result)!=1)
        exit;

if (list($prenom, $nom, $epouse, $nationalite, 
        $user_id, $username, $alias, $matricule, $dcd, $deces, 
        $date,
        $cv, $section, 
        $mobile, $web, $libre, $promo,
        $is_contact, $size_x, $size_y) = mysql_fetch_row($result)) {

$page->assign('prenom', $prenom);
$page->assign('nom', $nom);
$page->assign('promo', $promo);
$page->assign('cv', $cv);
$page->assign('username', $username);
$page->assign('epouse', $epouse);
$page->assign('nationalite', $nationalite);
$page->assign('user_id', $user_id);
$page->assign('alias', $alias);
$page->assign('matricule', $matricule);
$page->assign('dcd', $dcd);
$page->assign('deces', $deces);
$page->assign('date', $date);
$page->assign('section', $section);
$page->assign('mobile', $mobile);
$page->assign('web', $web);
$page->assign('libre', $libre);

// reformatage is_contact
$is_contact = (bool) $is_contact;
$page->assign('is_contact', $is_contact);

// photo

$photo="getphoto.php?x=".$user_id.(SID == '' ? '' : '&amp;'.SID).($new ? '&amp;modif=new' : '');
if(!isset($size_y) and !isset($size_x)) list($size_x, $size_y) = getimagesize("none.png");
if(!isset($size_y) or $size_y < 1) $size_y=1;
if(!isset($size_x) or $size_x < 1) $size_x=1;
if($size_y > 300){
    $size_x = (integer)($size_x*300/$size_y);
    $size_y = 300;
}
if($size_x < 180){
    $size_y = (integer)($size_y*180/$size_x);
    $size_x = 180;
}
$page->assign('photo_url', $photo);
$page->assign('size_x', $size_x);
$page->assign('size_y', $size_y);


mysql_free_result($result);

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

$result = mysql_query($reqsql);

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

$result = mysql_query($reqsql);

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
$result = mysql_query("SELECT text
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
$result = mysql_query("SELECT text, url
                       FROM groupesx_ins
		       LEFT JOIN groupesx_def ON groupesx_ins.gid = groupesx_def.id
		       WHERE guid = '$user_id'");
$gxs = "";
while (list($gxt,$gxu) = mysql_fetch_row($result)) {
        if ($gxs) $gxs .= ", ";
        if ($gxu) $gxs .= "<a target=\"_blank\" href=\"$gxu\">";
        $gxs .= $gxt;
        if ($gxu) $gxs .= "</a>";
} 
mysql_free_result($result);
$page->assign('groupes', $gxs);

// reformatage appli
$result = mysql_query("SELECT applis_def.text, applis_def.url, applis_ins.type
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
}
mysql_free_result($result);
$page->assign('applis', $applis);

$page->run();

?>
