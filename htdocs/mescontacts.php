<?php
require("auto.prepend.inc.php");
new_skinned_page("mescontacts.tpl",AUTH_COOKIE,true);
require("applis.func.inc.php");

// si l'utilisateur demande le retrait de qqun de sa liste
if (isset($_REQUEST['action'])) {
    if($_REQUEST['action']=='retirer') {

        if (($res = mysql_query("SELECT user_id FROM auth_user_md5 WHERE username='{$_REQUEST['user']}'")) && mysql_num_rows($res)==1) {
            list($cont_user_id) = mysql_fetch_row($res);
            if (mysql_query("DELETE FROM contacts WHERE uid = '{$_SESSION['uid']}' AND contact='$cont_user_id'"))
                $page->assign('erreur', "<p class='normal'><strong>Contact {$_REQUEST['user']} retiré !</strong></p>\n");
        }

        // si l'utilisateur demande l'ajout de qqun à sa liste
    } elseif ($_REQUEST["action"]=="ajouter") {

        if (($res = mysql_query("SELECT user_id FROM auth_user_md5 WHERE username='".$_REQUEST["user"]."'")) && mysql_num_rows($res)==1) {
            list($cont_user_id) = mysql_fetch_row($res);
            if (mysql_query("INSERT INTO contacts set uid = '{$_SESSION['uid']}', contact = '$cont_user_id'")) {
                $page->assign('erreur', '<p class="normal"><strong>Contact ajouté !</strong></p>');
            } else
                $page->assign('erreur', '<p class="erreur">Contact déjà dans la liste !</p>');
        } else {
            $page->assign('erreur', '<p class="erreur">Utilisateur inexistant ou non inscrit !</p>');
        }
    }
}

$sql = "SELECT contact AS id,
               a.*,
               i.deces != 0 AS dcd, i.matricule_ax,
               e.entreprise, es.label AS secteur, ef.label AS fonction,
               n.text AS nat,
               ad0.text AS app0text, ad0.url AS app0url, ai0.type AS app0type,
               ad1.text AS app1text, ad1.url AS app1url, ai1.type AS app1type,
               adr.ville, gp.pays, gr.name AS region
        FROM       contacts       AS c
        INNER JOIN auth_user_md5  AS a   ON (a.user_id = c.contact)
        INNER JOIN identification AS i   ON (a.matricule = i.matricule)
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
mysql_query($sql);
echo mysql_error();

$page->mysql_assign($sql,'contacts','nb_contacts');

$page->display();
?>
