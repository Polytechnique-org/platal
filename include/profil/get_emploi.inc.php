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

require_once('geoloc.inc.php');
require_once('secteur.emploi.inc.php');
require_once('fonction.emploi.inc.php');

$res = $globals->xdb->iterRow("SELECT entrid, entreprise, secteur, ss_secteur, poste, fonction,
	adr1, adr2, adr3, cp, ville, pays, region, tel, fax, mobile,
	pub, adr_pub, tel_pub, email, email_pub, web
        FROM entreprises
        WHERE uid = {?} ORDER BY entrid",Session::getInt('uid', -1));

$nb_res = $res->total();
for($i = 0; $i < $nb_res ; $i++){
	list($endrid[$i], $entreprise[$i], $secteur[$i], $ss_secteur[$i], $poste[$i], $fonction[$i],
       	     $adrpro1[$i], $adrpro2[$i], $adrpro3[$i], $cppro[$i], $villepro[$i], $payspro[$i], $regionpro[$i],
             $telpro[$i], $faxpro[$i], $mobilepro[$i], $pubpro[$i], $adr_pubpro[$i],
	     $tel_pubpro[$i], $emailpro[$i], $email_pubpro[$i], $webpro[$i]) = $res->next();
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
	$mobilepro[$i] = '';
	$pubpro[$i] = '';
	$adr_pubpro[$i] = '';
	$tel_pubpro[$i] = '';
	$emailpro[$i] = '';
	$email_pubpro[$i] = '';
	$webpro[$i] = '';
}

//recuperation des donnees sur les secteurs :

$res = $globals->xdb->iterRow("SELECT id, label FROM emploi_secteur");

while(list($tmp_secteur_id, $tmp_secteur_label) = $res->next()){
    $secteurs[$tmp_secteur_id] = $tmp_secteur_label;
}

//recuperation des donnees sur les fonctions :
$res = $globals->xdb->iterRow("SELECT id, fonction_fr, FIND_IN_SET('titre', flags) FROM fonctions_def ORDER BY id");

while(list($tmp_fonction_id, $tmp_fonction_label, $tmp_fonction_titre) = $res->next()){
    $fonctions[$tmp_fonction_id] = $tmp_fonction_label;
    $fonctions_titre[$tmp_fonction_id] = $tmp_fonction_titre;
}

//recuperation du CV
$res = $globals->xdb->query("SELECT cv FROM auth_user_md5 WHERE user_id = {?}", Session::getInt('uid', -1));
$cv = $res->fetchOneCell();

?>
