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
        $Id: mescontacts_ldif.php,v 1.7 2004-08-31 10:03:28 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_nonhtml_page('mescontacts_ldif.tpl', AUTH_COOKIE);

function ensure_adr(&$table) {
    $trim = Array();
    if(!empty($table['adr1'])) $trim[] = $table['adr1'];
    if(!empty($table['adr2'])) $trim[] = $table['adr2'];
    if(!empty($table['adr3'])) $trim[] = $table['adr3'];
    unset($table['adr1'], $table['adr2'], $table['adr3']);
    foreach($trim as $key=>$val)
        $table["adr$key"] = $val;
    $table['adr_fmt'] = implode("\n", $trim);
    return $table;
}

function ldif_encode($data,$force_64=false) {
    return base64_encode(utf8_encode($data));
}
$page->register_modifier('ldif_format', 'ldif_encode');
$page->register_modifier('utf8', 'utf8_encode');


/*
 * On construit la liste des contacts, et de l'entreprise à laquelle ils appartiennent
 */
$contacts = Array();
$req = $globals->db->query("SELECT contact AS id, date, prenom, nom, epouse, username, mobile, web, libre, promo, alias,
                           entreprise, adr1, adr2, adr3, cp, ville, gp.pays, gr.name, tel, fax,
                           poste, f.label AS fonction
                    FROM      contacts      AS c 
                    LEFT JOIN auth_user_md5 AS a  ON(a.user_id = c.contact)
                    LEFT JOIN entreprises   AS e  ON(a.user_id = e.uid)
                    LEFT JOIN emploi_naf    AS f  ON(e.fonction = f.id)
                    LEFT JOIN geoloc_pays   AS gp ON(e.pays = gp.a2)
                    LEFT JOIN geoloc_region AS gr ON(e.pays = gr.a2 AND e.region = gr.region)
                    WHERE c.uid ='{$_SESSION['uid']}'
                    ORDER BY contact");
while($line = mysql_fetch_assoc($req)) {
    $contacts[$line['id']] = ensure_adr($line);
}
mysql_free_result($req);

/*
 * On y ajoute les infos d'adresses
 */
$req = $globals->db->query(
       "SELECT c.contact AS id, adr1, adr2, adr3, cp, ville, gp.pays, gr.name, tel, fax
        FROM       contacts      AS c
        INNER JOIN adresses      AS a  ON (a.uid = c.contact AND FIND_IN_SET('active', a.statut))
        LEFT  JOIN geoloc_pays   AS gp ON (a.pays = gp.a2)
        LEFT  JOIN geoloc_region AS gr ON (a.pays = gr.a2 AND a.region = gr.region)
        WHERE c.uid = {$_SESSION['uid']}
        ORDER BY c.contact");
echo mysql_error();
while($line = mysql_fetch_assoc($req))
    $contacts[$line['id']]['home'] = ensure_adr($line);
mysql_free_result($req);
$page->assign_by_ref('contacts',$contacts);

header("Pragma: ");
header("Cache-Control: ");
header("Content-type: text/x-ldif\n");

$page->run();
?>
