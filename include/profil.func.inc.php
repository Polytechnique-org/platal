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

function format_display_number($tel, &$error, $format = array('format'=>'','phoneprf'=>''))
{
    $error = false;
    $ret = '';
    $tel_length = strlen($tel);
    if((!isset($format['phoneprf'])) || ($format['phoneprf'] == '')) {
        $res = XDB::query("SELECT phonePrefix AS phoneprf, phoneFormat AS format
                             FROM geoloc_countries
                            WHERE phonePrefix = {?} OR phonePrefix = {?} OR phonePrefix = {?}
                            LIMIT 1",
                          substr($tel, 0, 1), substr($tel, 0, 2), substr($tel, 0, 3));
        if ($res->numRows() == 0) {
            $error = true;
            return '+' . $tel;
        }
        $format = $res->fetchOneAssoc();
    }
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
