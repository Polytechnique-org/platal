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
        $Id: get_mentor.inc.php,v 1.1 2004-08-31 16:04:11 x2000habouzit Exp $
 ***************************************************************************/

require_once('geoloc.inc.php');
require_once('secteur.emploi.inc.php');

$max_mentor_pays = 10;
$max_mentor_secteurs = 10;

//recuperation de l'expertise
$res = $globals->db->query("SELECT expertise FROM mentor WHERE uid = {$_SESSION['uid']}");

if(mysql_num_rows($res) > 0) {
    list($mentor_expertise) = mysql_fetch_row($res);
} else {
    $mentor_expertise = '';
}
$mentor_expertise_bd = $mentor_expertise;

//suppression eventuelle d'un pays
if(isset($_POST['mentor_pays_op']) && ($_POST['mentor_pays_op'] == 'retirer')) {
    if(isset($_POST['mentor_pays_id'])) {
        $id_supprimee = $_POST['mentor_pays_id'];
        $globals->db->query("DELETE FROM mentor_pays WHERE uid = {$_SESSION['uid']} AND pid = '$id_supprimee' LIMIT 1");
    }
}

//recuperation des pays
$res = $globals->db->query("SELECT m.pid, p.pays 
                    FROM mentor_pays AS m
		    LEFT JOIN geoloc_pays AS p ON(m.pid = p.a2) WHERE m.uid = {$_SESSION['uid']} LIMIT $max_mentor_pays");
$nb_mentor_pays = mysql_num_rows($res);
$mentor_pid = $mentor_pays = Array();
for($i = 1; $i <= $nb_mentor_pays ; $i++) list($mentor_pid[$i], $mentor_pays[$i]) = mysql_fetch_row($res);

//ajout eventuel d'un pays
if(isset($_POST['mentor_pays_op']) && ($_POST['mentor_pays_op'] == 'ajouter') && ($nb_mentor_pays < $max_mentor_pays)) {
    if(isset($_POST['mentor_pays_id']) && ($_POST['mentor_pays_id'] != '00')) {
	$id_ajoutee = $_POST['mentor_pays_id'];
	$globals->db->query("INSERT INTO mentor_pays(uid, pid) VALUES('{$_SESSION['uid']}', '$id_ajoutee')");
	$nb_mentor_pays++;
	$mentor_pid[$nb_mentor_pays] = $id_ajoutee;
	$mentor_pays[$nb_mentor_pays] = stripslashes($_POST['mentor_pays_name']);
    }
}



//suppression d'un secteur / ss-secteur
if(isset($_POST['mentor_secteur_op']) && ($_POST['mentor_secteur_op'] == 'retirer')) {
    if(isset($_POST['mentor_secteur_id'])) {
        $id_supprimee = $_POST['mentor_secteur_id'];
        $globals->db->query("DELETE FROM mentor_secteurs WHERE uid = {$_SESSION['uid']} AND secteur = '$id_supprimee' LIMIT 1");
    }
}

//recuperation des secteurs
$res = $globals->db->query("SELECT m.secteur, s.label, m.ss_secteur, ss.label
                    FROM mentor_secteurs AS m
		    LEFT JOIN emploi_secteur AS s ON(m.secteur = s.id)
		    LEFT JOIN emploi_ss_secteur AS ss ON(s.id = ss.secteur AND m.ss_secteur = ss.id)
		    WHERE m.uid = {$_SESSION['uid']}
		    LIMIT $max_mentor_pays");
$nb_mentor_secteurs = mysql_num_rows($res);
$mentor_sid = $mentor_secteur = $mentor_ssid = $mentor_ss_secteur = Array();
for($i = 1; $i <= $nb_mentor_secteurs ; $i++)
    list($mentor_sid[$i], $mentor_secteur[$i], $mentor_ssid[$i], $mentor_ss_secteur[$i]) = mysql_fetch_row($res);

//ajout d'un secteur
$mentor_secteur_id_new = '';
if(isset($_POST['mentor_secteur_op']) && ($_POST['mentor_secteur_op'] == 'ajouter') && ($nb_mentor_secteurs < $max_mentor_secteurs)) {
    if(isset($_POST['mentor_secteur_id']) && ($_POST['mentor_secteur_id'] != ''))
    {
	$sid_ajoutee = $_POST['mentor_secteur_id'];
	if(isset($_POST['mentor_ss_secteur_id']))
	    $ssid_ajoutee = $_POST['mentor_ss_secteur_id'];
	$globals->db->query("INSERT INTO mentor_secteurs (uid, secteur, ss_secteur)
				    VALUES('{$_SESSION['uid']}', '$sid_ajoutee',".( ($ssid_ajoutee == '')?'NULL':"'$ssid_ajoutee'" ).")");
	$nb_mentor_secteurs++;
	$mentor_sid[$nb_mentor_secteurs] = $sid_ajoutee;
	$mentor_secteur[$nb_mentor_secteurs] = stripslashes($_POST['mentor_secteur_name']);
	$mentor_ssid[$nb_mentor_secteurs] = $ssid_ajoutee;
	$mentor_ss_secteur[$nb_mentor_secteurs] = stripslashes($_POST['mentor_ss_secteur_name']);
    }
} elseif(isset($_POST['mentor_secteur_id_new'])){
    $mentor_secteur_id_new = $_POST['mentor_secteur_id_new'];
}
?>
