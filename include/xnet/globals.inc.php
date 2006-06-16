<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

require_once('platal/globals.inc.php');

// {{{ class XorgGlobals

class XnetGlobals extends PlatalGlobals
{
    function XnetGlobals()
    {
        $this->PlatalGlobals('XnetSession');
    }

    function init()
    {
        global $globals;
        require_once('xorg/hook.inc.php');

        $globals       = new XnetGlobals;
        $globals->core = new CoreConfig;
        $globals->root = dirname(dirname(dirname(__FILE__)));
        $globals->hook = new XOrgHook();

        $globals->hook->config(null);

        $globals->read_config();
        
        $globals->dbconnect();
        if ($globals->debug & 1) {
            $globals->db->trace_on();
        }
        $globals->xdb =& new XOrgDB;
    }

    function asso($key=null)
    {
        static $aid = null;
        if ($aid === null) {
            $gp  = basename(dirname($_SERVER['PHP_SELF']));
            // for url like /groupex/event.php/file.csv
            if (substr($gp, -4) == ".php")
                $gp = basename(dirname(dirname($_SERVER['PHP_SELF'])));
            $res = $this->xdb->query('SELECT  a.*, d.nom AS domnom
                                        FROM  groupex.asso AS a
                                   LEFT JOIN  groupex.dom  AS d ON d.id = a.dom
                                       WHERE  diminutif = {?}', $gp);
            if (!($aid = $res->fetchOneAssoc())) {
                $aid = array();
            }
        }
        if (empty($key)) {
            return $aid;
        } elseif ( isset($aid[$key]) ) {
            return $aid[$key];
        } else {
            return null;
        }
    }
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
