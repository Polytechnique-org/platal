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
        $Id: applis.func.inc.php,v 1.9 2004/08/31 20:22:19 x2000habouzit Exp $
 ***************************************************************************/


function applis_options($current=0) {
    global $globals;
    $html = '<option value="-1"></option>';
    $res=$globals->db->query("select * from applis_def order by text");
    while ($arr_appli=mysql_fetch_array($res)) { 
	$html .= '<option value="'.$arr_appli["id"].'"';
	if ($arr_appli["id"]==$current) $html .= " selected='selected'";
	$html .= '>'.htmlspecialchars($arr_appli["text"])."</option>\n";
    }
    return $html;
}
/** pour appeller applis_options depuis smarty
 */
function _applis_options_smarty($params){
    if(!isset($params['selected']))
	$params['selected'] = 0;
    return applis_options($params['selected']);
}
$page->register_function('applis_options','_applis_options_smarty');


/** affiche un Array javascript contenant les types de chaque appli
 */
function applis_type(){
    global $globals;
    $html = "";
    $res=$globals->db->query("select type from applis_def order by text");
    if (list($appli_type)=mysql_fetch_row($res))
	$html .= "new Array('".str_replace(",","','",$appli_type)."')";
    while (list($appli_type)=mysql_fetch_row($res))
	$html .= ",\nnew Array('".str_replace(",","','",$appli_type)."')";
    mysql_free_result($res);
    return $html;
}
$page->register_function('applis_type','applis_type');

/** affiche tous les types possibles d'applis
 */
function applis_type_all(){
    global $globals;
    $res = $globals->db->query("show columns from applis_def like 'type'");
    $arr_appli = mysql_fetch_array($res);
    mysql_free_result($res);
    return str_replace(")","",str_replace("set(","",$arr_appli["Type"]));
}
$page->register_function('applis_type_all','applis_type_all');

/** formatte une ecole d'appli pour l'affichage
 */
function applis_fmt($type, $text, $url) {
    $txt="";
    if (($type!="Ingénieur")&&($type!="Diplôme"))
	$txt .= $type;
    if ($text!="Université") {
	if ($txt) $txt .= " ";
	if ($url) 
	    $txt .= "<a href=\"$url\" onclick=\"return popup(this)\">$text</a>";
	else 
	    $txt .= $text;
    }
    return $txt;
}
function _applis_fmt($params, &$smarty) {
    extract($params);
    return applis_fmt($type, $text, $url);
}
$page->register_function('applis_fmt','_applis_fmt');

?>
