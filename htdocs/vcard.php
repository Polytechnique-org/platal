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
        $Id: vcard.php,v 1.17 2004-11-22 20:04:36 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_nonhtml_page('vcard.tpl', AUTH_COOKIE);
require_once("xorg.misc.inc.php");

function format_adr($params, &$smarty) {
    // $adr1, $adr2, $adr3, $cp, $ville, $region, $pays
    extract($params['adr']);
    $res = ";;";
    if (! empty($adr1)) $res .= "$adr1\n";
    if (! empty($adr2)) $res .= "$adr2\n";
    if (! empty($adr3)) $res .= "$adr3\n";
    if (! empty($adr1) || ! empty($adr2) || ! empty($adr3))
        $res = substr($res, 0, -1);
    $res .= ";";
    if (! empty($ville)) $res .= "$ville;"; else $res .= ";";
    if (! empty($region)) $res .= "$region;"; else $res .= ";";
    if (! empty($cp)) $res .= "$cp;"; else $res .= ";";
    if (! empty($pays)) $res .= "$pays";
    return quoted_printable_encode($res);
}

$page->register_modifier('qp_enc', 'quoted_printable_encode');
$page->register_function('format_adr', 'format_adr');

if(preg_match('/^\d*$/',$_REQUEST['x'])) {
    $myquery = "SELECT  prenom, nom, epouse, mobile, web, libre, promo, user_id, date, a.alias AS forlife
		  FROM  auth_user_md5 AS u
	    INNER JOIN  aliases       AS a  ON (u.user_id=a.id AND a.type='a_vie')
		 WHERE  a.id='{$_REQUEST['x']}'";
} else {
    $myquery = "SELECT  prenom, nom, epouse, mobile, web, libre, promo, user_id, date, a.alias AS forlife
		  FROM  auth_user_md5 AS u
	    INNER JOIN  aliases       AS a  ON (u.user_id=a.id AND a.type='a_vie')
	    INNER JOIN  aliases       AS b  ON (u.user_id=b.id AND a.type!='homonyme')
		 WHERE  b.alias='{$_REQUEST['x']}'";
}

$result=$globals->db->query($myquery);
if (mysql_num_rows($result)!=1) { exit; }

$vcard = mysql_fetch_assoc($result);
$page->assign_by_ref('vcard', $vcard);
mysql_free_result($result);

$page->mysql_assign("SELECT alias FROM aliases WHERE id={$vcard['user_id']} AND FIND_IN_SET('bestalias',flags)",'best');

$adr = $globals->db->query(
        "SELECT statut,adr1,adr2,adr3,cp,ville,gp.pays,gr.name,tel,fax,
        FIND_IN_SET('courrier', a.statut) AS courrier
        FROM adresses as a
        LEFT JOIN geoloc_pays AS gp ON(a.pays = gp.a2)
        LEFT JOIN geoloc_region AS gr
        ON(a.pays = gr.a2 AND a.region = gr.region)
        WHERE uid = {$vcard['user_id']}
        ORDER BY FIND_IN_SET('active', a.statut),
        NOT FIND_IN_SET('res-secondaire', a.statut)"
);
$home = Array();
while($home[] = mysql_fetch_assoc($adr));
array_pop($home);
mysql_free_result($adr);
$page->assign_by_ref('home', $home);


$adr = $globals->db->query(
        "SELECT adr1,adr2,adr3,cp,ville,gp.pays,gr.name,tel,fax,poste,entreprise,f.fonction_fr as fonction
        FROM entreprises as e
        LEFT JOIN fonctions_def AS f ON(e.fonction = f.id)
        LEFT JOIN geoloc_pays AS gp ON(e.pays = gp.a2)
        LEFT JOIN geoloc_region AS gr
        ON(e.pays = gr.a2 AND e.region = gr.region)
        WHERE uid = {$vcard['user_id']}"
);
if(mysql_num_rows($adr))
    $work = mysql_fetch_assoc($adr);
mysql_free_result($adr);
$page->assign_by_ref('work', $work);


header("Pragma: ");
header("Cache-Control: ");
header("Content-type: text/x-vcard\n");
header("Content-Transfer-Encoding: Quoted-Printable\n");

$page->run();
?>
