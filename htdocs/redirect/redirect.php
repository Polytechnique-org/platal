<?php

require("db_connect.inc.php");

/*echo "<pre>";
var_dump($_SERVER);
echo "</pre>";*/

// on coupe la chaîne REQUEST_URI selon les / et on ne garde que
// le premier non vide et éventuellement le second
// la config d'apache impose la forme suivante pour REQUEST_URI :
// REQUEST_URI = /prenom.nom(/path/fichier.hmtl)?
list($username, $path) = preg_split('/\//', $_SERVER["REQUEST_URI"], 2, PREG_SPLIT_NO_EMPTY);

$result = mysql_query("select redirecturl from auth_user_md5 where username= '$username' or alias = '$username'");
if ($result and list($url) = mysql_fetch_row($result) and $url != '') {
	// on envoie un redirect (PHP met automatiquement le code de retour 302
	if (!empty($path)) {
	    if (substr($url, -1, 1) == "/")
	        $url .= $path;
	    else
	        $url .= "/" . $path;
	}
	header("Location: http://$url");
	exit();
}

// si on est ici, il y a eu un erreur ou on n'a pas trouvé le redirect
header("HTTP/1.0 404 Not Found");

?>

<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
The requested URL <?php echo $_SERVER['REQUEST_URI'] ?> was not found on this server.<p>
<hr>
<address>Apache Server at www.carva.org Port 80</address>
</body></html>
