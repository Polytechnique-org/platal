<?php
//mise a jour d'expertise si nécessaire

if($mentor_expertise != $mentor_expertise_bd)
{
  mysql_query("REPLACE INTO mentor(uid, expertise) VALUES('{$_SESSION['uid']}', '".put_in_db($mentor_expertise)."')");
}


?>
