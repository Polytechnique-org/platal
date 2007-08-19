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

abstract class PLModule
{
    abstract function handlers();

    /** Register a hook
     * @param fun name of the handler (the exact name will be handler_$fun)
     * @param auth authentification level of needed to run this handler
     * @param perms permission required to run this handler
     * @param type additionnal flags
     *
     * Perms syntax is the following:
     * perms = rights(,rights)*
     * rights = right(:right)*
     * right is an atomic right permission (like 'admin', 'user', 'groupadmin', 'groupmember'...)
     *
     * If type is set to NO_AUTH, the system will return 403 instead of asking auth data
     * this is useful for Ajax handler
     * If type is not set to NO_SKIN, the system will consider redirecting the user to https
     */
    public function make_hook($fun, $auth, $perms = 'user', $type = DO_AUTH)
    {
        return array('hook'  => array($this, 'handler_'.$fun),
                     'auth'  => $auth,
                     'perms' => $perms,
                     'type'  => $type);
    }

    /* static functions */

    public static function factory($modname)
    {
        $mod_path = dirname(__FILE__) . '/../modules/' . $modname . '.php';
        $class    = ucfirst($modname) . 'Module';

        require_once $mod_path;
        return new $class();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
