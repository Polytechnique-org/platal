<?php
/********************************************************************************
* include/session.inc.php : sessions for profile
* -------------------------
*
* This file is part of the banana distribution
* Copyright: See COPYING files that comes with this distribution
********************************************************************************/

$sname = $_SERVER['SCRIPT_NAME'];
$array = explode('/',$sname);
$sname = array_pop($array);
unset($array);
switch ($sname) {
  case "thread.php":
    if (!isset($_SESSION['bananapostok'])) 
      $_SESSION['bananapostok']=true;
    break;
  case "index.php":
    if (isset($_GET["banana"]) && ($_GET["banana"]=="updateall")) {
      mysql_query("UPDATE auth_user_quick SET banana_last='"
              .gmdate("YmdHis")."' WHERE user_id='{$_SESSION['uid']}'");
      $_SESSION["banana_last"]=time();
    }
  default:
    $_SESSION['bananapostok']=true;
    break;
}
?>
