<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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

require_once('diogenes/diogenes.core.globals.inc.php');
require_once('diogenes/diogenes.database.inc.php');
require_once('platal/iterator.inc.php');
require_once('platal/database.inc.php');

// {{{ class CoreConfig

class CoreConfig
{
    var $locale = 'fr_FR';
}

// }}}

// {{{ class XorgGlobals

class PlatalGlobals extends DiogenesCoreGlobals
{
    var $page    = 'XorgPage';
    var $session;
    var $menu;
    var $hook;

    /** The x.org version */
    var $version = '0.9.8';
    var $debug   = 0;

    /** db params */
    var $dbdb               = 'x4dat';
    var $dbhost             = 'localhost';
    var $dbuser             = 'x4dat';
    var $dbpwd              = 'x4dat';
    
    var $table_auth         = 'auth_user_md5';
    var $table_log_actions  = 'logger.actions';
    var $table_log_sessions = 'logger.sessions';
    var $table_log_events   = 'logger.events';

    /** logger */
    var $tauth  = array('native'=>'auth_user_md5');
    var $tlabel = array('native'=>'X.Org');

    /** paths */
    var $baseurl   = 'http://localhost/xorg';
    var $spoolroot = '/var/spool/xorg';
    var $root      = null;

    function PlatalGlobals($sess)
    {
        $this->session = $sess;
    }

    function read_config()
    {
        $array = parse_ini_file($this->root.'/configs/platal.conf', true);
        if (!is_array($array)) {
            return;
        }

        foreach ($array as $cat=>$conf) {
            $c = strtolower($cat);
            foreach ($conf as $key=>$val) {
                if ($c == 'core' && isset($this->$key)) {
                    $this->$key=$val;
                } else {
                    $this->$c->$key = $val;
                }
            }
        }
    }

    function setlocale()
    {
        global $globals;
        setlocale(LC_MESSAGES, $globals->core->locale);
        setlocale(LC_TIME,     $globals->core->locale);
        setlocale(LC_CTYPE,    $globals->core->locale);
    }
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
