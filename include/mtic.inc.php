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
        $Id: mtic.inc.php,v 1.2 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/

$mtic_domains = "/etc/postfix/forward-domaines.conf";
$mtic_mailforward = '"|/home/listes/mailforward ';



/** nettoyage du champ email s'il contient la redirection
 * @param $email email
 * @return le email épuré
 * @see utilisée nulle part
 */
function clean_mtic($email) {
	global $mtic_mailforward;
	$len = strlen($mtic_mailforward);
	if (strncmp($mtic_mailforward,$email,$len) == 0) {
		// on vire le debut (mtic_mailforward) et le dernier caractere
		$email = substr($email,$len, -1);
	}
	return $email;
}



/** on regarde l'adresse et on ajoute la redirection si necessaire
 * @param $email email
 * @return true || false
 * @see emails.php
 * @see step4.php
 */
function check_mtic($email) {
	global $mtic_domains,$mtic_mailforward;
	list($local,$domain) = explode("@",$email);
	// lecture du fichier de configuration
	$tab = file($mtic_domains);
	foreach ($tab as $ligne) {
		if ($ligne{0} != '#') { // on saute les commentaires
			// pour chaque ligne, on regarde si la première partie
			// qui correspond au domaine du destinataire
			// matche le domaine de l'email donnée
			$a = explode(':',$ligne);
			$regexp = $a[0];
			if (eregi($regexp,$domain)) {
				// c'est le cas, on revoie true
				return true;
			}
		}
	}
	return false;
}



?>
