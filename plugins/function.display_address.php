<?php
/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
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

function display_address_isIdentity($idt, $value, $test_reverse = true)
{
    $value = strtolower(replace_accent($value));
    $idt   = strtolower(replace_accent($idt));
    $idt   = preg_replace('/[^a-z]/', '', $idt);

    $value = preg_replace('/[^a-z]/', '', $value);
    if (strpos($value, $idt) !== false || strpos($idt, $value) !== false || levenshtein($value, $idt) < strlen($idt) / 3) {
        return true;
    }

    if ($test_reverse) {
        return display_address_isIdentity($idt, implode(' ', array_reverse(explode(' ', $value))), false);
    }
    return false;
}

function smarty_function_display_address($param, $smarty)
{
    $adr = $param['adr'];
    $txtad = $adr->text;
    if (!$txtad) {
        $txthtml = '';
        if ($adr->phones() && count($adr->phones())) {
            require_once 'function.display_phones.php';
            $txthtml .= smarty_function_display_phones(array('tels' => $adr->phones()), $smarty);
        } elseif (isset($param['phones']) && count($param['phones'])) {
            require_once 'function.display_phones.php';
            $txthtml .=  smarty_function_display_phones(array('tels' => $param['phones']), $smarty);
        }
        if (!isset($param['nodiv']) && $txthtml != '' && isset($param['pos'])) {
            $txthtml = '<div class="adresse" style="float: ' . $param['pos'] . '">' . $txthtml . '</div>';
        }
        return $txthtml;
    }

    $lines = explode("\n", $txtad);
    $idt   = array_shift($lines);
    $restore = true;

    if (!display_address_isIdentity($param['for'], $idt)) {
        array_unshift($lines, $idt);
        $idt = $param['for'];
        $restore = false;
    }

    $txthtml = "";
    $map = "<a href=\"http://maps.google.fr/?q="
        .   urlencode(implode(", ", $lines) . " ($idt)")
        . "\"><img src=\"images/icons/map.gif\" alt=\"Google Maps\" title=\"Carte\"/></a>";
    if ($adr->flags->hasflag('mail')) {
        $mail = '&nbsp;<img src="images/icons/email_open.gif" alt="Adresse courier" title="On peut lui envoyer du courier à cette adresse." />';
    } else {
        $mail = '';
    }
    $comment = "";
    if ($adr->comment != "")
    {
        $commentHtml = str_replace(array('&', '"'), array('&amp;', '&quot;'), $adr->comment);
        $commentJs = str_replace(array('\\', '\''), array('\\\\', '\\\''), $commentHtml);
        $comment = "<img style=\"margin-left: 5px;\" src=\"images/icons/comments.gif\""
            . " onmouseover=\"return overlib('"
            . $commentJs
            . "',WIDTH,250);\""
            . " onmouseout=\"nd();\""
            . " alt=\"Commentaire\" title=\""
            . $commentHtml
            . "\"/>";
    }
    if ($restore) {
        array_unshift($lines, $idt);
    }
    if ($param['titre'])
    {
        if ($param['titre_div'])
            $txthtml .= '<div class="titre">' . pl_entity_decode($param['titre']) . '&nbsp;' . $map . $mail . $comment . "</div>\n";
        else
            $txthtml .= '<em>' . pl_entity_decode($param['titre']) . '&nbsp;</em>' . $map . $mail . $comment . "<br />\n";
    }
    foreach ($lines as $line)
    {
        $txthtml .= "<strong>" . pl_entities($line) . "</strong><br/>\n";
    }
    if ($adr->phones() != null) {
        require_once 'function.display_phones.php';
        $txthtml .= smarty_function_display_phones(array('tels' => $adr->phones()),$smarty);
    } else if (isset($param['phones']) && count($param['phones'])) {
        require_once 'function.display_phones.php';
        $txthtml .= smarty_function_display_phones(array('tels' => $param['phones']),$smarty);
    }
    if (!$param['nodiv']) {
        $pos = $param['pos'] ? " style='float: " . $param['pos'] . "'" : '';
        $txthtml = "<div class='adresse' $pos>\n".$txthtml."</div>\n";
    }
    return $txthtml;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
