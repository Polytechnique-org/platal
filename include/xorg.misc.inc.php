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
 ***************************************************************************/

function quoted_printable_encode($input, $line_max = 76) {
    $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
    $eol = "\n";
    $linebreak = "=0D=0A=\n    ";
    $escape = "=";
    $output = "";

    foreach ($lines as $j => $line) {
	$linlen = strlen($line);
	$newline = "";
	for($i = 0; $i < $linlen; $i++) {
	    $c = $line{$i};
	    $dec = ord($c);
	    if ( ($dec == 32) && ($i == ($linlen - 1)) ) {
		// convert space at eol only
		$c = "=20";
	    } elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) {
		// always encode "\t", which is *not* required
		$c = $escape.strtoupper(sprintf("%02x",$dec));
	    }
	    if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
		$output .= $newline.$escape.$eol;
		$newline = "    ";
	    }
	    $newline .= $c;
	} // end of for
	$output .= $newline;
	if ($j<count($lines)-1) $output .= $linebreak;
    }
    return trim($output);
}

/** vérifie si une adresse email convient comme adresse de redirection 
 * @param $email l'adresse email a verifier
 * @return BOOL
 */
function isvalid_email_redirection($email) {
    return isvalid_email($email) && 
	!preg_match("/@(polytechnique\.(org|edu)|melix\.(org|net)|m4x\.org)$/", $email);
}

/* Un soundex en français posté par Frédéric Bouchery
   Voici une adaptation en PHP de la fonction soundex2 francisée de Frédéric BROUARD (http://sqlpro.developpez.com/Soundex/).
   C'est une bonne démonstration de la force des expressions régulières compatible Perl.
   trouvé sur http://expreg.com/voirsource.php?id=40&type=Chaines%20de%20caract%E8res */
function soundex_fr($sIn)
{ 
    // Si il n'y a pas de mot, on sort immédiatement 
    if ( $sIn === '' ) return '    '; 
    // On met tout en minuscule 
    $sIn = strtoupper( $sIn ); 
    // On supprime les accents 
    $sIn = strtr( $sIn, 'ÂÄÀÇÈÉÊË¼ÎÏÔÖÙÛÜ', 'AAASEEEEEIIOOUUU' ); 
    // On supprime tout ce qui n'est pas une lettre 
    $sIn = preg_replace( '`[^A-Z]`', '', $sIn ); 
    // Si la chaîne ne fait qu'un seul caractère, on sort avec. 
    if ( strlen( $sIn ) === 1 ) return $sIn . '   '; 
    // on remplace les consonnances primaires 
    $convIn = array( 'GUI', 'GUE', 'GA', 'GO', 'GU', 'CA', 'CO', 'CU', 'Q', 'CC', 'CK' ); 
    $convOut = array( 'KI', 'KE', 'KA', 'KO', 'K', 'KA', 'KO', 'KU', 'K', 'K', 'K' ); 
    $sIn = str_replace( $convIn, $convOut, $sIn ); 
    // on remplace les voyelles sauf le Y et sauf la première par A 
    $sIn = preg_replace( '`(?<!^)[EIOU]`', 'A', $sIn ); 
    // on remplace les préfixes puis on conserve la première lettre 
    // et on fait les remplacements complémentaires 
    $convIn = array( '`^KN`', '`^(PH|PF)`', '`^MAC`', '`^SCH`', '`^ASA`', '`(?<!^)KN`', '`(?<!^)(PH|PF)`', '`(?<!^)MAC`', '`(?<!^)SCH`', '`(?<!^)ASA`' ); 
    $convOut = array( 'NN', 'FF', 'MCC', 'SSS', 'AZA', 'NN', 'FF', 'MCC', 'SSS', 'AZA' ); 
    $sIn = preg_replace( $convIn, $convOut, $sIn ); 
    // suppression des H sauf CH ou SH 
    $sIn = preg_replace( '`(?<![CS])H`', '', $sIn ); 
    // suppression des Y sauf précédés d'un A 
    $sIn = preg_replace( '`(?<!A)Y`', '', $sIn ); 
    // on supprime les terminaisons A, T, D, S 
    $sIn = preg_replace( '`[ATDS]$`', '', $sIn ); 
    // suppression de tous les A sauf en tête 
    $sIn = preg_replace( '`(?!^)A`', '', $sIn ); 
    // on supprime les lettres répétitives 
    $sIn = preg_replace( '`(.)\1`', '$1', $sIn ); 
    // on ne retient que 4 caractères ou on complète avec des blancs 
    return substr( $sIn . '    ', 0, 4); 
}

function make_forlife($prenom,$nom,$promo) {
    /* on traite le prenom */
    $prenomUS=replace_accent(trim($prenom));
    $prenomUS=stripslashes($prenomUS);

    /* on traite le nom */
    $nomUS=replace_accent(trim($nom));
    $nomUS=stripslashes($nomUS);

    // calcul du login
    $forlife = strtolower($prenomUS.".".$nomUS.".".$promo);
    $forlife = str_replace(" ","-",$forlife);
    $forlife = str_replace("'","",$forlife);
    return $forlife;
}
?>
