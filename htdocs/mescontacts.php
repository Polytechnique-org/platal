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
        $Id: mescontacts.php,v 1.10 2004-09-05 12:54:18 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page("mescontacts.tpl",AUTH_COOKIE,true);
require("applis.func.inc.php");

// si l'utilisateur demande le retrait de qqun de sa liste
if (isset($_REQUEST['action'])) {
    if($_REQUEST['action']=='retirer') {

	list($cont_user_id) = mysql_fetch_row($res);
	if ($globals->db->query("DELETE FROM contacts WHERE uid = '{$_SESSION['uid']}' AND contact='{$_REQUEST['user']}'"))
	    $page->assign('erreur', "Contact retiré !\n");

        // si l'utilisateur demande l'ajout de qqun à sa liste
    } elseif ($_REQUEST["action"]=="ajouter") {

        if (($res = $globals->db->query("SELECT id FROM aliases WHERE alias='{$_REQUEST['user']}' AND type!='homonyme'")) && mysql_num_rows($res)==1) {
            list($cont_user_id) = mysql_fetch_row($res);
            if ($globals->db->query("INSERT INTO contacts set uid = '{$_SESSION['uid']}', contact = '$cont_user_id'")) {
                $page->assign('erreur', 'Contact ajouté !');
            } else
                $page->assign('erreur', 'Contact déjà dans la liste !');
        } else {
            $page->assign('erreur', 'Utilisateur inexistant ou non inscrit !');
        }
    }
}

$sql = "SELECT contact AS id,
               a.*, l.alias AS forlife,
               i.deces != 0 AS dcd, i.matricule_ax,
               e.entreprise, es.label AS secteur, ef.label AS fonction,
               n.text AS nat,
               ad0.text AS app0text, ad0.url AS app0url, ai0.type AS app0type,
               ad1.text AS app1text, ad1.url AS app1url, ai1.type AS app1type,
               adr.ville, gp.pays, gr.name AS region
        FROM       contacts       AS c
        INNER JOIN auth_user_md5  AS a   ON (a.user_id = c.contact)
        INNER JOIN identification AS i   ON (a.matricule = i.matricule)
	INNER JOIN aliases        AS l   ON (a.user_id = l.id AND l.type='a_vie')
        LEFT  JOIN entreprises    AS e   ON (e.entrid = 1 AND e.uid = a.user_id)
        LEFT  JOIN emploi_secteur AS es  ON (e.secteur = es.id)
        LEFT  JOIN emploi_naf     AS ef  ON (e.fonction = ef.id)
        LEFT  JOIN nationalites   AS n   ON (a.nationalite = n.id)
        LEFT  JOIN applis_ins     AS ai0 ON (a.user_id = ai0.uid AND ai0.ordre = 0)
        LEFT  JOIN applis_def     AS ad0 ON (ad0.id = ai0.aid)
        LEFT  JOIN applis_ins     AS ai1 ON (a.user_id = ai1.uid AND ai1.ordre = 1)
        LEFT  JOIN applis_def     AS ad1 ON (ad1.id = ai1.aid)
        LEFT  JOIN adresses       AS adr ON (a.user_id = adr.uid AND FIND_IN_SET('active', adr.statut))
        LEFT  JOIN geoloc_pays    AS gp  ON (adr.pays = gp.a2)
        LEFT  JOIN geoloc_region  AS gr  ON (adr.pays = gr.a2 AND adr.region = gr.region)
        WHERE c.uid = {$_SESSION['uid']}
        ORDER BY a.nom, a.prenom";
$page->mysql_assign($sql,'contacts','nb_contacts');

$page->run();
?>
