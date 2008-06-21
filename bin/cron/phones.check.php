#!/usr/bin/php5
<?php

require('./connect.db.inc.php');

//next two are required to include 'profil.func.inc.php'
require_once('xorg.inc.php');
$page = new XorgPage(null);

require_once('profil.func.inc.php');

$globals->debug = 0; //do not store backtraces


function do_update_by_block($values)
{
    // Update display_tel by block
    // Because there is no mysql update syntax for multiple updates in one query
    // we use a multiple insert syntax which will fail because the key already exist
    // and then update the display_tel
    XDB::execute("INSERT INTO  profile_phones (uid, link_type, link_id, tel_id ,tel_type,
                                          search_tel, display_tel, pub, comment)
                       VALUES  " . $values . "
      ON DUPLICATE KEY UPDATE  display_tel = VALUES(display_tel)");
}

$res = XDB::query("SELECT DISTINCT g.phoneprf FROM geoloc_pays AS g WHERE g.phoneprf IS NOT NULL");
$prefixes = $res->fetchColumn();
foreach ($prefixes as $i => $prefix) {
    $res = XDB::query("SELECT g.phoneformat FROM geoloc_pays AS g
                        WHERE g.phoneprf = {?} AND g.phoneformat != '' LIMIT 1",
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
        $res = XDB::iterator("SELECT uid, link_type, link_id, tel_id, tel_type, search_tel,
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
                $disp = format_display_number($phone['search_tel'], $error, array('format' => $format, 'phoneprf' => $prefix));
                if ($values != '') {
                    $values .= ",\n";
                }
                $values .= "('"   . addslashes($phone['uid']) . "', '" . addslashes($phone['link_type'])
                    . "', '" . addslashes($phone['link_id'])
                    . "', '" . addslashes($phone['tel_id']) . "', '" . addslashes($phone['tel_type'])
                    . "', '" . addslashes($phone['search_tel']) . "', '" . addslashes($disp)
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
