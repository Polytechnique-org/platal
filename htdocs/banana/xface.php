<?php

$face = base64_decode($_REQUEST['face']);
$face = ereg_replace("'", "'\\''", $face);

header("Content-Type: image/png");
passthru("echo '$face'|uncompface -X |convert xbm:- png:-");
?>
