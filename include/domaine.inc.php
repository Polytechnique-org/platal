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
        $Id: domaine.inc.php,v 1.4 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/

/** liste les domaines présents dans le result mysql donné
* @see domaineperso.php
* @see groupex.php
* @return Array
*/
function liste_domaines() {

    // on récupère la liste des domaines gérables par l'utilisateur
    $sql = "SELECT domain FROM groupex.aliases as d ".
            (has_perms() ? '' : ', groupex.gestionnaires as g').
           " WHERE domain NOT LIKE '%@%' ".
            (has_perms() ? '' : " AND g.did = d.id AND g.uid = {$_SESSION['uid']}").
           " ORDER BY domain";

    $result = $globals->db->query($sql);

    $ret = Array();
    while (list($domaine) = mysql_fetch_row($result)) {
        $ret[] = $domaine;
    }
    mysql_free_result($result);

    return $ret;
}

/** ajoute domaine
* @param $domaine nom du domaine
* @return string message indiquant le succes ou l'echec de l'ajout de domaine
* @see domaineperso.php
*/
function add_domain($domaine) {
    if ($_SESSION['perms'] == 'admin') {
        $ok = true;
    } else { // si pas admin, on verifie qu'il n'a pas deja de domaine
        $sql = "SELECT 1 FROM groupex.aliases AS a, groupex.gestionnaires AS g
                WHERE g.uid = {$_SESSION['uid']} AND a.id = g.did AND a.domain NOT LIKE '%@%'
                    AND a.domain NOT LIKE '%polytechnique.org'";
        $result = $globals->db->query($sql);
        $ok = (mysql_num_rows($result) < 1);
    }
    if ($ok) {
        // ok, ajout du domaine et des droits d'utilisation (sauf si admin)
        if($globals->db->query("insert into groupex.aliases set domain = '$domaine'")) {;
        $did = mysql_insert_id();
            if ($_SESSION['perms'] != 'admin')
            if($globals->db->query("INSERT INTO groupex.gestionnaires SET did = $did, uid = {$_SESSION['uid']}"))
                return "<p class='erreur'>Félicitations, ton domaine a été ajouté aux domaines gérés par Polytechnique.org.\n"
                    .  "Clique sur le lien ci-dessous pour lui ajouter des alias</p>\n";
        } else
            return "<p class='erreur'>Erreur : ".mysql_error()."</p>\n";
    } else {
        return "<p class=\"erreur\">Tu as déjà un nom de domaine perso, tu ne peux pas en avoir plusieurs.</p>\n";
    }
}

?>
