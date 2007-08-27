#!/usr/bin/php5
<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

require_once(dirname(__FILE__) . '/../classes/csvimporter.php');

// {{{ function showHelp()

function showHelp($error = null)
{
    if ($error) {
        echo 'Ligne de commande non-valide : ' . $error, "\n\n";
    }
    echo 'csv2sql.php -t table [-du] [-k keys] [-i source] [-r phpfile]', "\n\n";
    echo 'options:', "\n";
    echo ' -t table: table in which insertion is to be done', "\n";
    echo ' -d: switch to debug mode', "\n";
    echo ' -u: switch to update mode', "\n";
    echo ' -o: switch to update-only mode', "\n";
    echo ' -k keys: comma-separated list of keys', "\n";
    echo ' -i source: CSV source file (stdin if not defined or if source is \'-\'', "\n";
    echo ' -r phpfile: PHP file which define relations', "\n";
}

// }}}
// {{{ function processArgs()

function processArgs()
{
    global $sourceName, $table, $includedFile, $debug, $action, $keys;
    $opts = getopt('oui:t:r:dk:');
    if ($opts['i'] == '-' || empty($opts['i'])) {
        $sourceName = 'php://stdin';
    } else {
        $sourceName = $opts['i'];
    }

    if ($opts['k'] && !empty($opts['k'])) {
        $keys = $opts['k'];
    }

    if (isset($opts['u'])) {
        $action = CSV_UPDATE ;
    } elseif (isset($opts['o'])) {
        $action = CSV_UPDATEONLY;
    }

    if (isset($opts['d'])) {
        $debug = true;
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

global $debug, $action, $keys;
$debug           = false;
$action          = CSV_INSERT;
$keys            = 'id';

processArgs();
require_once(dirname(__FILE__) . '/../classes/xdb.php');

$source          = file_get_contents($sourceName);
$insert_relation = null;
$update_relation = null;
if (isset($includedFile)) {
    require_once($includedFile);
}

$translater = new CSVImporter($table, $keys, !$debug);
$translater->setCSV($source);
$translater->run($action, $insert_relation, $update_relation);

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
