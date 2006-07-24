<?php 
require_once('xorg.inc.php');

header("Content-type: text/xml");
new_nonhtml_page('geoloc/geolocInit.tpl', AUTH_COOKIE);
header("Pragma:");

$querystring = "";
foreach ($_GET as $v => $a)
	if ($v != 'initfile')
		$querystring .= '&'.urlencode($v).'='.urlencode($a);
$page->assign('querystring',$querystring);
$page->run();
?>
