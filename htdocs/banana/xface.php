<?php

$face = base64_decode($_REQUEST['face']);
$face = escapeshellarg($face);

header("Content-Type: image/png");
passthru("echo $face|uncompface -X |convert xbm:- png:-");

?>
