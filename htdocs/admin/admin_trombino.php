<?php
require("auto.prepend.inc.php");
new_admin_page('admin/admin_trombino.tpl');

$q = $globals->db->query("SELECT username,promo FROM auth_user_md5 WHERE user_id = '" . $_REQUEST["uid"] . "'");
list($username, $promo) = mysql_fetch_row($q);

if (isset($_REQUEST["action"])) {
    switch ($_REQUEST["action"]) {

    case "ecole":
        header("Content-type: image/jpeg");
	readfile("/home/web/trombino/photos".$promo."/".$username.".jpg");
        exit;
	break;

    case "valider":
        $handle = fopen ($_FILES['userfile']['tmp_name'], "r");
	$data = fread ($handle, filesize ($_FILES['userfile']['tmp_name']));
	fclose ($handle);
	list($x, $y) = getimagesize($_FILES['userfile']['tmp_name']);
	$mimetype = substr($_FILES['userfile']['type'], 6);
	unlink($_FILES['userfile']['tmp_name']);
        $globals->db->query(
	  "REPLACE INTO photo
	   SET uid='".$_REQUEST["uid"]."',
	   attachmime = '".$mimetype."',
	   attach='".addslashes($data)."',
	   x='".$x."', y='".$y."'");
    	break;

    case "supprimer":
        $globals->db->query("DELETE FROM photo WHERE uid = '".$_REQUEST["uid"]."'");
        break;
    }
}

$page->assign('username', $username);
$page->run();
?>
