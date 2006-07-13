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

define('PL_OK', 0);
define('PL_NEEDLOGIN', 1);
define('PL_FORBIDDEN', 403);
define('PL_NOT_FOUND', 404);

class Platal
{
    var $__mods;
    var $__hooks;

    var $path;
    var $argv;

    function Platal()
    {
        $this->path = trim(Get::_get('p', null), '/');

        $this->__mods  = array();
        $this->__hooks = array();

        foreach (glob(dirname(__FILE__).'/../modules/*.php') as $module) {
            $module = basename($module, '.php');
            $m =& PLModule::factory($this, $module);
            $this->__mods[$module] =& $m;
            $this->__hooks += $m->handlers();
        }
    }

    function find_hook()
    {
        $p = $this->path;

        while ($p) {
            if (array_key_exists($p, $this->__hooks))
                break;

            $p = substr($p, 0, strrpos($p, '/'));
        }

        if (empty($this->__hooks[$p])) {
            return null;
        }

        $hook = $this->__hooks[$p];

        if (!is_callable($hook['hook'])) {
            return null;
        }

        $this->argv    = explode('/', substr($this->path, strlen($p)));
        $this->argv[0] = $p;

        return $hook;
    }

    function call_hook(&$page)
    {
        $hook = $this->find_hook();

        if (is_null($hook)) {
            return PL_NOT_FOUND;
        }

        $args    = $this->argv;
        $args[0] = &$page;

        if ($hook['auth'] > Session::get('auth', AUTH_PUBLIC)) {
            $_SESSION['session']->doAuth($page);
        }

        return call_user_func_array($hook['hook'], $args);
    }

    function run()
    {
        global $page;

        new_skinned_page('index.tpl', AUTH_PUBLIC);

        if (empty($this->path)) {
            $this->__mods['core']->handler_index($page);
        } else
        switch ($this->call_hook($page)) {
          case PL_FORBIDDEN:
            $this->__mods['core']->handler_403($page);
            break;

          case PL_NOT_FOUND:
            $this->__mods['core']->handler_404($page);
            break;
        }
        $page->assign_by_ref('platal', $this);
        $page->run();
    }
}

?>
