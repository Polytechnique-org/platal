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


// {{{ liste les pays ou les régions d'un pays
/** donne la liste déroulante des pays
 * @param $current pays actuellement selectionné
 */
function geoloc_pays($current) {
    global $globals;
    $res  = $globals->xdb->iterRow('SELECT a2,pays FROM geoloc_pays ORDER BY pays');
    $html = "";
    while (list($my_id, $my_pays) = $res->next()) {
	$html .= sprintf("<option value=\"%s\" %s>%s</option>\n",
                $my_id, ($current==$my_id?"selected='selected'":""), $my_pays);
    }
    return $html;
}

function _geoloc_pays_smarty($params){
  if(!isset($params['pays']))
    return;
  return geoloc_pays($params['pays']);
}
$page->register_function('geoloc_pays', '_geoloc_pays_smarty');

/** donne la liste deroulante des regions pour un pays
 * @param $pays le pays dont on veut afficher les regions
 * @param $current la region actuellement selectionnee
 */
function geoloc_region($pays,$current) {
    global $globals;
    $res  = $globals->xdb->iterRow('SELECT region,name FROM geoloc_region where a2={?} ORDER BY name', $pays);
    $html = "<option value=\"\"></option>";
    while (list($regid, $regname) = $res->next()) {
	$html .= sprintf("<option value=\"%s\" %s>%s</option>\n",
                $regid, ($current==$regid?"selected='selected'":""), $regname);
    }
    return $html;

}
function _geoloc_region_smarty($params){
  if(!isset($params['pays']))
    return;
  if(!isset($params['region']))
    return;
  return geoloc_region($params['pays'], $params['region']);
}
$page->register_function('geoloc_region', '_geoloc_region_smarty');
// }}}

// retrieve the infos on a text address
// store on the fly the info of the city concerned
function get_address_infos($txt) {
    $url ="http://www.geoloc.org/adressparser/address.php?txt=".urlencode(utf8_encode($txt));
    if (!($f = @fopen($url, 'r'))) return false;
    fgets($f);
    $keys = explode('|',fgets($f));
    $vals = explode('|',fgets($f));
    $infos = array();
    foreach ($keys as $i=>$key) if($vals[$i]) $infos[$key] = $vals[$i];
    global $globals;
    if ($infos['sql'])
       $globals->xdb->execute("REPLACE INTO geoloc_city VALUES ".$infos['sql']);
    return $infos;
}

// make the text of an address that can be read by a mailman
function get_address_text($adr) {
    $t = "";
    if ($adr['adr1']) $t.= $adr['adr1'];
    if ($adr['adr2']) $t.= "\n".$adr['adr2'];
    if ($adr['adr3']) $t.= "\n".$adr['adr3'];
    $l = "";
    if ($adr['country'] == 'US' || $adr['country'] == 'CA') {
        if ($adr['city']) $l .= $adr['city'].",\n";
        if ($adr['region']) $l .= $adr['region']." ";
        if ($adr['postcode']) $l .= $adr['postcode'];
    } else {
        if ($adr['postcode']) $l .= $adr['postcode']." ";
        if ($adr['city']) $l .= $adr['city'];
    }
    if ($l) $t .= "\n".trim($l);
    if ($adr['pays']) $t .= "\n".$adr['pays'];
    return trim($t);
}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
