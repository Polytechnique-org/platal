#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';

function is_utf8($s)
{
    return @iconv('utf-8', 'utf-8', $s) == $s;
}

$tables = array ('city', 'city_in_maps', 'maps', 'pays', 'region');
XDB::execute("SET NAMES 'latin1'");
foreach ($tables as $table) {
    $res = XDB::query("SELECT * FROM geoloc_$table");
    if (!$res) {
        echo "$table\n";
        continue;
    }
    $all = $res->fetchAllAssoc();
    foreach ($all as &$array) {
        $from = array();
        $to   = array();
        foreach ($array as $key=>$value) {
            $from[] = $key . '="' . mysql_real_escape_string($value) . '"';
            $valued = utf8_decode($value);
            if (is_utf8($value) && $valued != $value) {
                $to[] = $key . '="' . mysql_real_escape_string($valued) .'"';
            }
        }
        if (!empty($to)) {
            $to = implode(', ', $to);
            $from = implode(' AND ', $from);
            $sql = "UPDATE geoloc_$table SET $to WHERE $from";
            if (!XDB::execute($sql)) {
                echo "Echec : $sql\n";
            }
        }
    }
}

?>
