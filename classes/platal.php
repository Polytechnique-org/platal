<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

    protected $https;

    public $ns;
    public $path;
    public $argv;

    static private $_page = null;

    public function __construct()
    {
        global $platal, $session, $globals;
        $platal  =& $this;
        $globalclass = PL_GLOBALS_CLASS;
        $globals = new $globalclass();
        $globals->init();
        $sessionclass = PL_SESSION_CLASS;
        $session = new $sessionclass();
        if (!$session->startAvailableAuth()) {
            Platal::page()->trigError('Données d\'authentification invalide.');
        }

        $modules    = func_get_args();
        if (is_array($modules[0])) {
            $modules = $modules[0];
        }
        $this->path = trim(Get::_get('n', null), '/');

        $this->__mods  = array();
        $this->__hooks = array();

        array_unshift($modules, 'core');
        foreach ($modules as $module) {
            $module = strtolower($module);
            $this->__mods[$module] = $m = PLModule::factory($module);
            $this->__hooks += $m->handlers();
        }

        if ($globals->mode == '') {
            pl_redirect('index.html');
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

        $this->https = ($hook['type'] & NO_HTTPS) ? false : true;
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

            if ((!isset($val) || $lev < $val)
                && ($lev <= strlen($k)/2 || strpos($k, $key) !== false || strpos($key, $k) !== false)) {
                $val  = $lev;
                $best = $k;
            }
        }
        if (!isset($best) && $has_end) {
            return "#final#";
        } else if (isset($best)) {
            return $best;
        }
        return null;
    }

    public function near_hook()
    {
        $hooks = array();
        $leafs = array();
        foreach ($this->__hooks as $hook=>$handler) {
            if (!$this->check_perms($handler['perms'])) {
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
            $leaf = $parts[count($parts)-1];
            if (!isset($leafs[$leaf])) {
                $leafs[$leaf] = $hook;
            } else if (is_array($leafs[$leaf])) {
                $leafs[$leaf][] = $hook;
            } else {
                $leafs[$leaf] = array($hook, $leafs[$leaf]);
            }
            $place["#final#"] = array();
        }

        // search for the nearest full path
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
                    $link = '';
                    break;
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
                $link = '';
                break;
            }
        }
        if ($link == $this->path) {
            $link = '';
        }
        if ($link && levenshtein($link, $this->path) < strlen($link)/3) {
            return $link;
        }

        // search for missing namespace (the given name is a leaf)
        $leaf = array_shift($p);
        $args = count($p) ? '/' . implode('/', $p) : '';
        if (isset($leafs[$leaf]) && !is_array($leafs[$leaf]) && $leafs[$leaf] != $this->path) {
            return $leafs[$leaf] . $args;
        }
        unset($val);
        $best = null;
        foreach ($leafs as $k=>&$path) {
            if (is_array($path)) {
                continue;
            }
            $lev = levenshtein($leaf, $k);

            if ((!isset($val) || $lev < $val)
                && ($lev <= strlen($k)/2 || strpos($k, $leaf) !== false || strpos($leaf, $k) !== false)) {
                $val  = $lev;
                $best = $path;
            }
        }
        return $best == null ? ( $link ? $link : null ) : $best . $args;
    }

    protected function check_perms($perms)
    {
        if (!$perms) { // No perms, no check
            return true;
        }
        $s_perms = S::v('perms');
        return $s_perms->hasFlagCombination($perms);
    }

    private function call_hook(PlPage &$page)
    {
        $hook = $this->find_hook();
        if (empty($hook)) {
            return PL_NOT_FOUND;
        }
        global $globals, $session;
        if ($this->https && !$_SERVER['HTTPS'] && $globals->core->secure_domain) {
            http_redirect('https://' . $globals->core->secure_domain . $_SERVER['REQUEST_URI']);
        }

        $args    =  $this->argv;
        $args[0] =& $page;

        if ($hook['auth'] > S::v('auth', AUTH_PUBLIC)) {
            if ($hook['type'] & DO_AUTH) {
                if (!$session->start($hook['auth'])) {
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
            // The handler need a better auth with the current args
            if (!$session->start($hook['auth'])) {
                $this->force_login($page);
            }
            $val = call_user_func_array($hook['hook'], $args);
        }
        return $val;
    }

    public function force_login(PlPage &$page)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
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
        $page =& self::page();

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

    public function on_subscribe($forlife, $uid, $promo, $pass)
    {
        $args = func_get_args();
        foreach ($this->__mods as $mod) {
            if (!is_callable($mod, 'on_subscribe'))
                continue;
            call_user_func_array(array($mod, 'on_subscribe'), $args);
        }
    }

    static public function &page()
    {
        global $platal;
        if (is_null(self::$_page)) {
            $pageclass = PL_PAGE_CLASS;
            self::$_page = new $pageclass();
        }
        return self::$_page;
    }

    static public function &session()
    {
        global $session;
        return $session;
    }

    static public function &globals()
    {
        global $globals;
        return $globals;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
