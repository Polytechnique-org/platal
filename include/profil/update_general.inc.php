<?php
if ($appli_id1>0)
     mysql_query("replace into applis_ins set uid={$_SESSION['uid']},aid=$appli_id1,type='$appli_type1',ordre=0");
else
     mysql_query("delete from applis_ins where uid={$_SESSION['uid']} and ordre=0");

if ($appli_id2>0)
     mysql_query("replace into applis_ins set uid={$_SESSION['uid']},aid=$appli_id2,type='$appli_type2',ordre=1");
else
     mysql_query("delete from applis_ins where uid={$_SESSION['uid']} and ordre=1");

$sql = "UPDATE auth_user_md5 SET ".
// champs calculés ou vérifés
"alias='$alias',nationalite=$nationalite,web='$web',".
"mobile='$mobile',".
// champs libres, on ajoute les slashes
"libre='".put_in_db($libre)."' WHERE user_id={$_SESSION['uid']}";


mysql_query($sql);

?>
