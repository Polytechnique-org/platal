<?php
require("auto.prepend.inc.php");

if (!empty($_POST['response2']))  {             // la variable $response existe-t-elle ?
    // OUI, alors changeons le mot de passe
    $password = $_POST['response2'];
    $sql = "UPDATE auth_user_md5 SET password='$password' WHERE user_id=".$_SESSION['uid'];
    mysql_query($sql);
    $_SESSION['log']->log("passwd","");
    new_skinned_page('motdepassemd5.success.tpl', AUTH_MDP);
    $page->display();
}

new_skinned_page('motdepassemd5.tpl', AUTH_MDP, true, 'motdepassemd5.head.tpl');
$page->display();
?>
