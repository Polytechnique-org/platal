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
        $Id: index.php,v 1.4 2004-10-09 09:29:03 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('paiment/index.tpl', AUTH_MDP);
require_once('profil.func.inc.php');
setlocale(LC_NUMERIC,'fr_FR');


// initialisation
$op = isset($_REQUEST['op']) ? $_REQUEST['op'] : 'select';
$methode = isset($_REQUEST['methode']) ? $_REQUEST['methode'] : 0;
$erreur = Array();

// on recupere les infos relatives a la transaction choisie
$ref = isset($_REQUEST['ref']) ? $_REQUEST['ref'] : 0;
$res = $globals->db->query("SELECT text,url,flags,mail,montant_min,montant_max,montant_def FROM paiement.paiements WHERE id=$ref");

if (!list($ref_text,$ref_url,$ref_flags,$ref_mail,$montant_min,$montant_max,$montant_def) = mysql_fetch_row($res)) {
    $erreur[] = "La transaction selectionnée n'est pas valide.";
}
$ref_flags = new flagset($ref_flags);

if($ref_flags->hasflag('old')){
    $erreur[] = "La transaction selectionnée est périmée.";
    //Don x.org, toujours valable :)
    $ref = 0;
    $res = $globals->db->query("SELECT text,url,flags,mail,montant_min,montant_max,montant_def FROM paiement.paiements WHERE id=$ref");
    if (!list($ref_text,$ref_url,$ref_flags,$ref_mail,$montant_min,$montant_max,$montant_def) = mysql_fetch_row($res)) {
        $erreur[] = "La transaction selectionnée n'est pas valide.";
    }
    $ref_flags = new flagset($ref_flags);
}

// on remplace les points par des virgules
$montant_min=strtr($montant_min,".",",");
$montant_max=strtr($montant_max,".",",");
$montant_def=strtr($montant_def,".",",");

// on recupere les infos relatives à la methode choisie
$methode = isset($_REQUEST['methode']) ? $_REQUEST['methode'] : 0;
$res = $globals->db->query("SELECT include FROM paiement.methodes WHERE id=$methode");
if (!list($methode_include) = mysql_fetch_row($res)) {
    $erreur[] = "La méthode de paiement sélectionnée n'est pas valide.";
}

// verifications
$montant = (($op=="submit") && isset($_REQUEST['montant'])) ? $_REQUEST['montant'] : $montant_def;
$montant = strtr($montant, ".", ",");

// on ajoute les centimes
if (ereg("^[0-9]+$",$montant))
$montant .= ",00";
elseif (ereg("^[0-9]+,[0-9]$",$montant))
$montant .= "0";

// on verifie que le montant est bien formatté
if (!ereg("^[0-9]+,[0-9]{2}$",$montant)) {
    $erreur[] = "Montant invalide.";
    $montant = $montant_def;
}

if (intval($montant) < $montant_min) {
    $erreur[] = "Montant inférieur au minimum autorisé ($montant_min).";
    $montant = $montant_min;
}

if (intval($montant) > $montant_max) {
    $erreur[] = "Montant supérieur au maximum autorisé ($montant_max).";
    $montant = $montant_max;
}

$page->assign('op',$op);
$page->assign('erreur',$erreur);
$page->assign('montant',$montant);

$page->assign('methode',$methode);
$page->assign('methode_include',$methode_include);

$page->assign('ref',$ref);
$page->assign('ref_url',$ref_url);

$page->run();
?>
