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
        $Id: FormatePrenomNOM2.php,v 1.2 2004-08-31 11:19:51 x2000habouzit Exp $
 ***************************************************************************/

require("xorg.common.inc.php");

$result=mysql_query("SELECT matricule,prenom,nom FROM identification");

while($myrow = mysql_fetch_array($result)) {

	$prenom = rtrim($myrow["prenom"]);
	$old_prenom = $prenom;
	$nom = rtrim($myrow["nom"]);
	$old_nom = $nom;
	$uid = $myrow["matricule"];

	$pre1=strtok($prenom,"-");
	$pre2=strtok(" -");
	$pre3=strtok(" -");
	$pre1=ucwords(strtolower($pre1));
	$pre2=ucwords(strtolower($pre2));
	$pre3=ucwords(strtolower($pre3));
	if ($pre3) {
		$prenom = $pre1."-".$pre2."-".$pre3;
	} else if ($pre2) {
		$prenom = $pre1."-".$pre2;
	} else {
		$prenom = $pre1;
	}
	$nom = strtoupper(strtr($nom,"éèëêàäâïîôöùûüç","eeeeaaaiioouuuc"));

	if ($old_prenom != $prenom || $old_nom != $nom) {
		echo "$old_prenom $old_nom >>>>> $prenom $nom<br>"; 
		$prenom = addslashes($prenom);
		$nom = addslashes($nom);
		//mysql_query("UPDATE identification SET prenom='$prenom',nom='$nom' WHERE matricule='$uid'");
	}

}

?>
