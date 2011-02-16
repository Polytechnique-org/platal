<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

// Return values for handlers and hooks. This defines the behavior of both the
// plat/al engine, and the invidivual hooks.
define('PL_DO_AUTH',     300);  // User should be redirected to the login page.
define('PL_BAD_REQUEST', 400);  // Request is not valid, and could not be interpreted.
define('PL_FORBIDDEN',   403);  // User is not allowed to view page (auth or permission error).
define('PL_NOT_FOUND',   404);  // Page doesn't not exist. Engine will try to offer suggestions.
define('PL_WIKI',        500);  // Page is a wiki page, plat/al engine should yield to the wiki engine.
define('PL_JSON',        501);  // Page is valid, but result should be JSON-encoded, not HTML-encoded.

abstract class PlHook
{
    protected $auth;
    protected $perms;
    protected $type;

    protected function __construct($auth = AUTH_PUBLIC, $perms = 'user', $type = DO_AUTH)
    {
        $this->auth  = $auth;
        $this->perms = $perms;
        $this->type  = $type;
    }

    public function checkPerms()
    {
        // Don't check permissions if there are no permission requirement
        // (either no requested group membership, or public auth is allowed).
        return !$this->perms || $this->auth == AUTH_PUBLIC ||
            Platal::session()->checkPerms($this->perms);
    }

    public function hasType($type)
    {
        return ($this->type & $type) == $type;
    }

    abstract protected function run(PlPage $page, array $args);

    public function call(PlPage $page, array $args)
    {
        global $globals, $session, $platal;
        if (!$session->checkAuth($this->auth)) {
            if ($this->hasType(DO_AUTH)) {
                if (!$session->start($this->auth)) {
                    $platal->force_login($page);
                    return PL_FORBIDDEN;
                }
            } else {
                return PL_FORBIDDEN;
            }
        }
        if (!$this->checkPerms()) {
            if (Platal::notAllowed()) {
                return PL_FORBIDDEN;
            }
        }
        return $this->run($page, $args);
    }
}

/** The standard plat/al hook, for interactive requests.
 * It optionally does active authentication (DO_AUTH). The handler is invoked
 * with the PlPage object, and with each of the remaining path components.
 */
class PlStdHook extends PlHook
{
    private $callback;

    public function __construct($callback, $auth = AUTH_PUBLIC, $perms = 'user', $type = DO_AUTH)
    {
        parent::__construct($auth, $perms, $type);
        $this->callback = $callback;
    }

    protected function run(PlPage $page, array $args)
    {
        global $session, $platal;

        $args[0] = $page;
        $val = call_user_func_array($this->callback, $args);
        if ($val == PL_DO_AUTH) {
            if (!$session->start($session->loggedLevel())) {
                $platal->force_login($page);
            }
            $val = call_user_func_array($this->callback, $args);
        }
        return $val;
    }
}

/** A specialized hook for API requests.
 * It is intended to be used for passive API requests, authenticated either by
 * an existing session (with a valid XSRF token), or by an alternative single
 * request auth mechanism implemented by PlSession::apiAuth.
 *
 * This hook is suitable for read-write requests against the website, provided
 * $auth is set appropriately. Note that the auth level is only checked for
 * session-authenticated users, as "apiAuth" users are assumed to always have
 * the requested level (use another hook otherwise).
 *
 * The callback will be passed as arguments the PlPage, the authenticated
 * PlUser, the JSON decoded payload, and the remaining path components, as with
 * any other hook.
 *
 * If the callback intends to JSON-encode its returned value, it is advised to
 * use PlPage::jsonAssign, and return PL_JSON to enable automatic encoding.
 */
class PlApiHook extends PlHook
{
    private $actualAuth;
    private $callback;

    public function __construct($callback, $auth = AUTH_PUBLIC, $perms = 'user', $type = NO_AUTH)
    {
        // As mentioned above, $auth is only applied for session-based auth
        // (as opposed to token-based). PlHook is initialized to AUTH_PUBLIC to
        // avoid it refusing to approve requests; this is important as the user
        // is not yet authenticated at that point (see below for the actual
        // permissions check).
        parent::__construct(AUTH_PUBLIC, $perms, $type);
        $this->actualAuth = $auth;
        $this->callback = $callback;
    }

    private function getEncodedPayload($method)
    {
        return $method == "GET" ? "" : file_get_contents("php://input");
    }

    private function decodePayload($encodedPayload)
    {
        return empty($encodedPayload) ? array() : json_decode($encodedPayload, true);
    }

    protected function run(PlPage $page, array $args)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $encodedPayload = $this->getEncodedPayload($method);
        $jsonPayload = $this->decodePayload($encodedPayload);
        $resource = '/' . implode('/', $args);

        // If the payload wasn't a valid JSON encoded object, bail out early.
        if (is_null($jsonPayload)) {
            $page->trigError("Could not decode the JSON-encoded payload sent with the request.");
            return PL_BAD_REQUEST;
        }

        // Authenticate the request. Try first with the existing session (which
        // is less expensive to check), by veryfing that the XSRF token is
        // valid; otherwise fallbacks to API-type authentication from PlSession.
        if (S::logged() && S::has_xsrf_token() && Platal::session()->checkAuth($this->actualAuth)) {
            $user = S::user();
        } else {
            $user = Platal::session()->apiAuth($method, $resource, $encodedPayload);
        }

        // Check the permissions, unless the handler is fully public.
        if ($this->actualAuth > AUTH_PUBLIC) {
            if (is_null($user) || !$user->checkPerms($this->perms)) {
                return PL_FORBIDDEN;
            }
        }

        // Invoke the callback, whose signature is (PlPage, PlUser, jsonPayload).
        array_shift($args);
        array_unshift($args, $page, $user, $jsonPayload);
        return call_user_func_array($this->callback, $args);
    }
}

/** A specialized hook for token-based requests.
 * It is intended for purely passive requests (typically for serving CSV or RSS
 * content outside the browser), and can fallback to regular session-based
 * authentication when the token is not valid/available.
 *
 * Note that $auth is only applied for session-backed authentication; it is
 * assumed that token-based auth is always enough for the hook (otherwise, just
 * use PlStdHook above).
 *
 * Also, this hook requires that the first two unmatched path components are the
 * user and token (for instance /<matched path>/<user>/<token>/....). They will
 * be popped before being passed to the handler, and replaced by the request's
 * PlUser object.
 */
class PlTokenHook extends PlHook
{
    private $actualAuth;
    private $callback;

    public function __construct($callback, $auth = AUTH_PUBLIC, $perms = 'user', $type = NO_AUTH)
    {
        // See PlApiHook::__construct.
        parent::__construct(AUTH_PUBLIC, $perms, $type);
        $this->actualAuth = $auth;
        $this->callback = $callback;
    }

    protected function run(PlPage $page, array $args)
    {
        // Retrieve the user, either from the session (less expensive, as it is
        // already there), or from the in-path (user, token) pair.
        if (S::logged() && Platal::session()->checkAuth($this->actualAuth)) {
            $user = S::user();
        } else {
            $user = Platal::session()->tokenAuth(@$args[1], @$args[2]);
        }

        // Check the permissions, unless the handler is fully public.
        if ($this->actualAuth > AUTH_PUBLIC) {
            if (is_null($user) || !$user->checkPerms($this->perms)) {
                return PL_FORBIDDEN;
            }
        }

        // Replace the first three remaining elements of the path with the
        // PlPage and PlUser objects.
        array_shift($args);
        $args[0] = $page;
        $args[1] = $user;
        return call_user_func_array($this->callback, $args);
    }
}

/** A specialized plat/al hook for serving wiki pages.
 */
class PlWikiHook extends PlHook
{
    public function __construct($auth = AUTH_PUBLIC, $perms = 'user', $type = DO_AUTH)
    {
        parent::__construct($auth, $perms, $type);
    }

    protected function run(PlPage $page, array $args)
    {
        return PL_WIKI;
    }
}

class PlHookTree
{
    public $hook = null;
    public $aliased  = null;
    public $children = array();

    public function addChildren(array $hooks)
    {
        global $platal;
        foreach ($hooks as $path=>$hook) {
            $path = explode('/', $path);
            $element  = $this;
            foreach ($path as $next) {
                $alias = null;
                if ($next{0} == '%') {
                    $alias = $next;
                    $next = $platal->hook_map(substr($next, 1));
                }
                if (!isset($element->children[$next])) {
                    $child = new PlHookTree();
                    $child->aliased = $alias;
                    $element->children[$next] = $child;
                } else {
                    $child = $element->children[$next];
                }
                $element = $child;
            }
            $element->hook = $hook;
        }
    }

    public function findChild(array $path)
    {
        $remain  = $path;
        $matched = array();
        $aliased = array();
        $element = $this;
        while (true)
        {
            $next = @$remain[0];
            if ($element->aliased) {
                $aliased = $matched;
            }
            if (empty($next) || !isset($element->children[$next])) {
                break;
            }
            $element = $element->children[$next];
            array_shift($remain);
            $matched[] = $next;
        }
        return array($element->hook, $matched, $remain, $aliased);
    }

    private function findNearestChildAux(array $remain, array $matched, array $aliased)
    {
        $next = @$remain[0];
        if ($this->aliased) {
            $aliased = $matched;
        }
        if (!empty($next)) {
            $child = @$this->children[$next];
            if (!$child) {
                $nearest_lev = 50;
                $nearest_sdx = 50;
                $match = null;
                foreach ($this->children as $path=>$hook) {
                    $lev = levenshtein($next, $path);
                    if ($lev <= $nearest_lev
                        && ($lev < strlen($next) / 2 || strpos($next, $path) !== false
                            || strpos($path, $next) !== false)) {
                        $sdx = levenshtein(soundex($next), soundex($path));
                        if ($lev == $nearest_lev || $sdx < $nearest_sdx) {
                            $child = $hook;
                            $nearest_lev = $lev;
                            $nearest_sdx = $sdx;
                            $match = $path;
                        }
                    }
                }
                $next = $match;
            }
            if ($child) {
                array_shift($remain);
                $matched[] = $next;
                return $child->findNearestChildAux($remain, $matched, $aliased);
            }
            if (($pos = strpos($next, '.php')) !== false) {
                $remain[0] = substr($next, 0, $pos);
                return $this->findNearestChildAux($remain, $matched, $aliased);
            }
        }
        return array($this->hook, $matched, $remain, $aliased);
    }

    public function findNearestChild(array $path)
    {
        return $this->findNearestChildAux($path, array(), array());
    }
}

abstract class Platal
{
    private $mods;
    private $hooks;

    protected $https;

    public $ns;
    public $path;
    public $argv = array();

    static private $_page = null;

    public function __construct()
    {
        global $platal, $session, $globals;
        $platal  = $this;

        /* Assign globals first, then call init: init must be used for operations
         * that requires access to the content of $globals (e.g. XDB requires
         * $globals to be assigned.
         */
        $globals = $this->buildGlobals();
        $globals->init();

        /* Get the current session: assign first, then activate the session.
         */
        $session = $this->buildSession();
        if (!$session->startAvailableAuth()) {
            Platal::page()->trigError("Données d'authentification invalides.");
        }

        $modules    = func_get_args();
        if (isset($modules[0]) && is_array($modules[0])) {
            $modules = $modules[0];
        }
        $this->path = trim(Get::_get('n', null), '/');

        $this->mods  = array();
        $this->hooks = new PlHookTree();

        array_unshift($modules, 'core');
        foreach ($modules as $module) {
            $module = strtolower($module);
            $this->mods[$module] = $m = PLModule::factory($module);
            $this->hooks->addChildren($m->handlers());
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

    public static function wiki_hook($auth = AUTH_PUBLIC, $perms = 'user', $type = DO_AUTH)
    {
        return new PlWikiHook($auth, $perms, $type);
    }

    public function hook_map($name)
    {
        return null;
    }

    protected function find_hook()
    {
        $p = explode('/', $this->path);
        list($hook, $matched, $remain, $aliased) = $this->hooks->findChild($p);
        if (empty($hook)) {
            return null;
        }
        $this->argv = $remain;
        array_unshift($this->argv, implode('/', $matched));
        if (!empty($aliased)) {
            $this->ns = implode('/', $aliased) . '/';
        }
        $this->https = !$hook->hasType(NO_HTTPS);
        return $hook;
    }

    public function near_hook()
    {
        $p = explode('/', $this->path);
        list($hook, $matched, $remain, $aliased) = $this->hooks->findNearestChild($p);
        if (empty($hook)) {
            return null;
        }
        $url = implode('/', $matched);
        if (!empty($remain)) {
            $url .= '/' . implode('/', $remain);
        }
        if ($url == $this->path || levenshtein($url, $this->path) > strlen($url) / 3
            || !$hook->checkPerms()) {
            return null;
        }
        return $url;
    }

    private function call_hook(PlPage $page)
    {
        $hook = $this->find_hook();
        if (empty($hook)) {
            return PL_NOT_FOUND;
        }
        global $globals, $session;
        if ($this->https && !@$_SERVER['HTTPS'] && $globals->core->secure_domain) {
            http_redirect('https://' . $globals->core->secure_domain . $_SERVER['REQUEST_URI']);
        }

        return $hook->call($page, $this->argv);
    }

    /** Show the authentication form.
     */
    abstract public function force_login(PlPage $page);

    public function run()
    {
        $page =& self::page();

        if (empty($this->path)) {
            $this->path = 'index';
        }

        try {
            $page->assign('platal', $this);
            $res = $this->call_hook($page);
            switch ($res) {
              case PL_BAD_REQUEST:
                $this->mods['core']->handler_400($page);
                break;

              case PL_FORBIDDEN:
                $this->mods['core']->handler_403($page);
                break;

              case PL_NOT_FOUND:
                $this->mods['core']->handler_404($page);
                break;

              case PL_WIKI:
                return PL_WIKI;
            }
        } catch (Exception $e) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            PlErrorReport::report($e);
            if (self::globals()->debug) {
                $page->kill(pl_entities($e->getMessage())
                            . '<pre>' . pl_entities("" . $e) . '</pre>');
            } else {
                $page->kill(pl_entities($e->getMessage()));
            }
        }

        $page->assign('platal', $this);
        if ($res == PL_JSON) {
            $page->runJSon();
        } else {
            $page->run();
        }
    }

    public function error403()
    {
        $page =& self::page();

        $this->mods['core']->handler_403($page);
        $page->assign('platal', $this);
        $page->run();
    }

    public function error404()
    {
        $page =& self::page();

        $this->mods['core']->handler_404($page);
        $page->assign('platal', $this);
        $page->run();
    }

    public static function notAllowed()
    {
        if (S::admin()) {
            self::page()->trigWarning('Tu accèdes à cette page car tu es administrateur du site.');
            return false;
        } else {
            return true;
        }
    }

    public static function load($modname, $include = null)
    {
        global $platal;
        $modname = strtolower($modname);
        if (isset($platal->mods[$modname])) {
            if (is_null($include)) {
                return;
            }
            $platal->mods[$modname]->load($include);
        } else {
            if (is_null($include)) {
                require_once PLModule::path($modname) . '.php';
            } else {
                require_once PLModule::path($modname) . '/' . $include;
            }
        }
    }

    public static function assert($cond, $error, $userfriendly = null)
    {
        if ($cond === false) {
            if ($userfriendly == null) {
                $userfriendly = "Une erreur interne s'est produite.
                    Merci de réessayer la manipulation qui a déclenché l'erreur ;
                    si cela ne fonctionne toujours pas, merci de nous signaler le problème rencontré.";
            }
            throw new PlException($userfriendly, $error);
        }
    }

    public function &buildLogger($uid, $suid = 0)
    {
        if (defined('PL_LOGGER_CLASS')) {
            $class = PL_LOGGER_CLASS;
            $logger = new $class($uid, $suid);
            return $logger;
        } else {
            return PlLogger::dummy($uid, $suid);
        }
    }

    protected function &buildPage()
    {
        $pageclass = PL_PAGE_CLASS;
        $page = new $pageclass();
        return $page;
    }

    static public function &page()
    {
        if (is_null(self::$_page)) {
            global $platal;
            self::$_page = $platal->buildPage();
        }
        return self::$_page;
    }

    protected function &buildSession()
    {
        $sessionclass = PL_SESSION_CLASS;
        $session = new $sessionclass();
        return $session;
    }

    static public function &session()
    {
        global $session;
        return $session;
    }

    protected function &buildGlobals()
    {
        $globalclass = PL_GLOBALS_CLASS;
        $globals = new $globalclass();
        return $globals;
    }

    static public function &globals()
    {
        global $globals;
        return $globals;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
