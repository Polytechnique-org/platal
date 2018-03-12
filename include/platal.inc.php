<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

require_once dirname(__FILE__) . '/version.inc.php';
require_once dirname(__FILE__) . '/misc.inc.php';

// Common basic permission flags.
define('PERMS_USER',  'user');
define('PERMS_ADMIN', 'admin');

// Page style options, used when rendering pages. Options are exclusive.
define('SKINNED', 0);  // Page is rendered with the normal skin.
define('SIMPLE',  1);  // Page is rendered with a light skin (no leftnav).
define('NO_SKIN', 2);  // Page content is passed as-is (use for csv, xml, ...).

// Hook options bitmasks. Authentication options are mutually exclusive, but
// others (NO_HTTPS at the moment) are not.
//
// With PlStdHook, NO_AUTH indicates that no session will be started, and that
// the actual handler is responsible for doing authentication; DO_AUTH forces
// the engine to try to authenticate the user, including redirecting to the
// login page. Note that DO_AUTH is ignored if AUTH_PUBLIC is requested.
//
// Options NO_AUTH and DO_AUTH are ignored with PlTokenHook.
define('NO_AUTH', 0);
define('DO_AUTH', 1);
define('NO_HTTPS', 2);

function pl_autoload($cls, array $pathes = array())
{
    $cls  = strtolower($cls);
    if (starts_with($cls, 'xdb')) {
        $cls = 'xdb';
    } else if (starts_with($cls, 'pldbtable')) {
        $cls = 'pldbtableentry';
    }
    $corebasepath = dirname(dirname(__FILE__));
    $basepath = dirname($corebasepath);
    $corebasename = basename($corebasepath);

    array_unshift($pathes, $corebasename . '/classes', 'classes');
    foreach ($pathes as $path) {
        if (file_exists("$basepath/$path/$cls.php")) {
            if (include_once "$basepath/$path/$cls.php") {
                return true;
            }
        }
    }
    return false;
}
pl_autoload('Env');
pl_autoload('PlBacktrace');

function pl_core_include($file)
{
    return dirname(__FILE__) . '/' . $file;
}

function pl_error_handler($errno, $errstr, $errfile, $errline)
{

    static $errortype;
    if (!error_reporting())
        return;

    if (!isset($errortype)) {
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
            E_STRICT          => "Runtime Notice",
            E_RECOVERABLE_ERROR => "Recoverable Error",
            E_DEPRECATED      => "Deprecation Notice"
        );
    }

    global $globals;
    if (isset($globals) && !$globals->debug) {
        if ($errno == E_NOTICE || $errno == E_USER_NOTICE || $errno == E_STRICT || $errno == E_DEPRECATED) {
            return;
        }
    }
    $type = isset($errortype[$errno]) ? $errortype[$errno] : $errno;
    $error = strpos($type, 'Warning') !== false || strpos($type, 'Error') !==false;

    if (!isset(PlBacktrace::$bt['PHP Errors'])) {
        new PlBacktrace('PHP Errors');
    }
    PlBacktrace::$bt['PHP Errors']->newEvent("$type: $errstr",
                                             0, $error ? $errstr : null,
                                             array(array('file' => $errfile,
                                                         'line' => $errline)));
}

function pl_dump_env()
{
    echo "<div class='phperror'><pre>";
    echo "\nSESSION: " . session_id(); var_dump($_SESSION);
    echo "\nPOST:    "; var_dump($_POST);
    echo "\nGET:     "; var_dump($_GET);
    echo "\nCOOKIE:  "; var_dump($_COOKIE);
    echo "</pre></div>";
}

function pl_print_errors($html = false)
{
    if (!isset(PlBacktrace::$bt['PHP Errors'])) {
        return;
    }
    foreach (PlBacktrace::$bt['PHP Errors']->traces as $trace) {
        if ($html) {
            echo "<pre>";
        }
        print "{$trace['action']}\n";
        print "  {$trace['data'][0]['file']}: {$trace['data'][0]['line']}\n";
        if ($html) {
            echo "</pre>";
        }
    }
}

function pl_assert_cb($file, $line, $message)
{
    Platal::assert(false, "Assertion failed at $file:$line with message: $message");
}

set_error_handler('pl_error_handler', E_ALL | E_STRICT);
assert_options(ASSERT_CALLBACK, 'pl_assert_cb');
assert_options(ASSERT_WARNING, false);
if (php_sapi_name() == 'cli') {
    register_shutdown_function('pl_print_errors');
}
//register_shutdown_function('pl_dump_env');

/** Check if the string is utf8
 */
function is_utf8($s)
{
    return @iconv('utf-8', 'utf-8', $s) == $s;
}

/** vérifie si une adresse email est bien formatée  * ATTENTION, cette fonction ne doit pas être appelée sur une chaîne ayant subit un addslashes (car elle accepte le "'" qui it alors un "\'"
 * @param $email l'adresse email a verifier
 * @return BOOL  */
function isvalid_email($email)
{
    // la rfc2822 authorise les caractères "a-z", "0-9", "!", "#", "$", "%", "&", "'", "*", "+", "-", "/", "=", "?", "^",  `", "{", "|", "}", "~" aussi bien dans la partie locale que dans le domaine.
    // Pour la partie locale, on réduit cet ensemble car il n'est pas utilisé.
    // Pour le domaine, le système DNS limite à [a-z0-9.-], on y ajoute le "_" car il est parfois utilisé.
    return preg_match("/^[a-z0-9_.'+-]+@[a-z0-9._-]+\.[a-z]{2,63}$/i", $email);
}

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
    Platal::session()->close();
    header('Location: '. $fullurl);
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

function pl_flatten_aux(array &$dest, array $src)
{
    foreach ($src as $val) {
        if (is_array($val)) {
            pl_flatten_aux($dest, $val);
        } else {
            $dest[] = $val;
        }
    }
}

function pl_flatten(array $array)
{
    $res = array();
    pl_flatten_aux($res, $array);
    return $res;
}

/**
 * Returns the path of a static content, including, when appropriate, the
 * version number. This is used to avoid cross-version cache issues, by ensuiring
 * that all static resources are served on a unique path.
 */
function pl_static_content_path($path, $filename)
{
    global $globals;
    if (isset($globals) && isset($globals->version)) {
        return $path . $globals->version . '/' . $filename;
    } else {
        return $path . $filename;
    }
}

/**
 * Adds content type headers; by default the encoding used is utf-8.
 */
function pl_content_headers($content_type, $encoding = 'utf-8')
{
    if (is_null($encoding)) {
        header("Content-Type: $content_type");
    } else {
        header("Content-Type: $content_type; charset=$encoding");
    }
}

/**
 * Adds content type and caching headers for content generated by plat/al. The
 * cache duration defaults to the global static_cache_duration. No encoding is
 * applied by default.
 */
function pl_cached_content_headers($content_type, $encoding = null, $cache_duration = -1, $filename = null)
{
    global $globals;
    $cache_duration = ($cache_duration < 0 ? $globals->static_cache_duration : $cache_duration);

    header("Cache-Control: max-age=$cache_duration");
    header("Expires: " . gmdate('D, d M Y H:i:s', time() + $cache_duration) . " GMT");
    header("Pragma: ");
    pl_content_headers($content_type, $encoding);
    if (!is_null($filename)) {
         header('Content-Disposition: attachment; filename=' . $filename);
    }
}

/**
 * Same as above, but applying an expiration time suitable for cacheable dynamic
 * content (eg. photos, logos, ...).
 */
function pl_cached_dynamic_content_headers($content_type, $encoding = null)
{
    global $globals;
    pl_cached_content_headers($content_type, $encoding, $globals->dynamic_cache_duration);
}

function pl_var_dump()
{
    echo '<pre>';
    $array = func_get_args();
    call_user_func_array('var_dump', $array);
    echo '</pre>';
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
