<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

global $adresses, $nb_adr_max, $nb_tel_max;

// on limite à 6 adresses personnelles par utilisateur
$nb_adr_max = 6; // ( = max(adrid possibles)
// on limite à 4 numéros de téléphone par adresse
$nb_tel_max = 4; // ( = max(telid possibles)

//les adresses sont stockées dans un tableau global indéxé par adrid;

function is_adr_empty($adrid){
  $adr = &$GLOBALS['adresses'][$adrid];
  $emptytel = count($adr['tels']) == 0;
  if (!$emptytel) {
  	$emptytel = true;
  	foreach ($adr['tels'] as $t) if ($t['tel']) {
  		$emptytel = false;
  		break;
  	}
  }
  return (
    ($adr['adr1'] == '') && ($adr['adr2'] == '') && ($adr['adr3'] == '') &&
    ($adr['postcode'] == '') && ($adr['city'] == '') && ($adr['country'] == '00') &&
    ($emptytel)
    );
}

function delete_address($adrid, $in_request_array = false){
    XDB::execute("DELETE FROM adresses WHERE uid = {?} AND adrid = {?}",
                 S::v('uid', -1), $adrid);
    XDB::execute("DELETE FROM tels WHERE uid = {?} AND adrid = {?}",
                 S::v('uid', -1), $adrid);
    if ($in_request_array == true){
        unset($_REQUEST['adrid'][$adrid]);
    } else{
        unset($GLOBALS['adresses'][$adrid]);
    }
}

//on verifie si on nous a demande une suppression
$req_adrid_del = Env::v('adrid_del', Array());
for ($i = 1; $i <= $nb_adr_max; $i++) {
    if (isset($req_adrid_del[$i])) {
        delete_address($i,true);
    }
}
if (Env::i('deltel')) {
	XDB::execute("DELETE FROM tels WHERE uid = {?} AND adrid = {?} AND telid = {?}",
		S::v('uid', -1), Env::i('adrid'), Env::i('telid'));
}

//$sql_order = "ORDER BY (NOT FIND_IN_SET('active', statut)), FIND_IN_SET('temporaire', statut)";
$sql_order = '';

//recuperation des adrid
$res = XDB::query("SELECT adrid FROM adresses WHERE uid = {?} AND NOT FIND_IN_SET('pro', statut) ".$sql_order, S::v('uid', -1));
$adrids = $res->fetchColumn();

//recuperation des donnees de la bd
$res = XDB::iterRow(
	"SELECT
	FIND_IN_SET('res-secondaire', statut), FIND_IN_SET('courrier', statut),
	FIND_IN_SET('active', statut), FIND_IN_SET('temporaire', statut),
	adr1, adr2, adr3, postcode, city, cityid,
        a.country, region, regiontxt, pub,
	gp.pays AS countrytxt, gp.display
	FROM adresses AS a INNER JOIN geoloc_pays AS gp ON(gp.a2 = a.country)
	WHERE uid = {?} AND NOT FIND_IN_SET('pro',statut) ".$sql_order
, S::v('uid', -1)
);

$nb_adr = $res->total();

for ($i = 0; $i < $nb_adr; $i++) {
  $adrid = $adrids[$i];
  $adresses[$adrid]['adrid'] = $adrid;
  list(
       $adresses[$adrid]['secondaire'], $adresses[$adrid]['courrier'],
       $adresses[$adrid]['active'], $adresses[$adrid]['temporaire'],
       $adresses[$adrid]['adr1'], $adresses[$adrid]['adr2'], $adresses[$adrid]['adr3'], $adresses[$adrid]['postcode'], $adresses[$adrid]['city'], $adresses[$adrid]['cityid'],
       $adresses[$adrid]['country'], $adresses[$adrid]['region'], $adresses[$adrid]['regiontxt'],
       $adresses[$adrid]['pub'],
       $adresses[$adrid]['countrytxt'],$adresses[$adrid]['display']) = $res->next();
  $adresses[$adrid]['nouvelle'] = 'modif';
  $adresses[$adrid]['numero_formulaire'] = -1;
  require_once('geoloc.inc.php');
  $adresses[$adrid]['txt'] = get_address_text($adresses[$adrid]);
}

$restels = XDB::iterator(
    "SELECT
    t.adrid, telid, tel_type, t.tel_pub, t.tel
    FROM tels AS t INNER JOIN adresses AS a ON(t.uid = a.uid AND t.adrid = a.adrid)
    WHERE t.uid = {?} AND NOT FIND_IN_SET('pro',statut) ORDER BY t.adrid, tel_type DESC, telid"
, S::v('uid', -1)
);
while ($tel = $restels->next()) {
    $adrid = $tel['adrid'];
    unset($tel['adrid']);
    if (!isset($adresses[$adrid]['tels']))
        $adresses[$adrid]['tels'] = array($tel);
    else
        $adresses[$adrid]['tels'][] = $tel;
}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
