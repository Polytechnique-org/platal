<?php
require_once('geoloc.inc.php');
require_once('secteur.emploi.inc.php');
require_once('fonction.emploi.inc.php');

$res = mysql_query("SELECT entrid, entreprise, secteur, ss_secteur, poste, fonction,
	adr1, adr2, adr3, cp, ville, pays, region, tel, fax,
	FIND_IN_SET('entreprise_public',visibilite),FIND_IN_SET('entreprise_ax',visibilite),FIND_IN_SET('adr_public',visibilite),
	FIND_IN_SET('adr_ax',visibilite),FIND_IN_SET('tel_public',visibilite),FIND_IN_SET('tel_ax',visibilite)
        FROM entreprises
        WHERE uid = '{$_SESSION['uid']}' ORDER BY entrid");
//echo mysql_error();
$nb_res = mysql_num_rows($res);
for($i = 0; $i < $nb_res ; $i++){
	list($endrid[$i], $entreprise[$i], $secteur[$i], $ss_secteur[$i], $poste[$i], $fonction[$i],
       	     $adrpro1[$i], $adrpro2[$i], $adrpro3[$i], $cppro[$i], $villepro[$i], $payspro[$i], $regionpro[$i],
             $telpro[$i], $faxpro[$i], $entreprise_public[$i], $entreprise_ax[$i], $adrpro_public[$i], $adrpro_ax[$i],
	     $telpro_public[$i], $telpro_ax[$i]) = mysql_fetch_row($res);
}
//limite dure a 2
for($i = $nb_res; $i < 2 ; $i++){
	$endrid[$i] = $i;
	$entreprise[$i] = '';
	$secteur[$i] = '';
	$ss_secteur[$i] = '';
	$poste[$i] = '';
	$fonction[$i] = '0';
        $adrpro1[$i] = '';
	$adrpro2[$i] = '';
	$adrpro3[$i] = '';
	$cppro[$i] = '';
	$villepro[$i] = '';
	$payspro[$i] = '00';
	$regionpro[$i] = '';
        $telpro[$i] = '';
	$faxpro[$i] = '';
	$entreprise_public[$i] = '';
	$entreprise_ax[$i] ='';
	$adrpro_public[$i] = '';
	$adrpro_ax[$i] = '';	             
	$telpro_public[$i] = '';
	$telpro_ax[$i] = '';
}

$page->assign_by_ref('endrid',$endrid);
$page->assign_by_ref('entreprise',$entreprise);
$page->assign_by_ref('secteur',$secteur);
$page->assign_by_ref('ss_secteur',$ss_secteur);
$page->assign_by_ref('poste',$poste);
$page->assign_by_ref('fonction',$fonction);
$page->assign_by_ref('adrpro1',$adrpro1);
$page->assign_by_ref('adrpro2',$adrpro2);
$page->assign_by_ref('adrpro3',$adrpro3);
$page->assign_by_ref('cppro',$cppro);
$page->assign_by_ref('villepro',$villepro);
$page->assign_by_ref('payspro',$payspro);
$page->assign_by_ref('regionpro',$regionpro);
$page->assign_by_ref('telpro',$telpro);
$page->assign_by_ref('faxpro',$faxpro);
$page->assign_by_ref('entreprise_public',$entreprise_public);
$page->assign_by_ref('entreprise_ax',$entreprise_ax);
$page->assign_by_ref('adrpro_public',$adrpro_public);
$page->assign_by_ref('adrpro_ax',$adrpro_ax);
$page->assign_by_ref('telpro_public',$telpro_public);
$page->assign_by_ref('telpro_ax',$telpro_ax);

//recuperation des donnees sur les secteurs :

$res = mysql_query("SELECT id, label from emploi_secteur");

while(list($tmp_secteur_id, $tmp_secteur_label) = mysql_fetch_row($res)){
	$secteurs[$tmp_secteur_id] = $tmp_secteur_label;
}

//recuperation des donnees sur les fonctions :
$res = mysql_query("SELECT id, fonction_fr, FIND_IN_SET('titre', flags) from fonctions_def ORDER BY id");

while(list($tmp_fonction_id, $tmp_fonction_label, $tmp_fonction_titre) = mysql_fetch_row($res)){
	$fonctions[$tmp_fonction_id] = $tmp_fonction_label;
	$fonctions_titre[$tmp_fonction_id] = $tmp_fonction_titre;
}

//recuperation du CV
$res = mysql_query("SELECT cv from auth_user_md5 where user_id = '{$_SESSION['uid']}'");
list($cv) = mysql_fetch_row($res);
$page->assign_by_ref('cv',$cv);

?>
