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


require_once("xorg.inc.php");
new_simple_page('fiche_referent.tpl',AUTH_COOKIE);

if (!Env::has('user')) {
    exit;
}

$res = $globals->xdb->query(
        "SELECT  prenom, nom, user_id, promo, cv, a.alias AS bestalias
          FROM  auth_user_md5 AS u
    INNER JOIN  aliases       AS a ON (u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
    INNER JOIN  aliases       AS a1 ON (u.user_id=a1.id AND a1.alias = {?} AND a1.type!='homonyme')",
    Env::get('user'));
if ($res->numRows() != 1) {
    exit;
}

list($prenom, $nom, $user_id, $promo, $cv, $bestalias) = $res->fetchOneRow();

$page->assign('prenom', $prenom);
$page->assign('nom', $nom);
$page->assign('promo', $promo);
$page->assign('cv', $cv);
$page->assign('bestalias', $bestalias);


//recuperation des infos professionnelles
$res = $globals->xdb->query(
        "SELECT  e.entreprise, s.label as secteur , ss.label as ss_secteur , f.fonction_fr as fonction,
                 e.poste, e.adr1, e.adr2, e.adr3, e.cp, e.ville,
                 gp.pays, gr.name, e.tel, e.fax, e.mobile
           FROM  entreprises AS e
      LEFT JOIN  emploi_secteur AS s ON(e.secteur = s.id)
      LEFT JOIN  emploi_ss_secteur AS ss ON(e.ss_secteur = ss.id AND e.secteur = ss.secteur)
      LEFT JOIN  fonctions_def AS f ON(e.fonction = f.id)
      LEFT JOIN  geoloc_pays AS gp ON (gp.a2 = e.pays)
      LEFT JOIN  geoloc_region AS gr ON (gr.a2 = e.pays and gr.region = e.region)
          WHERE  e.uid = {?}
       ORDER BY  e.entrid", $user_id);
$page->assign('adr_pro', $res->fetchAllAssoc());

/////  recuperations infos referent

//expertise
$res = $globals->xdb->query("SELECT expertise FROM mentor WHERE uid = {?}", $user_id);
$page->assign('expertise', $res->fetchOneCell());

//secteurs
$secteurs = $ss_secteurs = Array();
$res = $globals->xdb->iterRow(
        "SELECT  s.label, ss.label
           FROM  mentor_secteurs AS m
      LEFT JOIN  emploi_secteur AS s ON(m.secteur = s.id)
      LEFT JOIN  emploi_ss_secteur AS ss ON(m.secteur = ss.secteur AND m.ss_secteur = ss.id)
          WHERE  uid = {?}", $user_id);
while (list($sec, $ssec) = $res->next()) {
    $secteurs[]    = $sec;
    $ss_secteurs[] = $ssec;
}
$page->assign_by_ref('secteurs', $secteurs);
$page->assign_by_ref('ss_secteurs', $ss_secteurs);

//pays
$res = $globals->xdb->query(
        "SELECT  gp.pays
           FROM  mentor_pays AS m
      LEFT JOIN  geoloc_pays AS gp ON(m.pid = gp.a2)
          WHERE  uid = {?}", $user_id);
$page->assign('pays', $res->fetchColumn());
$page->run();

?>
