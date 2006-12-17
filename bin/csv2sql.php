#!/usr/bin/php5
<?php

// {{{ function showHelp()

function showHelp($error = null) {
    if ($error) {
        echo 'Ligne de commande non-valide : ' . $error, "\n\n";
    }
    echo 'csv2sql.php -t table [-i source] [-r phpfile]', "\n\n";
    echo 'options:', "\n";
    echo ' -t table: table in which insertion is to be done', "\n";
    echo ' -i source: CSV source file (stdin if not defined or if source is \'-\'', "\n";
    echo ' -r phpfile: PHP file which define relations', "\n";
}

// }}}
// {{{ function processArgs()

function processArgs()
{
    global $sourceName, $table, $includedFile;
    $opts = getopt('i:t:r:d:');
    if ($opts['i'] == '-' || empty($opts['i'])) {
        $sourceName = 'php://stdin';
    } else {
        $sourceName = $opts['i'];
    }

    if ($opts['r'] && !empty($opts['r'])) {
        $includedFile = $opts['r'];
    }

    if (!$opts['t'] || empty($opts['t'])) {
        showHelp('Table non définie');
        exit;
    }
    $table = $opts['t'];
}

// }}}

processArgs();
require_once(dirname(__FILE__) . '/../classes/csvimporter.php');
require_once(dirname(__FILE__) . '/../classes/xdb.php');

$source    = file_get_contents($sourceName);
$insert_relation = null;
$update_relation = null;
$debug           = false;
$action          = CSV_INSERT;
if (isset($includedFile)) {
    require_once($includedFile);
}

$translater = new CSVImporter($table, $key, !$debug);
$translater->setCSV($source);
$translater->run($action, $insert_relation, $update_relation);

?>
