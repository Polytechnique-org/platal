<?php
$i=0;
define("AUTH_PUBLIC", $i++);
define("AUTH_COOKIE", $i++);
define("AUTH_MDP", $i++);

define("PERMS_EXT", "ext");
define("PERMS_USER", "user");
define("PERMS_ADMIN", "admin");

define('SKIN_COMPATIBLE','default.tpl');
define('SKIN_COMPATIBLE_ID',1);
define('SKIN_STOCHASKIN_ID','254');

define('SKINNED', 0);
define('NO_SKIN', 1);

// import class definitions
require("diogenes.database.inc.php");
require("xorg.globals.inc.php");
require("xorg.session.inc.php");

$globals = new XorgGlobals;
require("config.xorg.inc.php");

session_start();

// connect to database
$globals->dbconnect();
$conn = $globals->db->connect_id;
?>
