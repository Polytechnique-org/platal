<?php
$sql = "UPDATE auth_user_md5 set section=$section WHERE user_id={$_SESSION['uid']}";

mysql_query($sql);

?>
