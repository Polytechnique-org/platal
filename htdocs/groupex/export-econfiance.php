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
        $Id: export-econfiance.php,v 1.10 2004/11/22 11:15:33 x2000habouzit Exp $
 ***************************************************************************/


/* Script permettant l'export de la liste des membres de la mailing list eConfiance, pour intégration par J-P Figer dans la liste des membres de X-Informatique */

require_once("xorg.inc.php");
require_once("xml-rpc-client.inc.php");

$cle = $globals->econfiance;

if (isset($_SESSION["chall"]) && $_SESSION["chall"] != "" && $_GET["PASS"] == md5($_SESSION["chall"].$cle)) {

    $res = $globals->db->query("SELECT password FROM auth_user_md5 WHERE user_id=10154");
    list($pass) = mysql_fetch_row($res);
    mysql_free_result($res);


    $client = new xmlrpc_client("http://10154:$pass@localhost:4949/polytechnique.org");
    $members = $client->get_members('x-econfiance');
    if(is_array($members)) {
	$membres = Array();
	foreach($members[1] as $member) {
	    if(preg_match('/^([^.]*.[^.]*.(\d\d\d\d))@polytechnique.org$/', $member[1], $matches)) {
		$membres[] = "a.alias='{$matches[1]}'";
	    }
	}
    }

    $where = join(' OR ',$membres);

    $all = $globals->db->query("SELECT  u.prenom,u.nom,a.alias
				  FROM  auth_user_md5 AS u
			    INNER JOIN  aliases       AS a ON ( u.user_id = a.id AND a.type!='homonyme' )
				 WHERE  $where
			      ORDER BY  nom");

    $res = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n<membres>\n\n";

    while (list ($prenom1,$nom1,$email1) = mysql_fetch_row($all)) {
	    $res .= "<membre>\n";
	    $res .= "\t<nom>".$nom1."</nom>\n";
	    $res .= "\t<prenom>".$prenom1."</prenom>\n";
	    $res .= "\t<email>".$email1."</email>\n";
	    $res .= "</membre>\n\n";
    }
    mysql_free_result($all);

    $res .= "</membres>\n\n";

    echo $res;
}

?>
