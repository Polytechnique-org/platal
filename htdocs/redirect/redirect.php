<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: redirect.php,v 1.5 2004-09-05 12:54:20 x2000habouzit Exp $
 ***************************************************************************/


require("db_connect.inc.php");

/*echo "<pre>";
var_dump($_SERVER);
echo "</pre>";*/

// on coupe la chaîne REQUEST_URI selon les / et on ne garde que
// le premier non vide et éventuellement le second
// la config d'apache impose la forme suivante pour REQUEST_URI :
// REQUEST_URI = /prenom.nom(/path/fichier.hmtl)?
list($user, $path) = preg_split('/\//', $_SERVER["REQUEST_URI"], 2, PREG_SPLIT_NO_EMPTY);

$result = $globals->db->query("
    SELECT  redirecturl
      FROM  auth_user_md5 AS u
INNER JOIN  aliases       AS a ON (u.user_id=a.id AND type!='homonyme')
     WHERE  a.alias='$user'");
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
