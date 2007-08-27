<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

require_once('geoloc.inc.php');
require_once('secteur.emploi.inc.php');

$max_mentor_pays = 10;
$max_mentor_secteurs = 10;

//recuperation de l'expertise
$res = XDB::query("SELECT expertise FROM mentor WHERE uid = {?}", S::v('uid',-1));

$mentor_expertise = $res->fetchOneCell();
$mentor_expertise_bd = $mentor_expertise;

//suppression eventuelle d'un pays
if(Post::v('mentor_pays_op', '') == 'retirer') {
    if(Post::has('mentor_pays_id')) {
        $id_supprimee = Post::v('mentor_pays_id', '00');
        XDB::execute("DELETE FROM mentor_pays WHERE uid = {?} AND pid = {?} LIMIT 1", S::v('uid', -1), $id_supprimee);
    }
}

//recuperation des pays
$res = XDB::iterRow("SELECT m.pid, p.pays
                    FROM mentor_pays AS m
		    LEFT JOIN geoloc_pays AS p ON(m.pid = p.a2) WHERE m.uid = {?} LIMIT {?}", S::v('uid', -1), $max_mentor_pays);
$nb_mentor_pays = $res->total();
$mentor_pid = $mentor_pays = Array();
for($i = 1; $i <= $nb_mentor_pays ; $i++) list($mentor_pid[$i], $mentor_pays[$i]) = $res->next();

//ajout eventuel d'un pays
if((Post::v('mentor_pays_op', '') == 'ajouter') && ($nb_mentor_pays < $max_mentor_pays)) {
    if(Post::v('mentor_pays_id', '00') != '00') {
	$id_ajoutee = Post::v('mentor_pays_id', '00');
	XDB::execute("INSERT INTO mentor_pays(uid, pid) VALUES({?}, {?})", S::v('uid', -1), $id_ajoutee);
	$nb_mentor_pays++;
	$mentor_pid[$nb_mentor_pays] = $id_ajoutee;
	$mentor_pays[$nb_mentor_pays] = Post::v('mentor_pays_name', '');
    }
}



//suppression d'un secteur / ss-secteur
if(Post::v('mentor_secteur_op', '') == 'retirer') {
    if(Post::has('mentor_secteur_id')) {
        $id_supprimee = Post::v('mentor_secteur_id', '');
        XDB::execute("DELETE FROM mentor_secteurs WHERE uid = {?} AND secteur = {?} LIMIT 1", S::v('uid', -1), $id_supprimee);
    }
}

//recuperation des secteurs
$res = XDB::iterRow("SELECT m.secteur, s.label, m.ss_secteur, ss.label
                    FROM mentor_secteurs AS m
		    LEFT JOIN emploi_secteur AS s ON(m.secteur = s.id)
		    LEFT JOIN emploi_ss_secteur AS ss ON(s.id = ss.secteur AND m.ss_secteur = ss.id)
		    WHERE m.uid = {?}
		    LIMIT {?}", S::v('uid', -1), $max_mentor_pays);
$nb_mentor_secteurs = $res->total();
$mentor_sid = $mentor_secteur = $mentor_ssid = $mentor_ss_secteur = Array();
for($i = 1; $i <= $nb_mentor_secteurs ; $i++)
    list($mentor_sid[$i], $mentor_secteur[$i], $mentor_ssid[$i], $mentor_ss_secteur[$i]) = $res->next();

//ajout d'un secteur
$mentor_secteur_id_new = '';
if((Post::v('mentor_secteur_op', '')== 'ajouter') && ($nb_mentor_secteurs < $max_mentor_secteurs)) {
    if(Post::v('mentor_secteur_id', '') != '')
    {
	$sid_ajoutee = Post::v('mentor_secteur_id', '');
	if(Post::has('mentor_ss_secteur_id'))
	    $ssid_ajoutee = Post::v('mentor_ss_secteur_id', '');
	XDB::execute("INSERT INTO mentor_secteurs (uid, secteur, ss_secteur)
				    VALUES({?}, {?}, {?})", S::v('uid', -1), $sid_ajoutee, ($ssid_ajoutee == '')?null:$ssid_ajoutee);
	$nb_mentor_secteurs++;
	$mentor_sid[$nb_mentor_secteurs] = $sid_ajoutee;
	$mentor_secteur[$nb_mentor_secteurs] = Post::v('mentor_secteur_name', '');
	$mentor_ssid[$nb_mentor_secteurs] = $ssid_ajoutee;
	$mentor_ss_secteur[$nb_mentor_secteurs] = Post::v('mentor_ss_secteur_name', '');
    }
} elseif(Post::has('mentor_secteur_id_new')){
    $mentor_secteur_id_new = Post::v('mentor_secteur_id_new', '');
}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
