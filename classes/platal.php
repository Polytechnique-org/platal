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

define('PL_FORBIDDEN', 403);
define('PL_NOT_FOUND', 404);

class Platal
{
    var $__mods;
    var $__hooks;

    var $ns;
    var $path;
    var $argv;

    function Platal()
    {
        $modules    = func_get_args();
        $this->path = trim(Get::_get('n', null), '/');

        $this->__mods  = array();
        $this->__hooks = array();

        array_unshift($modules, 'core');
        foreach ($modules as $module) {
            $this->__mods[$module] = $m = PLModule::factory($module);
            $this->__hooks += $m->handlers();
        }
    }

    function pl_self($n = null)
    {
        if (is_null($n))
            return $this->path;

        if ($n >= 0)
            return join('/', array_slice($this->argv, 0, $n + 1));

        if ($n <= -count($this->argv))
            return $this->argv[0];

        return join('/', array_slice($this->argv, 0, $n));
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

    function find_nearest_key($key, &$array)
    {
        $keys = array_keys($array);
        if (in_array($key, $keys)) {
            return $key;
        }
        foreach ($keys as $k) {
            if (strpos($key, $k) !== false || strpos($k, $key) !== false) {
                return $k;
            }
        }
        if (in_array("#final#", $keys)) {
            return "#final#";
        }
        return null;
    }

    function list_hooks()
    {
        $hooks = array();
        foreach ($this->__hooks as $hook=>$handler) {
            $parts = split('/', $hook);
            $place =& $hooks;
            foreach ($parts as $part) {
                if (!isset($place[$part])) {
                    $place[$part] = array();
                }
                $place =& $place[$part]; 
            }
            $place["#final#"] = array();
        }

        $p = split('/', $this->path);
        $place =& $hooks;
        $link  = '';
        $ended = false;
        foreach ($p as $k) {
            if (!$ended) {
                $key = $this->find_nearest_key($k, $place);
            }
            if ($ended || $key == "#final#") {
                $key = $k;
                $ended = true;
            }
            if (!is_null($key)) {
                if (!empty($link)) {
                    $link .= '/';
                }
                $link .= $key;
                $place =& $place[$key];
            } else {
                return null;
            }
        }
        return $link;
    }

    function call_hook(&$page)
    {
        $hook = $this->find_hook();
        if (empty($hook)) {
            return PL_NOT_FOUND;
        }

        $args    = $this->argv;
        $args[0] = &$page;

        if (strlen($hook['perms']) && $hook['perms'] != Session::v('perms')) {
            return PL_FORBIDDEN;
        }

        if ($hook['auth'] > S::v('auth', AUTH_PUBLIC)) {
            if ($hook['type'] == DO_AUTH) {
                global $globals;
    
                if (!call_user_func(array($globals->session, 'doAuth'))) {
                    $this->force_login($page);
                }
            } else {
                return PL_FORBIDDEN;
            }
        }

        return call_user_func_array($hook['hook'], $args);
    }

    function force_login(&$page)
    {
        if (S::logged()) {
            $page->changeTpl('password_prompt_logged.tpl');
            $page->addJsLink('do_challenge_response_logged.js');
        } else {
            $page->changeTpl('password_prompt.tpl');
            $page->addJsLink('do_challenge_response.js');
    	}
        $page->run();
    }

    function run()
    {
        global $page;

        new_skinned_page('index.tpl');

        if (empty($this->path)) {
            $this->path = 'index';
        }

        $page->assign('platal', $this);
        switch ($this->call_hook($page)) {
          case PL_FORBIDDEN:
            $this->__mods['core']->handler_403($page);
            break;

          case PL_NOT_FOUND:
            $page->assign('near', $this->list_hooks());
            $this->__mods['core']->handler_404($page);
            break;
        }

        $page->assign('platal', $this);
        $page->run();
    }

    function on_subscribe($forlife, $uid, $promo, $pass)
    {
        $args = func_get_args();
        foreach ($this->__mods as $mod) {
            if (!is_callable($mod, 'on_subscribe'))
                continue;
            call_user_func_array(array($mod, 'on_subscribe'), $args);
        }
    }
}

?>
