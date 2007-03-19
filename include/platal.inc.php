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

$TIME_BEGIN = microtime(true);

define('AUTH_PUBLIC', 0);
define('AUTH_COOKIE', 1);
define('AUTH_MDP',    2);

define('PERMS_EXT',   'ext');
define('PERMS_USER',  'user');
define('PERMS_ADMIN', 'admin');

define('SKINNED', 0);
define('SIMPLE',  1);
define('NO_SKIN', 2);

define('NO_AUTH', 0);
define('DO_AUTH', 1);

function __autoload($cls)
{
    $cls  = strtolower($cls);
    $path = dirname(dirname(__FILE__));
    if (!@include "$path/classes/$cls.php") {
        if (substr($cls, -1, 3) == 'req') {
            @include 'validations.inc.php';
            return;
        }
        @include "$cls.inc.php";
    }
}
__autoload('Env');

function pl_error_handler($errno, $errstr, $errfile, $errline)
{
    if (!error_reporting())
        return;

    $errortype = array (
        E_ERROR           => "Error",
        E_WARNING         => "Warning",
        E_PARSE           => "Parsing Error",
        E_NOTICE          => "Notice",
        E_CORE_ERROR      => "Core Error",
        E_CORE_WARNING    => "Core Warning",
        E_COMPILE_ERROR   => "Compile Error",
        E_COMPILE_WARNING => "Compile Warning",
        E_USER_ERROR      => "User Error",
        E_USER_WARNING    => "User Warning",
        E_USER_NOTICE     => "User Notice",
        E_STRICT          => "Runtime Notice"
    );

    global $globals;
    if (isset($globals) && !$globals->debug) {
        if (strpos($errortype[$errno], 'Notice') !== false) {
            return;
        }
    }

    $errstr = utf8_encode(htmlentities($errstr));
    $GLOBALS['pl_errors'][] =
        "<div class='phperror'>".
        "<strong>{$errortype[$errno]}</strong> <em>$errstr</em><br />".
        "<tt>$errfile : $errline</tt>".
        "</div>";
}

function pl_clear_errors()
{
    unset($GLOBALS['pl_errors']);
}

function pl_dump_env()
{
    echo "<div class='phperror'><pre>";
    echo "\nSESSION: "; var_export($_SESSION);
    echo "\nPOST:    "; var_export($_POST);
    echo "\nGET:     "; var_export($_GET);
    echo "\nCOOKIE:  "; var_export($_COOKIE);
    echo "</pre></div>";
}

function pl_print_errors()
{
    print join("\n", $GLOBALS['pl_errors']);
}

set_error_handler('pl_error_handler', E_ALL | E_STRICT);
register_shutdown_function('pl_print_errors');
// register_shutdown_function('pl_dump_env');

function pl_url($path, $query = null, $fragment = null)
{
    global $platal;

    $base = $platal->ns . $path . ($query ? '?'.$query : '');
    return $fragment ? $base.'#'.$fragment : $base;
}

function pl_self($n = null) {
    global $platal;
    return $platal->pl_self($n);
}

function http_redirect($fullurl)
{
    if (count($_SESSION)) {
        session_write_close();
    }
    header('Location: '.$fullurl);
    exit;
}

function pl_redirect($path, $query = null, $fragment = null)
{
    global $globals;
    http_redirect($globals->baseurl . '/' . pl_url($path, $query, $fragment));
}

function pl_entities($text, $mode = ENT_COMPAT)
{
    return htmlentities($text, $mode, 'UTF-8');
}

function pl_entity_decode($text, $mode = ENT_COMPAT)
{
    return html_entity_decode($text, $mode, 'UTF-8');
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
