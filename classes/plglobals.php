<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
/** Debug levels:
 * DEBUG_BT     = show the backtraces (SQL/XMLRPC/...)
 * DEBUG_VALID  = run html validation
 * DEBUG_SMARTY = don't hide smarty errors/warnings/notices
 */
define('DEBUG_BT', 1);
define('DEBUG_VALID', 2);
define('DEBUG_SMARTY', 4);

/** PlGlobals provides functions to read a set of configuration files and gives
 * access to this configurations.
 *
 * The configuration files ares ini files with sections:
 * <pre>
 * [SectionName]
 * fieldname = value
 * </pre>
 *
 * With this configuration file, you'll be able to access 'value' via
 * $globals->sectionname->fieldname. Let say 'sectionname' is a namespace
 *
 *
 * You should derivate this class into a local Globals class. In this class
 * you can specify configuration variables that belongs to the 'global' namespace
 * (accessible via $global->fieldname). To do so, just define the fieldname
 * in your class and set its value in the [Core] section of you ini file.
 */
class PlGlobals
{
    public $coreVersion = '0.9.17';

    /** Debug level.
     * This is a combination of the DEBUG_ flags. As soon as at least
     * one flag is set, the debug mode is activated, this means:
     *   - debug panel on the top of the pages
     *   - don't hide php notices
     *   - recompile templates when they have been changed
     */
    public $debug   = 0;

    /** Access mode.
     */
    public $mode    = 'rw';    // 'rw' => read/write,
                               // 'r'  => read/only
                               // ''   => site down

    /** BaseURL of the site.
     * This is read from the HTTP headers if available but you MUST give a
     * default value for this field in you configuration file (because, this
     * can be used in CLI scripts that has no access no HTTP headers...)
     *
     * [Core]
     * baseurl = "https//www.mysite.org/"
     */
    public $baseurl;

    /** In case your base url is https-based, this provied an HTTP-based value
     * for the URL.
     */
    public $baseurl_http;

    /** paths */
    public $spoolroot;

    /** Localization configuration.
     */
    public $locale;
    public $timezone;

    /** You must give a list of file to load.
     * The filenames given are relatives to the config path of your plat/al installation.
     */
    public function __construct(array $files)
    {
        $this->spoolroot = dirname(dirname(dirname(__FILE__)));

        $this->readConfig($files);
        if (isset($_SERVER) && isset($_SERVER['SERVER_NAME'])) {
            $base = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
            $this->baseurl      = @trim($base    .$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']), '/');
            $this->baseurl_http = @trim('http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']), '/');
        }

        $this->setLocale();
    }

    /** Initialiase dynamic data in the object.
     * This is te place to read data from the database if needed.
     */
    public function init()
    {
    }

    private function readIniFile($filename)
    {
        $array = parse_ini_file($filename, true);
        if (!is_array($array)) {
            return;
        }
        foreach ($array as $cat => $conf) {
            $c = strtolower($cat);
            foreach ($conf as $k => $v) {
                if ($c == 'core' && property_exists($this, $k)) {
                    $this->$k=$v;
                } else {
                    if (!isset($this->$c)) {
                        $this->$c = new stdClass;
                    }
                    $this->$c->$k = $v;
                }
            }
        }
    }

    private function readConfig(array $files)
    {
        foreach ($files as $file) {
            $this->readIniFile($this->spoolroot . '/configs/' . $file);
        }
        if (file_exists($this->spoolroot.'/spool/conf/platal.dynamic.conf')) {
            $this->readIniFile($this->spoolroot.'/spool/conf/platal.dynamic.conf');
        }
    }

    /** Writes an ini file separated in categories
     * @param filename the name of the file to write (overwrite existing)
     * @param categories an array of categories (array of keys and values)
     */
    private function writeIniFile($filename, &$categories)
    {
        // [category]
        // key = value
        $f = fopen($filename, 'w');
        foreach ($categories as $cat => $conf) {
            fwrite($f, '; {{{ '.$cat."\n\n");
            fwrite($f, '['.$cat.']'."\n\n");
            foreach ($conf as $k => $v) {
                fwrite($f, $k.' = "'.str_replace('"','\\"',$v).'"'."\n");
            }
            fwrite($f, "\n".'; }}}'."\n");
        }
        fwrite($f, '; vim:set syntax=dosini foldmethod=marker:'."\n");
        fclose($f);
    }

    /** Change dynamic config file
     * @param conf array of keys and values to add or replace
     * @param category name of category to change
     * 
     * Opens the dynamic conf file and set values from conf in specified
     * category. Updates config vars too.
     */ 
    public function changeDynamicConfig($conf, $category = 'Core')
    {
        $dynamicfile = $this->spoolroot.'/spool/conf/platal.dynamic.conf';
        if (file_exists($dynamicfile)) {
            $array = parse_ini_file($dynamicfile, true);
        } else {
            $array = null;
        }
        if (!is_array($array)) {
            // dynamic conf is empty
            $array = array($category => $conf);
        } else {
            // looks for a category that looks the same (case insensitive)
            $same = false;
            foreach ($array as $m => &$c) {
                if (strtolower($m) == strtolower($category)) {
                    $same = $m;
                    break;
                }
            }
            if (!$same) {
                // this category doesn't exist yet
                $array[$category] = $conf;
            } else {
                // this category already exists
                $conflower = array();
                foreach ($conf as $k => $v) {
                    $conflower[strtolower($k)] = $v;
                }
                // $conflower is now same as $conf but with lower case keys
                // replaces values of keys that already exists
                foreach ($array[$same] as $k => $v) {
                    if (isset($conflower[strtolower($k)])) {
                        $array[$same][$k] = $conflower[strtolower($k)];
                        unset($conflower[strtolower($k)]);
                    }
                }
                // add new keys
                foreach ($conf as $k => $v) {
                    if (isset($conflower[strtolower($k)])) {
                        $array[$same][$k] = $v;
                    }
                } 
            }
        }
        // writes the file over
        $this->writeIniFile($dynamicfile, $array);
        // rereads the new config to correctly set vars
        $this->readIniFile($dynamicfile);
    }

    public function bootstrap($conf, $callback, $category = 'Core')
    {
        $bootstrap = false;
        $category = strtolower($category);
        foreach ($conf as $key) {
            if (!isset($this->$category->$key)) {
                $bootstrap = true;
                break;
            }
        }
        if ($bootstrap) {
            call_user_func($callback);
        }
    }

    private function setLocale()
    {
        setlocale(LC_MESSAGES, $this->locale);
        setlocale(LC_TIME,     $this->locale);
        setlocale(LC_CTYPE,    $this->locale);
        date_default_timezone_set($this->timezone);
        mb_internal_encoding("UTF-8");
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
