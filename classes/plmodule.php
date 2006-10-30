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

class PLModule
{
    function handlers()     { die("implement me"); }

    function make_hook($fun, $auth, $perms = '', $type = DO_AUTH)
    {
        return array('hook'  => array($this, 'handler_'.$fun),
                     'auth'  => $auth,
                     'perms' => $perms,
                     'type'  => $type);
    }

    /* static functions */

    public static function factory($modname)
    {
        $mod_path = dirname(__FILE__).'/../modules/'.strtolower($modname).'.php';
        $class    = ucfirst($modname).'Module';

        require_once $mod_path;
        return new $class();
    }
}

?>
