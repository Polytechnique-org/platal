<?php
require("auto.prepend.inc.php");

$sql = "DELETE FROM perte_pass WHERE DATE_SUB(NOW(), INTERVAL 380 MINUTE) > created";
$globals->db->query($sql);

$certificat = isset($_REQUEST['certificat']) ? $_REQUEST['certificat'] : "";
$sql = "SELECT uid FROM perte_pass WHERE certificat='$certificat'";
$result = $globals->db->query($sql);

if ($ligne = mysql_fetch_array($result))  {
    $uid=$ligne["uid"];
    if (!empty($_POST['response2']))  {             // la variable $response existe-t-elle ?
    // OUI, alors changeons le mot de passe
        $password = $_POST['response2'];
        $sql = "UPDATE auth_user_md5 SET password='$password' WHERE user_id=".$uid;
        $globals->db->query($sql);
        $logger = new DiogenesCoreLogger($uid);
        $logger->log("passwd","");
        $sql = "DELETE FROM perte_pass WHERE certificat='$certificat'";
        $globals->db->query($sql);
        new_skinned_page('tmpPWD.success.tpl', AUTH_PUBLIC);
        $page->run();
    }
    else {
        new_skinned_page('motdepassemd5.tpl', AUTH_PUBLIC, true,'motdepassemd5.head.tpl');
        $page->run();
    }
}
else {
    new_skinned_page('tmpPWD.failure.tpl', AUTH_PUBLIC);
    $page->run();
}

?>
