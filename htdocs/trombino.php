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
        $Id: trombino.php,v 1.4 2004-11-12 21:30:58 x2000coic Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('trombino.tpl', AUTH_MDP);

require("validations.inc.php");

if (isset($_REQUEST["ordi"]) and
    isset($_FILES["userfile"]) and isset($_FILES["userfile"]["tmp_name"])) {
    //Fichier en local
    $myphoto = new PhotoReq($_SESSION['uid'], $_FILES["userfile"]["tmp_name"]);
    if(!isset($erreur))
        $myphoto->submit();
} elseif (isset($_REQUEST["web"]) and isset($_REQUEST["photo"])) {
    // net
    $fp = fopen($_REQUEST["photo"], 'r');
    if (!$fp) {
        $erreur = "Fichier inexistant";
    } else {
        $attach = fread($fp, 35000);
        fclose($fp);
        $file = tempnam('/tmp','photo_');
        $fp = fopen($file,'w');
        fwrite($fp, $attach);
        fclose($fp);

	$myphoto = new PhotoReq($_SESSION['uid'], $file);
    if(!isset($erreur))
        $myphoto->submit();
    }
} elseif (isset($_REQUEST["trombi"])) {
    // Fichier à récupérer dans les archives trombi + commit immédiat
    $file = "/home/web/trombino/photos".$_SESSION["promo"]."/".$_SESSION["forlife"].".jpg";
    $myphoto = new PhotoReq($_SESSION['uid'], $file);
    if($myphoto){// There was no errors, we can go on
      $myphoto->commit();
      $myphoto->clean();
    }
} elseif (isset($_REQUEST["suppr"])) {
    // effacement de la photo
    $globals->db->query("DELETE FROM photo WHERE uid = ".$_SESSION["uid"]);
    $globals->db->query("DELETE FROM requests WHERE user_id = ".$_SESSION["uid"]." AND type='photo'");
}

// Si une requête a été faite et qu'une erreur est signalée, on affiche l'erreur
if(isset($erreur)) $page->assign('erreur', $erreur);

$sql = $globals->db->query("SELECT * FROM requests WHERE user_id='{$_SESSION['uid']}' AND type='photo'");
$page->assign('submited', mysql_num_rows($sql) > 0);

$page->run();

// Affichage de la page principale
?>
