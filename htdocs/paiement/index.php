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
        $Id: index.php,v 1.7 2004-11-22 20:05:04 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_skinned_page('paiment/index.tpl', AUTH_MDP);
require_once('profil.func.inc.php');
setlocale(LC_NUMERIC,'fr_FR');

function comp($s1,$s2) {
    list($r1,$a1) = split(',', $s1);
    list($r2,$a2) = split(',', $s2);
    $n1 = $r1*100+$a1;
    $n2 = $r2*100+$a2;
    if($n1>$n2) return 1;
    if($n1<$n2) return -1;
    return 0;
}

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

if (comp($montant,$montant_min)<0) {
    $erreur[] = "Montant inférieur au minimum autorisé ($montant_min).";
    $montant = $montant_min;
}

if (comp($montant,$montant_max)>0) {
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
