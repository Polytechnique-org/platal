<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

function quoted_printable_encode($input, $line_max = 76)
{
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
function isvalid_email_redirection($email)
{
    return isvalid_email($email) &&
        !preg_match("/@(polytechnique\.(org|edu)|melix\.(org|net)|m4x\.org)$/", $email);
}

/** genere une chaine aleatoire de 22 caracteres ou moins
 * @param $len longueur souhaitée, 22 par défaut
 * @return la chaine aleatoire qui contient les caractères [A-Za-z0-9+/]
 */
function rand_token($len = 22)
{
    $len = max(2, $len);
    $len = min(50, $len);
    $fp = fopen('/dev/urandom', 'r');
    // $len * 2 is certainly an overkill,
    // but HEY, reading 40 bytes from /dev/urandom is not that slow !
    $token = fread($fp, $len * 2);
    fclose($fp);
    $token = base64_encode($token);
    $token = preg_replace("![Il10O+/]!", "", $token);
    $token = substr($token,0,$len);
    return $token;
}

/** genere une chaine aleatoire convenable pour une url
 * @param $len longueur souhaitée, 22 par défaut
 * @return la chaine aleatoire
 */
function rand_url_id($len = 22)
{
    return rand_token($len);
}


/** genere une chaine aleatoire convenable pour un mot de passe
 * @return la chaine aleatoire
 */
function rand_pass()
{
    return rand_token(8);
}

/** Remove accent from a string and replace them by the nearest letter
 */
global $lc_convert, $uc_convert;
$lc_convert = array('é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
    'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'å' => 'a', 'ã' => 'a',
    'ï' => 'i', 'î' => 'i', 'ì' => 'i', 'í' => 'i',
    'ô' => 'o', 'ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'õ' => 'o', 'ø' => 'o',
    'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
    'ç' => 'c', 'ñ' => 'n');
$uc_convert = array('É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E',
    'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A', 'Å' => 'A', 'Ã' => 'A',
    'Ï' => 'I', 'Î' => 'I', 'Ì' => 'I', 'Í' => 'I',
    'Ô' => 'O', 'Ö' => 'O', 'Ò' => 'O', 'Ó' => 'O', 'Õ' => 'O', 'Ø' => 'O',
    'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
    'Ç' => 'C', 'Ñ' => 'N');

function replace_accent($string)
{
    global $lc_convert, $uc_convert;
    $string = strtr($string, $lc_convert);
    return strtr($string, $uc_convert);
}

/** creates a username from a first and last name
 *
 * @param $prenom the firstname
 * @param $nom the last name
 *
 * return STRING the corresponding username
 */
function make_username($prenom,$nom)
{
    /* on traite le prenom */
    $prenomUS=replace_accent(trim($prenom));
    $prenomUS=stripslashes($prenomUS);

    /* on traite le nom */
    $nomUS=replace_accent(trim($nom));
    $nomUS=stripslashes($nomUS);

    // calcul du login
    $username = strtolower($prenomUS.".".$nomUS);
    $username = str_replace(" ","-",$username);
    $username = str_replace("'","",$username);
    return $username;
}

/* Un soundex en français posté par Frédéric Bouchery
   Voici une adaptation en PHP de la fonction soundex2 francisée de Frédéric BROUARD (http://sqlpro.developpez.com/Soundex/).
   C'est une bonne démonstration de la force des expressions régulières compatible Perl.
trouvé sur http://expreg.com/voirsource.php?id=40&type=Chaines%20de%20caract%E8res */
function soundex_fr($sIn)
{
    static $convVIn, $convVOut, $convGuIn, $convGuOut, $accents;
    if (!isset($convGuIn)) {
        global $uc_convert, $lc_convert;
        $convGuIn  = array( 'GUI', 'GUE', 'GA', 'GO', 'GU', 'SCI', 'SCE', 'SC', 'CA', 'CO',
                            'CU', 'QU', 'Q', 'CC', 'CK', 'G', 'ST', 'PH');
        $convGuOut = array( 'KI', 'KE', 'KA', 'KO', 'K', 'SI', 'SE', 'SK', 'KA', 'KO',
                            'KU', 'K', 'K', 'K', 'K', 'J', 'T', 'F');
        $convVIn   = array( '/E?(AU)/', '/([EA])?[UI]([NM])([^EAIOUY]|$)/', '/[AE]O?[NM]([^AEIOUY]|$)/',
            '/[EA][IY]([NM]?[^NM]|$)/', '/(^|[^OEUIA])(OEU|OE|EU)([^OEUIA]|$)/', '/OI/',
            '/(ILLE?|I)/', '/O(U|W)/', '/O[NM]($|[^EAOUIY])/', '/(SC|S|C)H/',
            '/([^AEIOUY1])[^AEIOUYLKTPNR]([UAO])([^AEIOUY])/', '/([^AEIOUY]|^)([AUO])[^AEIOUYLKTP]([^AEIOUY1])/', '/^KN/',
            '/^PF/', '/C([^AEIOUY]|$)/',
            '/C/', '/Z$/', '/(?<!^)Z+/', '/ER$/', '/H/', '/W/');
        $convVOut  = array( 'O', '1\3', 'A\1',
            'E\1', '\1E\3', 'O',
            'Y', 'U', 'O\1', '9',
            '\1\2\3', '\1\2\3', 'N',
            'F', 'K\1',
            'S', 'SE', 'S', 'E', '', 'V');
        $accents = $uc_convert + $lc_convert;
        $accents['Ç'] = 'S';
        $accents['¿'] = 'E';
    }
    // Si il n'y a pas de mot, on sort immédiatement
    if ( $sIn === '' ) return '    ';
    // On supprime les accents
    $sIn = strtr( $sIn, $accents);
    // On met tout en minuscule
    $sIn = strtoupper( $sIn );
    // On supprime tout ce qui n'est pas une lettre
    $sIn = preg_replace( '`[^A-Z]`', '', $sIn );
    // Si la chaîne ne fait qu'un seul caractère, on sort avec.
    if ( strlen( $sIn ) === 1 ) return $sIn . '   ';
    // on remplace les consonnances primaires
    $sIn = str_replace( $convGuIn, $convGuOut, $sIn );
    // on supprime les lettres répétitives
    $sIn = preg_replace( '`(.)\1`', '$1', $sIn );
    // on réinterprète les voyelles
    $sIn = preg_replace( $convVIn, $convVOut, $sIn);
    // on supprime les terminaisons T, D, S, X (et le L qui précède si existe)
    $sIn = preg_replace( '`L?[TDX]S?$`', '', $sIn );
    // on supprime les E, A et Y qui ne sont pas en première position
    $sIn = preg_replace( '`(?!^)Y([^AEOU]|$)`', '\1', $sIn);
    $sIn = preg_replace( '`(?!^)[EA]`', '', $sIn);
    return substr( $sIn . '    ', 0, 4);
}

/** met les majuscules au debut de chaque atome du prénom
 * @param $prenom le prénom à formater
 * return STRING le prénom avec les majuscules
 */
function make_firstname_case($prenom)
{
    $prenom = strtolower($prenom);
    $pieces = explode('-',$prenom);

    foreach ($pieces as $piece) {
        $subpieces = explode("'",$piece);
        $usubpieces="";
        foreach ($subpieces as $subpiece)
            $usubpieces[] = ucwords($subpiece);
        $upieces[] = implode("'",$usubpieces);
    }
    return implode('-',$upieces);
}


function make_forlife($prenom, $nom, $promo)
{
    $prenomUS = replace_accent(trim($prenom));
    $nomUS    = replace_accent(trim($nom));

    $forlife = strtolower($prenomUS.".".$nomUS.".".$promo);
    $forlife = str_replace(" ","-",$forlife);
    $forlife = str_replace("'","",$forlife);
    return $forlife;
}

/** Convert ip to uint (to store it in a database)
 */
function ip_to_uint($ip)
{
    return ip2long($ip);
}

/** Convert uint to ip (to build a human understandable ip)
 */
function uint_to_ip($uint)
{
    return long2ip($uint);
}


/******************************************************************************
 * Security functions
 *****************************************************************************/

function check_ip($level)
{
    if (empty($_SERVER['REMOTE_ADDR'])) {
        return false;
    }
    if (empty($_SESSION['check_ip'])) {
        $ips = array();
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        }
        $ips[] = $_SERVER['REMOTE_ADDR'];
        foreach ($ips as &$ip) {
            $ip = "ip = " . ip_to_uint($ip);
        }
        $res = XDB::query('SELECT  state
                             FROM  ip_watch
                            WHERE  ' . implode(' OR ', $ips) . '
                         ORDER BY  state DESC');
        if ($res->numRows()) {
            $_SESSION['check_ip'] = $res->fetchOneCell();
        } else {
            $_SESSION['check_ip'] = 'safe';
        }
    }
    $test = array();
    switch ($level) {
      case 'unsafe': $test[] = 'unsafe';
      case 'dangerous': $test[] = 'dangerous';
      case 'ban': $test[] = 'ban'; break;
      default: return false;
    }
    return in_array($_SESSION['check_ip'], $test);
}

function check_email($email, $message)
{
    $res = XDB::query("SELECT state, description
        FROM emails_watch
        WHERE state != 'safe' AND email = {?}", $email);
    if ($res->numRows()) {
        send_warning_mail($message);
        return true;
    }
    return false;
}

function check_account()
{
    return S::v('watch_account');
}

function check_redirect($red = null)
{
    require_once 'emails.inc.php';
    if (is_null($red)) {
        $red = new Redirect(S::v('uid'));
    }
    $_SESSION['no_redirect'] = !$red->other_active('');
    $_SESSION['mx_failures'] = $red->get_broken_mx();
}

function send_warning_mail($title)
{
    global $globals;
    $mailer = new PlMailer();
    $mailer->setFrom("webmaster@" . $globals->mail->domain);
    $mailer->addTo($globals->core->admin_email);
    $mailer->setSubject("[Plat/al Security Alert] $title");
    $mailer->setTxtBody("Identifiants de session :\n" . var_export($_SESSION, true) . "\n\n"
        ."Identifiants de connexion :\n" . var_export($_SERVER, true));
    $mailer->send();
}

function kill_sessions()
{
    assert(S::has_perms());
    shell_exec('sudo -u root ' . dirname(dirname(__FILE__)) . '/bin/kill_sessions.sh');
}


/******************************************************************************
 * Dynamic configuration update/edition stuff
 *****************************************************************************/

function update_NbIns()
{
    global $globals;
    $res = XDB::query("SELECT  COUNT(*)
                         FROM  auth_user_md5
                        WHERE  perms IN ('admin','user') AND deces=0");
    $cnt = $res->fetchOneCell();
    $globals->change_dynamic_config(array('NbIns' => $cnt));
}

function update_NbValid()
{
    global $globals;
    $res = XDB::query("SELECT  COUNT(*)
                         FROM  requests");
    $globals->change_dynamic_config(array('NbValid' => $res->fetchOneCell()));
}

function update_NbNotifs()
{
    require_once 'notifs.inc.php';
    $n = select_notifs(false, S::i('uid'), S::v('watch_last'), false);
    $_SESSION['notifs'] = $n->numRows();
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
