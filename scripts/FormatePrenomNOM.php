<?php echo "L'usage de cette page est réservé à un root"; exit; ?>
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
        $Id: FormatePrenomNOM.php,v 1.3 2004-11-22 11:16:32 x2000habouzit Exp $
 ***************************************************************************/

require("xorg.inc.php");

$result=mysql_query("SELECT user_id,prenom,nom FROM auth_user_md5");

while($myrow = mysql_fetch_array($result)) {

	$prenom = $myrow["prenom"];
	$old_prenom = $prenom;
	$nom = $myrow["nom"];
	$old_nom = $nom;
	$uid = $myrow["user_id"];

	$pre1=strtok($prenom,"-");
	$pre2=strtok(" ");
	$pre1=ucwords(strtolower($pre1));
	$pre2=ucwords(strtolower($pre2));
	if ($pre2) {
		$prenom = $pre1."-".$pre2;
	} else {
		$prenom = $pre1;
	}
	$nom = strtoupper(strtr($nom,"éèëêàäâïîôöùûüç","eeeeaaaiioouuuc"));

	if ($old_prenom!=$prenom || $old_nom!=$nom) {
		echo "$old_prenom $old_nom >>>>> $prenom $nom<br>"; 
		$prenom = addslashes($prenom);
		$nom = addslashes($nom);
		//mysql_query("UPDATE auth_user_md5 SET prenom='$prenom',nom='$nom' WHERE user_id=$uid");
	}

}

?>
