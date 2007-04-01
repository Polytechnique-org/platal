#!/usr/bin/php5
<?php

global $globals;
require_once 'connect.db.inc.php';
$globals->dbcharset = 'latin1';

$tables = array ('city' => array('id', 'alias'),
                 'city_in_maps' => array('city_id', 'map_id', 'infos'),
                 'maps' => array('map_id'),
                 'pays' => array('a2'),
                 'region' => array('a2', 'region'));
foreach ($tables as $table => $keys) {
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
            if (in_array($key, $keys)) {
                $from[] = $key . '=' . XDB::escape($value);
            }
            $valued = utf8_decode($value);
            if (is_utf8($value) && $valued != $value) {
                $to[] = $key . '=' . XDB::escape($valued);
            }
        }
        if (!empty($to)) {
            $to = implode(', ', $to);
            $from = implode(' AND ', $from);
            $sql = "UPDATE geoloc_$table SET $to WHERE $from";
            if (!XDB::execute($sql)) {
                echo "Echec : $sql\n";
            } elseif (XDB::affectedRows() == 0) {
                echo "$sql\n";
            }
        }
    }
}

?>
