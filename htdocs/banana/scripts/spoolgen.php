<?php
/********************************************************************************
* spoolgen.php : spool generation
* --------------
*
* This file is part of the banana distribution
* Copyright: See COPYING files that comes with this distribution
********************************************************************************/

ini_set('max_execution_time','300');
ini_set('include_path', '..:../include:../../include:../../../include');

require("include/encoding.inc.php");
require("include/config.inc.php");
require("include/NetNNTP.inc.php");
include("include/post.inc.php");
include("include/groups.inc.php");
require("include/spool.inc.php");
require("include/password.inc.php");


$nntp = new nntp($news['server']);
if (!$nntp) {
  print "cannot connect to server\n";
  exit;
}

if ($news['user']!="anonymous") {
  $result = $nntp->authinfo($news["user"],$news["pass"]);
  if (!$result) {
    print "authentication error\n";
    exit;
  }
}
unset($result);

$groups = new groups($nntp,2);
$list = array_keys($groups->overview);
unset($groups);
foreach ($list as $g) {
  print "Generating spool for $g : ";
  $spool = new spool($nntp,$g);
  print "done.\n";
  unset($spool);
}
$nntp->quit();
?>
