<?php
require("db_connect.inc.php");
require("controlpermanent.inc.php");
require_once("appel.inc.php");
require_once("validations.inc.php");

// getdata.php3 - by Florian Dittmer <dittmer@gmx.net> 
// Example php script to demonstrate the direct passing of binary data 
// to the user. More infos at http://www.phpbuilder.com 
// Syntax: getdata.php3?id=<id> 

if(isset($_REQUEST['x'])) {
	if(isset($_REQUEST['req']) && $_REQUEST['req']="true") {
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
			$f=fopen(url("none.png"),"r");
			echo fread($f,30000);
			fclose($f);
		}
	}
}
?>
