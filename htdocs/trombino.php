<?php
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
    $file = "/home/web/trombino/photos".$_SESSION["promo"]."/".$_SESSION["username"].".jpg";
    $myphoto = new PhotoReq($_SESSION['uid'], $file);
    $myphoto->commit();
    $myphoto->clean();
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
