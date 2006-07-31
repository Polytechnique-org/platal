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

class XnetGlobals extends PlatalGlobals
{
    function XnetGlobals()
    {
        $this->PlatalGlobals('XnetSession');
    }

    function init()
    {
        global $globals;

        $globals       = new XnetGlobals;
        $globals->core = new CoreConfig;

        $globals->read_config();

        $globals->dbconnect();
    }

    function asso($key=null)
    {
        static $aid = null;

        if (is_null($aid)) {
            $gp = Get::v('n');
            $gp = substr($gp, 0, strpos($gp, '/'));

            if ($gp) {
                $res = XDB::query('SELECT  a.*, d.nom AS domnom
                                     FROM  groupex.asso AS a
                                LEFT JOIN  groupex.dom  AS d ON d.id = a.dom
                                    WHERE  diminutif = {?}', $gp);
                if (!($aid = $res->fetchOneAssoc())) {
                    $aid = array();
                }
            } else {
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
