<?php
require("auto.prepend.inc.php");
new_skinned_page('cookie_on.tpl', AUTH_MDP);

$res = @mysql_query( "SELECT password FROM auth_user_md5 WHERE user_id='{$_SESSION['uid']}'" );
list($password)=mysql_fetch_row($res);
$cookie=md5($password);
@mysql_free_result($res);

setcookie('ORGaccess',$cookie,(time()+25920000),'/','',0);
$_SESSION['log']->log("cookie_on");

$page->run();
?>
