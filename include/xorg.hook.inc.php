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
 **************************************************************************/

require_once("PEAR.php");

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
 * @version  $Id: xorg.hook.inc.php,v 1.8 2004-12-01 13:13:03 x2000habouzit Exp $
 * @access   public
 * @link     http://doc.polytechnique.org/XOrgModule/#hook
 * @since    Classe available since 0.9.3
 */
class XOrgHook extends PEAR
{
    // {{{ properties
    
    /**
     * holds the name of the hook we want to run.
     *
     * @var    string
     m @access private
     */
    var $_name;
    
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
    function XOrgHook($name)
    {
        global $globals;
        $this->PEAR();

        if (!file_exists($globals->root."/hooks/$name/API")) {
            $this->raiseError("The hook « $name » do not exists, or is undocumented",1,PEAR_ERROR_DIE);
        }
        foreach (glob($globals->root."/hooks/$name/*.inc.php") as $file) {
            require_once("$file");
            $this->_mods[] = basename($file, '.inc.php');
        }
    }

    // }}}
    // {{{ function __call()

    /**
     * The overload helper for function calls.
     *
     * @param  callback $function   the name of the function called
     * @param  array    $arguments  the array of the arguments
     * @param  mixed    $return     a reference to place the result of the called function
     * @retuns mixed    returns the folded value
     *                  f1(arg_1,...,arg_n-1, f2(arg1,...,arg_n-1, ... f_k(arg1,...arg_n))...)
     * @see overload
     */
    function __call($function, $arguments, &$return)
    {
        if ( ($i = count($arguments) - 1) < 0) {
            $this->raiseError("In the Hook « {$this->_name} » the function « $function » expects at least 1 argument");
        }
        foreach ($this->_mods as $mod) {
            echo $mod.'_'.$function;
            if (!function_exists($mod.'_'.$function)) continue;
            $arguments[$i] =& call_user_func_array($mod.'_'.$function,$arguments);
        }

        $return =& $arguments[$i];
        
        return true;
    }

    // }}}
}

overload('XOrgHook');

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
