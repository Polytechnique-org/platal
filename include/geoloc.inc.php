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
        $Id: geoloc.inc.php,v 1.7 2004-10-14 22:32:02 x2000habouzit Exp $
 ***************************************************************************/


/** donne la liste déroulante des pays
 * @param $current pays actuellement selectionné
 */
function geoloc_pays($current) {
    global $globals;
    $sql = "SELECT a2,pays FROM geoloc_pays ORDER BY pays";

    $result = $globals->db->query($sql);
    $html = "";
    while (list($my_id,$my_pays) = mysql_fetch_row($result))
	$html .= sprintf("<option value=\"%s\" %s>%s</option>\n",$my_id,($current==$my_id?"selected='selected'":""),$my_pays);
    
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
    $sql = "SELECT region,name FROM geoloc_region where a2='".$pays."' ORDER BY name";
    $result = $globals->db->query($sql);
  
    $html = "<option value=\"\"></option>";
    while (list($regid,$regname) = mysql_fetch_row($result))
	$html .= sprintf("<option value=\"%s\" %s>%s</option>\n",$regid,($current==$regid?"selected='selected'":""),$regname);
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

?>
