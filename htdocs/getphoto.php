<?php

require('auto.prepend.inc.php');
new_skinned_page('login.tpl', AUTH_COOKIE);

//require("db_connect.inc.php");
//require("controlpermanent.inc.php");
//require_once("appel.inc.php");
//require_once("validations.inc.php");

// getdata.php3 - by Florian Dittmer <dittmer@gmx.net> 
// Example php script to demonstrate the direct passing of binary data 
// to the user. More infos at http://www.phpbuilder.com 
// Syntax: getdata.php3?id=<id> 

function url($url) {
    $chemins = Array('.', '..', '/');
    foreach ($chemins as $ch)
	if (file_exists("$ch/login.php") || file_exists("$ch/public/login.php"))
	    return "$ch/$url";
    return "";
}

if(isset($_REQUEST['x'])) {
    if(isset($_REQUEST['req']) && $_REQUEST['req']="true") {
    include 'validations.inc.php';
	$myphoto = PhotoReq::get_unique_request($_REQUEST['x']);
	Header("Content-type: image/".$myphoto->mimetype);
	echo $myphoto->data;
    } else {
	$result = $globals->db->query("SELECT attachmime, attach FROM photo WHERE uid = '{$_REQUEST['x']}'");

	if(  list($type,$data) = @mysql_fetch_row($result) ) {
	    Header(  "Content-type: image/$type");
	    echo $data;
	} else {
	    Header(  "Content-type: image/png");
	    $f=fopen(url("images/none.png"),"r");
	    echo fread($f,30000);
	    fclose($f);
	}
    }
}
?>
