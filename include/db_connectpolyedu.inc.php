<?php
require("config.xorg.inc.php");



/** connexion à polyedu.polytechnique.fr
 * @return une connexion MySQL
 * @see admin/utilisateurs.php
 * @see step4.php
 * @see x.php
 */
function connect_polyedu () {
	global $dbuser, $dbpwd;
	// pas pour les test
	$dbhost = "polyedu.polytechnique.fr";
	$db_edu = @mysql_connect($dbhost,$dbuser,$dbpwd);
	if (!$db_edu) {
		echo mysql_error();
		return false;
	} else {
		mysql_select_db("polyedu",$db_edu);
		return $db_edu;
	}
}



?>
