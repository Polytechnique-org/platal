<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

// Use native function if it's available (>= PHP5.3)
if (!function_exists('quoted_printable_encode')) {
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

/** Convert ip to uint (to store it in a database)
 */
function ip_to_uint($ip)
{
    $part = explode('.', $ip);
    if (count($part) != 4) {
        return null;
    }
    $v = 0;
    $fact = 0x1000000;
    for ($i = 0 ; $i < 4 ; ++$i) {
        $v += $fact * $part[$i];
        $fact >>= 8;
    }
    return $v;
}

/** Convert uint to ip (to build a human understandable ip)
 */
function uint_to_ip($uint)
{
    return long2ip($uint);
}

/** Converts DateTime / string / timestamp to DateTime object
 */
function make_datetime($date)
{
    if ($date instanceof DateTime) {
        return $date;
    } elseif (preg_match('/^\d{14}$/', $date) || preg_match('/^\d{8}$/', $date)) {
        return new DateTime($date);
    } elseif (is_int($date) || is_numeric($date)) {
        return new DateTime("@$date");
    } else {
        try {
            $d = new DateTime($date);
            return $d;
        } catch (Exception $e) {
            return null;
        }
    }
}

/** Here to allow clean date formats instead of PHP's erroneous system...
 * Format :
 * %a: Mon...Sun
 * %A: Monday...Sunday
 * %d: day, two digits
 * %e: day, space before single digits
 * %j: day of year
 * %u: day of week (1 for monday, 7 for sunday)
 * %w: day of week (0 for sunday, 6 for saturday)
 *
 * //%U: week number (first week is that with the first sunday)
 * //%V: week number (ISO 8601-1988: first week is that with at least 4 week days)
 * %W: week number (first week is that with the first monday)
 *
 * %b: Jan...Dec
 * %B: January...December
 * %h: = %b
 * %m: month, two digits
 *
 * %C: century, two digits
 * %g: year, two digits (ISO 8601-1988)
 * %G: %g with four digits
 * %y: year, two digits
 * %Y: year, four digits
 *
 * %H: hour, two digits, 24h format
 * %h: hour, two digits, 12h format
 * %l: hour, two digits, space before single, 12h format
 * %M: minute, two digits
 * %p: AM/PM
 * %P: am/pm
 * %r: %I:%M:%S %p
 * %R: %H:%M
 * %S: second, two digits
 * %T: %H:%M:%S
 * %z: timezone (offset)
 * %Z: timezone (abbrev)
 *
 * %x: %e %B %Y
 * %X: %T
 * %s: unix timestamp
 * %%: %
 */
function format_datetime($date, $format)
{
    $format = str_replace(array('%X', '%x', '%R', '%r', '%T', '%%'),
                     array('%T', '%e %B %Y', '%H:%M', '%I:%M:%S %p', '%H:%M:%S', '%%'),
                     $format);

    $date = make_datetime($date);
    $yy = (int) $date->format('Y');
//    if ($yy > 1901 && $yy < 2038) {
//        return strftime($format, $date->format('U'));
//    } else {
        $w = (int) $date->format('w');
        $u = $w;
        if ($u == 0) {
            $u = 7;
        }
        $weekday = new DateTime('2010-05-' . (10 + $u));
        $aa = strftime('%A', $weekday->format('U'));
        $a = strftime('%a', $weekday->format('U'));

        $m = $date->format('m');
        $monthday = new DateTime('2010-' . $m . '-01');
        $bb = strftime('%B', $monthday->format('U'));
        $b = strftime('%b', $monthday->format('U'));
        $y = $date->format('y');

        $j  = $date->format('z'); // Day of year
        $d  = $date->format('d'); // Day of month, 2 digits
        $e  = $date->format('j'); // Day of month, leanding space
        if (strlen($e) == 1) {
            $e = ' ' . $e;
        }

        $yy = "$yy";
        $cc = substr($yy, 0, 2); // Century
        $ww = $date->format('W'); // Week number

        $hh = $date->format('H'); // Hour, 24h
        $h  = $date->format('h'); // Hour, 12h
        $l  = $date->format('g'); // Hour, 12h with leading space
        if (strlen($l) == 1) {
            $l = ' ' . $l;
        }

        $mm = $date->format('i'); // Minutes
        $p  = $date->format('A'); // AM/PM
        $pp = $date->format('a'); // am/pm
        $ss = $date->format('s'); // Seconds

        $s  = $date->format('U'); // Timestamp
        $zz = $date->format('T'); // Timezone abbrev
        $z  = $date->format('Z'); // Timezone offset

        $txt = str_replace(
            array('%a', '%A', '%d', '%e', '%j', '%u', '%w',
                  '%W', '%b', '%B', '%h', '%m', '%C', '%y', '%Y',
                  '%H', '%h', '%l', '%M', '%p', '%P', '%S', '%z', '%Z',
                  '%%'),
            array($a, $aa, $d, $e, $j, $u, $w,
                  $ww, $b, $bb, $b, $m, $cc, $y, $yy,
                  $hh, $h, $l, $mm, $p, $pp, $ss, $z, $zz,
                  '%'),
            $format);

        return $txt;
//    }
}

/** Get the first n characters of the string
 */
function left($string, $count)
{
    return substr($string, 0, $count);
}

/** Get the last n characters of the string
 */
function right($string, $count)
{
    return substr($string, -$count);
}

/** Check if a string is a prefix for another one.
 */
function starts_with($string, $prefix, $caseSensitive = true)
{
    $prefixLen = strlen($prefix);
    if (strlen($string) < $prefixLen) {
        return false;
    }
    $part = left($string, $prefixLen);
    if ($caseSensitive) {
        return strcmp($prefix, $part) === 0;
    } else {
        return strcasecmp($prefix, $part) === 0;
    }
}

/** Check if a string is a suffix for another one.
 */
function ends_with($string, $suffix, $caseSensitive = true)
{
    $suffixLen = strlen($suffix);
    if (strlen($string) < $suffixLen) {
        return false;
    }
    $part = right($string, $suffixLen);
    if ($caseSensitive) {
        return strcmp($suffix, $part) === 0;
    } else {
        return strcasecmp($suffix, $part) === 0;
    }
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
