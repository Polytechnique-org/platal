<?php
require("auto.prepend.inc.php");
new_skinned_page('emails.tpl',AUTH_COOKIE);

// on regarde si on a affaire à un homonyme
$res = $globals->db->query("SELECT username!=loginbis AND loginbis!='',alias FROM auth_user_md5 WHERE username = '".$_SESSION["username"]."'");
list($is_homonyme,$alias) = mysql_fetch_row($res);
mysql_free_result($res);
$page->assign('is_homonyme', $is_homonyme);
$page->assign('alias', $alias);


$sql = "SELECT email
        FROM emails
        WHERE uid = {$_SESSION["uid"]} AND num != 0 AND (FIND_IN_SET('active', flags) OR FIND_IN_SET('filtre', flags))";
$page->mysql_assign($sql, 'mails', 'nb_mails');


// on regarde si l'utilisateur a un alias et si oui on l'affiche !
$sql = "SELECT domain FROM groupex.aliases WHERE id=12 AND email like '".$_SESSION['username']."'";
$result = $globals->db->query($sql);
if ($result && list($aliases) = mysql_fetch_row($result))
    $page->assign('melix', substr($aliases,0,-3));
mysql_free_result($result);

$page->run();
?> 
