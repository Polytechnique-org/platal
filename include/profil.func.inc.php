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


require_once('applis.func.inc.php');

function replace_ifset(&$var,$req) {
    if (Env::has($req)){
        $var = Env::v($req);
    }
}

function replace_ifset_i(&$var,$req,$i) {
    if (isset($_REQUEST[$req][$i])){
        $var[$i] = $_REQUEST[$req][$i];
    }
}

function replace_ifset_i_j(&$var,$req,$i,$j) {
    if (isset($_REQUEST[$req][$j])){
        $var[$i] = $_REQUEST[$req][$j];
    }
}

//pour rentrer qqchose dans la base
function put_in_db($string){
    return trim($string);
}

// example of use for diff_user_details : get $b from database, $a from other site
//  calculate diff $c and add $c in database (with set_user_details)
function diff_user_details(&$a, &$b, $view = 'private') { // compute $c = $a - $b
//    if (!isset($b) || !$b || !is_array($b) || count($b) == 0)
//        return $a;
//    if (!isset($a) || !$a || !is_array($a))
//        $c = array();
//    else
        $c = $a;
    foreach ($b as $val => $bvar) {
        if (isset($a[$val])) {
            if ($a[$val] == $bvar)
                unset($c[$val]);
            else {
                switch ($val) {
                    case 'adr' : if (!($c['adr'] = diff_user_addresses($a[$val], $bvar, $view))) unset($c['adr']); break;
                    case 'adr_pro' : if (!($c['adr_pro'] = diff_user_pros($a[$val], $bvar, $view))) unset($c['adr_pro']); break;
                    case 'mobile' : if (same_tel($a[$val], $bvar)) unset($c['mobile']); break;
                }
            }
        }
    }
    // don't modify mobile if you don't have the right
    if (isset($b['mobile_pub']) && !has_user_right($b['mobile_pub'], $view) && isset($c['mobile']))
        unset($c['mobile']);
    if (isset($b['freetext_pub']) && !has_user_right($b['freetext_pub'], $view) && isset($c['freetext']))
        unset($c['freetext']);
    if (!count($c))
        return false;
    return $c;
}

function same_tel(&$a, &$b) {
    $numbera = preg_replace('/[^0-9]/', '', (string) $a);
    $numberb = preg_replace('/[^0-9]/', '', (string) $b);
    return $numbera === $numberb;
}
function same_address(&$a, &$b) {
    return
        (same_field($a['adr1'],$b['adr1'])) &&
        (same_field($a['adr1'],$b['adr1'])) &&
        (same_field($a['adr1'],$b['adr1'])) &&
        (same_field($a['postcode'],$b['postcode'])) &&
        (same_field($a['city'],$b['city'])) &&
        (same_field($a['countrytxt'],$b['countrytxt'])) &&
        true;
}
function same_pro(&$a, &$b) {
    return
        (same_field($a['entreprise'],$b['entreprise'])) &&
        (same_field($a['fonction'],$b['fonction'])) &&
        true;
}
function same_field(&$a, &$b) {
    if ($a == $b) return true;
    if (is_array($a)) {
        if (!is_array($b) || count($a) != count($b)) return false;
        foreach ($a as $val => $avar)
            if (!isset($b[$val]) || !same_field($avar, $b[$val])) return false;
        return true;
    } elseif (is_string($a))
        return (strtoupper($a) == strtoupper($b));
}
function diff_user_tel(&$a, &$b) {
    $c = $a;
    if (isset($b['tel_pub']) && isset($a['tel_pub']) && has_user_right($b['tel_pub'], $a['tel_pub']))
        $c['tel_pub'] = $b['tel_pub'];
    foreach ($b as $val => $bvar) {
        if (isset($a[$val])) {
            if ($a[$val] == $bvar)
                unset($c[$val]);
        }
    }
    if (!count($c))
        return false;
    $c['telid'] = $a['telid'];
    return $c;
}

function diff_user_address($a, $b) {
    if (isset($b['pub']) && isset($a['pub']) && has_user_right($b['pub'], $a['pub']))
        $a['pub'] = $b['pub'];
    if (isset($b['tels'])) {
        $bvar = $b['tels'];

        $telids_b = array();
        foreach ($bvar as $i => $telb) $telids_b[$telb['telid']] = $i;

        if (isset($a['tels']))
            $avar = $a['tels'];
        else
            $avar = array();
        $ctels = $avar;
        foreach ($avar as $j => $tela) {
            if (isset($tela['telid'])) {
                // if b has a tel with the same telid, compute diff
                if (isset($telids_b[$tela['telid']])) {
                    if (!($ctels[$j] = diff_user_tel($tela, $varb[$telids_b[$tela['adrid']]])))
                        unset($ctels[$j]);
                    unset($telids_b[$tela['telid']]);
                }
            } else {
                // try to find a match in b
                foreach ($bvar as $i => $telb) {
                    if (same_tel($tela['tel'], $telb['tel'])) {
                        $tela['telid'] = $telb['telid'];
                        if (!($ctels[$j] = diff_user_tel($tela, $telb)))
                            unset($ctels[$j]);
                        unset($telids_b[$tela['telid']]);
                        break;
                    }
                }
            }
        }

        foreach ($telids_b as $telidb => $i)
            $ctels[] = array('telid' => $telidb, 'remove' => 1);

        if (!count($ctels)) {
            $b['tels'] = $avar;
        } else
            $a['tels'] = $ctels;
    }

    foreach ($a as $val => $avar) {
        if (!isset($b[$val]) || !same_field($avar,$b[$val])) {
            return $a;
        }
    }
    return false;
}

// $b need to use adrids
function diff_user_addresses(&$a, &$b) {
    $c = $a;
    $adrids_b = array();
    foreach ($b as $i => $adrb) $adrids_b[$adrb['adrid']] = $i;

    foreach ($a as $j => $adra) {
        if (isset($adra['adrid'])) {
            // if b has an address with the same adrid, compute diff
            if (isset($adrids_b[$adra['adrid']])) {
                if (!($c[$j] = diff_user_address($adra, $b[$adrids_b[$adra['adrid']]])))
                    unset($c[$j]);
                unset($adrids_b[$adra['adrid']]);
            }
        } else {
            // try to find a match in b
            foreach ($b as $i => $adrb) {
                if (same_address($adra, $adrb)) {
                    $adra['adrid'] = $adrb['adrid'];
                    if (!($c[$j] = diff_user_address($adra, $adrb)))
                        unset($c[$j]);
                    if ($c[$j]) $c[$j]['adrid'] = $adra['adrid'];
                    unset($adrids_b[$adra['adrid']]);
                    break;
                }
            }
        }
    }

    foreach ($adrids_b as $adridb => $i)
        $c[] = array('adrid' => $adridb, 'remove' => 1);

    if (!count($c)) return false;
    return $c;
}

function diff_user_pro($a, &$b, $view = 'private') {
    if (isset($b['pub']) && isset($a['pub']) && has_user_right($b['pub'], $a['pub']))
        $a['pub'] = $b['pub'];
    if (isset($b['adr_pub']) && !has_user_right($b['adr_pub'], $view)) {
        unset($a['adr1']);
        unset($a['adr2']);
        unset($a['adr3']);
        unset($a['postcode']);
        unset($a['city']);
        unset($a['countrytxt']);
        unset($a['region']);
    }
    if (isset($b['adr_pub']) && isset($a['adr_pub']) && has_user_right($b['adr_pub'], $a['adr_pub']))
        $a['adr_pub'] = $b['adr_pub'];
    if (isset($b['tel_pub']) && !has_user_right($b['tel_pub'], $view)) {
        unset($a['tel']);
        unset($a['fax']);
        unset($a['mobile']);
    }
    if (isset($b['tel_pub']) && isset($a['tel_pub']) && has_user_right($b['tel_pub'], $a['tel_pub']))
        $a['tel_pub'] = $b['tel_pub'];
    if (isset($b['email_pub']) && !has_user_right($b['email_pub'], $view))
        unset($a['email']);
    if (isset($b['email_pub']) && isset($a['email_pub']) && has_user_right($b['email_pub'], $a['email_pub']))
        $a['email_pub'] = $b['email_pub'];
    foreach ($a as $val => $avar) {
        if (($avar && !isset($b[$val])) || !same_field($avar,$b[$val])) {
            return $a;
        }
    }
    return false;
}

// $b need to use entrids
function diff_user_pros(&$a, &$b, $view = 'private') {
    $c = $a;
    $entrids_b = array();
    foreach ($b as $i => $prob) $entrids_b[$prob['entrid']] = $i;

    foreach ($a as $j => $proa) {
        if (isset($proa['entrid'])) {
            // if b has an address with the same adrid, compute diff
            if (isset($entrids_b[$proa['entrid']])) {
                if (!($c[$j] = diff_user_pro($proa, $b[$entrids_b[$proa['entrid']]], $view)))
                    unset($c[$j]);
                unset($entrids_b[$proa['entrid']]);
            }
        } else {
            // try to find a match in b
            foreach ($b as $i => $prob) {
                if (same_pro($proa, $prob)) {
                    $proa['entrid'] = $prob['entrid'];
                    if (!($c[$j] = diff_user_pro($proa, $prob, $view)))
                        unset($c[$j]);
                    if ($c[$j]) $c[$j]['entrid'] = $proa['entrid'];
                    unset($entrids_b[$proa['entrid']]);
                    break;
                }
            }
        }
    }

    foreach ($entrids_b as $entridb => $i)
        $c[] = array('entrid' => $entridb, 'remove' => 1);

    if (!count($c)) return false;
    return $c;
}

function format_phone_number($tel)
{
    $tel = trim($tel);
    if (substr($tel, 0, 3) === '(0)') {
        $tel = '33' . $tel;
    }
    $tel = preg_replace('/\(0\)/',  '', $tel);
    $tel = preg_replace('/[^0-9]/', '', $tel);
    if (substr($tel, 0, 2) === '00') {
        $tel = substr($tel, 2);
    } else if(substr($tel, 0, 1) === '0') {
        $tel = '33' . substr($tel, 1);
    }
    return $tel;
}

function format_display_number($tel, &$error)
{
    $error = false;
    $ret = '';
    $tel_length = strlen($tel);
    $res = XDB::query("SELECT phoneprf, format
                         FROM phone_formats
                        WHERE phoneprf = {?} OR phoneprf = {?} OR phoneprf = {?}",
                      substr($tel, 0, 1), substr($tel, 0, 2), substr($tel, 0, 3));
    if ($res->numRows() == 0) {
        $error = true;
        return '*+' . $tel;
    }
    $format = $res->fetchOneAssoc();
    if ($format['format'] == '') {
        $format['format'] = '+p';
    }
    $j = 0;
    $i = strlen($format['phoneprf']);
    $length_format = strlen($format['format']);
    while (($i < $tel_length) && ($j < $length_format)){
        if ($format['format'][$j] == '#'){
            $ret .= $tel[$i];
            $i++;
        } else if ($format['format'][$j] == 'p') {
            $ret .= $format['phoneprf'];
        } else {
            $ret .= $format['format'][$j];
        }
        $j++;
    }
    for (; $i < $tel_length - 1; $i += 2) {
        $ret .= ' ' . substr($tel, $i, 2);
    }
    //appends last alone number to the last block
    if ($i < $tel_length) {
        $ret .= substr($tel, $i);
    }
    return $ret;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
