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
        $Id: auth-groupex.php,v 1.7 2004-10-08 20:07:18 web Exp $
 ***************************************************************************/

$gpex_pass = $_GET["pass"];
$gpex_url = urldecode($_GET["url"]);
if (strpos($gpex_url, '?') === false) {
    $gpex_url .= "?PHPSESSID=" . $_GET["session"];
} else {
    $gpex_url .= "&PHPSESSID=" . $_GET["session"];
}
/* a-t-on besoin d'ajouter le http:// ? */
if (!preg_match("/^(http|https):\/\/.*/",$gpex_url))
    $gpex_url = "http://$gpex_url";
$gpex_challenge = $_GET["challenge"];

require("auto.prepend.inc.php");
new_skinned_page('index.tpl',AUTH_COOKIE);

// mise à jour de l'heure et de la machine de dernier login sauf quand on est en suid
if (!isset($_SESSION['suid'])) {
    $logger = (isset($_SESSION['log']) && $_SESSION['log']->uid==$uid) ? $_SESSION['log'] : new DiogenesCoreLogger($uid);
    $logger->log("connexion_auth_ext",$_SERVER['PHP_SELF']);
}

/* cree le champs "auth" renvoye au Groupe X */
function gpex_make_auth($chlg, $privkey, $datafields) {
    $fieldarr = split(",",$datafields);
    $tohash = "1$chlg$privkey";

    while(list(,$val) = each($fieldarr)) {
        /* on verifie qu'on n'a pas demandé une
           variable inexistante ! */
        if (isset($_SESSION[$val])) {
            $tohash .= stripslashes($_SESSION[$val]);
        } else if ($val == 'username') {
	    $sql = "SELECT alias FROM aliases AS al INNER JOIN auth_user_md5 AS a ON (a.user_id = al.id AND (al.type = 'a_vie' OR al.type = 'alias' OR al.type = 'epouse')) WHERE a.user_id = ".$_SESSION["uid"]." AND alias LIKE '%.%' ORDER BY LENGTH(alias)";
	    $res = mysql_query($sql);
	    list($min_username) = mysql_fetch_array($res);
            $tohash .= stripslashes($min_username);
	}
    }
    $tohash .= "1";
    return md5($tohash);
}

/* cree les parametres de l'URL de retour avec les champs demandes */
function gpex_make_params($chlg, $privkey, $datafields) {
    $params = "&auth=".gpex_make_auth($chlg, $privkey, $datafields);
    $fieldarr = split(",",$datafields);
    while(list(,$val) = each($fieldarr)) {
        if (isset($_SESSION[$val])) {
            $params .= "&$val=".$_SESSION[$val];
        } else if ($val == 'username') {
	    $sql = "SELECT alias FROM aliases AS al INNER JOIN auth_user_md5 AS a ON (a.user_id = al.id AND (al.type = 'a_vie' OR al.type = 'alias' OR al.type = 'epouse')) WHERE a.user_id = ".$_SESSION["uid"]." AND alias LIKE '%.%' ORDER BY LENGTH(alias)";
	    $res = mysql_query($sql);
	    list($min_username) = mysql_fetch_array($res);
            $params .= "&$val=".$min_username;
	}
    }
    return $params;
}

/* on parcourt les entrees de groupes_auth */
$res = $globals->db->query("select privkey,name,datafields from groupesx_auth");
while (list($privkey,$name,$datafields) = mysql_fetch_row($res)) {
    if (md5($gpex_challenge.$privkey) == $gpex_pass) {
        $returl = $gpex_url.gpex_make_params($gpex_challenge,$privkey,$datafields);
        header("Location:$returl");
        exit(0);
    }
}

/* si on n'a pas trouvé, on renvoit sur x.org */
header("Location:https://www.polytechnique.org/");
exit(0);

?>
