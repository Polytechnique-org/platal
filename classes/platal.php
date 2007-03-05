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

define('PL_DO_AUTH',   300);
define('PL_FORBIDDEN', 403);
define('PL_NOT_FOUND', 404);

class Platal
{
    private $__mods;
    private $__hooks;

    public $ns;
    public $path;
    public $argv;

    public function __construct()
    {
        $modules    = func_get_args();
        if (is_array($modules[0])) {
            $modules = $modules[0];
        }
        $this->path = trim(Get::_get('n', null), '/');

        $this->__mods  = array();
        $this->__hooks = array();

        array_unshift($modules, 'core');
        foreach ($modules as $module) {
            $this->__mods[$module] = $m = PLModule::factory($module);
            $this->__hooks += $m->handlers();
        }
    }

    public function pl_self($n = null)
    {
        if (is_null($n))
            return $this->path;

        if ($n >= 0)
            return join('/', array_slice($this->argv, 0, $n + 1));

        if ($n <= -count($this->argv))
            return $this->argv[0];

        return join('/', array_slice($this->argv, 0, $n));
    }

    protected function find_hook()
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

    protected function find_nearest_key($key, array &$array)
    {
        $keys    = array_keys($array);
        if (in_array($key, $keys)) {
            return $key;
        }

        if (($pos = strpos($key, '.php')) !== false) {
            $key = substr($key, 0, $pos);
        }

        $has_end = in_array("#final#", $keys);
        if (strlen($key) > 24 && $has_end) {
            return "#final#";
        }

        foreach ($keys as $k) {
            if ($k == "#final#") {
                continue;
            }
            $lev = levenshtein($key, $k);
            if ((!isset($val) || $lev < $val) && $lev <= (strlen($k)*2)/3) {
                $val  = $lev;
                $best = $k;
            }
        }
        if (!isset($best) && $has_end) {
            return "#final#";
        } else {
            return $best;
        }
        return null;
    }

    protected function near_hook()
    {
        $hooks = array();
        foreach ($this->__hooks as $hook=>$handler) {
            if (!empty($handler['perms']) && $handler['perms'] != S::v('perms')) {
                continue;
            }
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
        foreach ($p as $k) {
            if (!isset($ended)) {
                $key = $this->find_nearest_key($k, $place);
            } else {
                $key = $k;
            }
            if ($key == "#final#") {
                if (!array_key_exists($link, $this->__hooks)) {
                    return null;
                }
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
        if ($link != $this->path) {
            return $link;
        }
        return null;
    }

    protected function check_perms($perms)
    {
        if (!$perms) { // No perms, no check
            return true;
        }
        $s_perms = S::v('perms');

        // hook perms syntax is
        $perms = explode(',', $perms);
        foreach ($perms as $perm)
        {
            $ok = true;
            $rights = explode(':', $perm);
            foreach ($rights as $right) {
                if (($right{0} == '!' && $s_perms->hasFlag(substr($right, 1))) || !$s_perms->hasFlag($right)) {
                    $ok = false;
                }
            }
            if ($ok) {
                return true;
            }
        }
        return false;
    }

    private function call_hook(PlatalPage &$page)
    {
        $hook = $this->find_hook();
        if (empty($hook)) {
            return PL_NOT_FOUND;
        }

        $args    = $this->argv;
        $args[0] = &$page;

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
        if ($hook['auth'] != AUTH_PUBLIC && !$this->check_perms($hook['perms'])) {
            return PL_FORBIDDEN;
        }

        $val = call_user_func_array($hook['hook'], $args);
        if ($val == PL_DO_AUTH) {
            global $globals;
            // The handler need a better auth with the current args
            if (!call_user_func(array($globals->session, 'doAuth'))) {
                $this->force_login($page);
            }
            $val = call_user_func_array($hook['hook'], $args);
        }
        return $val;
    }

    protected function force_login(PlatalPage &$page)
    {
        if (S::logged()) {
            $page->changeTpl('core/password_prompt_logged.tpl');
            $page->addJsLink('do_challenge_response_logged.js');
        } else {
            $page->changeTpl('core/password_prompt.tpl');
            $page->addJsLink('do_challenge_response.js');
        }
        $page->assign('platal', $this);
        $page->run();
    }

    public function run()
    {
        global $page;

        new_skinned_page('platal/index.tpl');

        if (empty($this->path)) {
            $this->path = 'index';
        }

        $page->assign('platal', $this);
        switch ($this->call_hook($page)) {
          case PL_FORBIDDEN:
            $this->__mods['core']->handler_403($page);
            break;

          case PL_NOT_FOUND:
            $this->__mods['core']->handler_404($page);
            break;
        }

        $page->assign('platal', $this);
        $page->run();
    }

    private function on_subscribe($forlife, $uid, $promo, $pass)
    {
        $args = func_get_args();
        foreach ($this->__mods as $mod) {
            if (!is_callable($mod, 'on_subscribe'))
                continue;
            call_user_func_array(array($mod, 'on_subscribe'), $args);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
