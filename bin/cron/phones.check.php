#!/usr/bin/php5
<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

require './connect.db.inc.php';

$globals->debug = 0; // do not store backtraces
// TODO: Improve this using Phone::iterate.

function do_update_by_block($values)
{
    // Update display_tel by block
    // Because there is no mysql update syntax for multiple updates in one query
    // we use a multiple insert syntax which will fail because the key already exist
    // and then update the display_tel
    XDB::execute("INSERT INTO  profile_phones (pid, link_type, link_id, tel_id ,tel_type,
                                          search_tel, display_tel, pub, comment)
                       VALUES  " . $values . "
      ON DUPLICATE KEY UPDATE  display_tel = VALUES(display_tel)");
}

$res = XDB::query("SELECT DISTINCT  phonePrefix
                              FROM  geoloc_countries
                             WHERE  phonePrefix IS NOT NULL");
$prefixes = $res->fetchColumn();
foreach ($prefixes as $i => $prefix) {
    $res = XDB::query("SELECT  phoneFormat
                         FROM  geoloc_countries
                        WHERE  phonePrefix = {?} AND phoneFormat != '' LIMIT 1",
                      $prefix);
    if ($res->numRows() > 0) {
        $format = $res->fetchOneCell();
        //Build regexp for mysql query
        $len = strlen($format);
        $regexp = "^";
        $nbPar = 0;
        for ($i = 0; $i < $len; $i++) {
            $char = $format[$i];
            switch ($char) {
            case 'p':
                $regexp .= $prefix;
                break;
            case '#':
                if ($nbPar == 0) {
                    $regexp .= '(';
                    $nbPar++;
                }
                $regexp .= '[0-9](';
                $nbPar++;
                break;
            default:
                //Appends the char after escaping it if necessary
                $escape = array('[', ']', '{', '}', '(', ')', '*', '+', '?', '.', '^', '$', '|', '\\');
                if (in_array($char, $escape)) {
                    $regexp .= '[' . $char . ']';
                } else {
                    $regexp .= $char;
                }
            }
        }
        //allows additionnal spaces and numbers
        $regexp .= '[0-9 ]*';
        //closes parenthesis
        for ($i = 0; $i < $nbPar; $i++) {
            $regexp .= ')?';
        }
        $regexp .= '$';
        $res = XDB::iterator("SELECT pid, link_type, link_id, tel_id, tel_type, search_tel,
                                     display_tel, pub, comment
                                FROM profile_phones
                               WHERE search_tel LIKE {?} AND display_tel NOT REGEXP {?}",
                             $prefix . '%', $regexp);
        if ($res->numRows() > 0)
        {
            //To speed up the update of phone numbers, theses updates are grouped by block of 1000
            $values = '';
            $i = 0;
            while ($phone = $res->next()) {
                $phone = new Phone(array('display' => $phone['display_tel']));
                $phone->format(array('format' => $format, 'phoneprf' => $prefix));
                if ($values != '') {
                    $values .= ",\n";
                }
                $values .= "('"   . addslashes($phone['pid']) . "', '" . addslashes($phone['link_type'])
                    . "', '" . addslashes($phone['link_id'])
                    . "', '" . addslashes($phone['tel_id']) . "', '" . addslashes($phone['tel_type'])
                    . "', '" . addslashes($phone['search_tel']) . "', '" . addslashes($phone->display)
                    . "', '" . addslashes($phone['pub']) . "', '" . addslashes($phone['comment']) . "')";
                $i++;
                if ($i == 1000) {
                    do_update_by_block($values);
                    $values = '';
                    $i = 0;
                }
            }
            if ($values != '') {
                do_update_by_block($values);
            }
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
