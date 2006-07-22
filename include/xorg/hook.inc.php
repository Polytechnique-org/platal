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
 **************************************************************************/

// {{{ class XOrgHook

/**
 * XOrg's Hooks API
 *
 * Hooks are used in modules to allow modules that depends upon us to hook
 * themselves in our core functionnalities.
 *
 * Every module will use some hools, and define their names.
 * Pretend « myhook » is one of those names, then :
 *  - hooks/myhook/API has to exists, and will explain the API of the hook
 *  - every module "mod" may have a file hooks/myhook/mod.inc.php that implements
 *    fully or partially the API of the hook.
 *
 * If the hook's API has to change, the functions that change MUST change their
 * name to avoid any compatibility problem.
 *
 * @category XOrgCore
 * @package  XOrgCore
 * @author   Pierre Habouzit <pierre.habouzit@polytechnique.org>
 * @access   public
 * @link     http://doc.polytechnique.org/XOrgModule/#hook
 * @since    Classe available since 0.9.3
 */
class XOrgHook
{
    // {{{ properties

    /**
     * list of all the modules names that have implemented some reactions to our triggers
     *
     * @var    array
     * @access private
     */
    var $_mods = Array();

    // }}}
    // {{{ constructor XOrgHook()

    /**
     * Instanciates our Hook.
     *
     * @param string $name  the name of the hook
     */
    function XOrgHook()
    {
        global $globals;

        foreach (glob($globals->spoolroot."/hooks/*.inc.php") as $file) {
            require_once("$file");
            $this->_mods[] = basename($file, '.inc.php');
        }
    }

    // }}}
    // {{{ function config

    function config()
    {
        foreach ($this->_mods as $mod) {
            if (!function_exists($mod.'_config')) continue;
            call_user_func($mod.'_config');
        }
    }

    // }}}
    // {{{ function subscribe

    function subscribe($forlife, $uid, $promo, $pass)
    {
        foreach ($this->_mods as $mod) {
            if (!function_exists($mod.'_subscribe')) continue;
            call_user_func($mod.'_subscribe', $forlife, $uid, $promo, $pass);
        }
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
