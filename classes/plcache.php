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

/** This class provide a common API for caching data.
 */
class PlCache
{
    /* Data types
     */
    const SCRIPT  = 0x0001; /* The value expires after the execution of the script */
    const SESSION = 0x0002; /* The value is session specific */
    const TIMER   = 0x0004; /* The value expires after some timeout */

    private static $backends = array();

    private static function getBackend($type)
    {
        if (isset(self::$backends[$type])) {
            return self::$backends[$type];
        }
        $globals = Platal::globals();
        if (($globals->debug & DEBUG_NOCACHE) != 0) {
            $storage = 'none';
        } else if (($globals->debug & DEBUG_SCRIPTCACHE) != 0
                   || php_sapi_name() == 'cli') {
            $storage = 'static';
        } else {
            $storage = 'static';
            switch ($type) {
              case self::TIMER:
                if ($globals->core->memcache) {
                    $storage = 'memcache';
                    break;
                }

              case self::SESSION:
                $storage = 'session';
                break;
            }
        }
        if (!isset(self::$backends[$storage])) {
            switch ($storage) {
              case 'none':
                self::$backends['none'] = new PlDummyCache();
                break;

              case 'static':
                self::$backends['static'] = new PlStaticCache();
                break;

              case 'session':
                self::$backends['session'] = new PlSessionCache();
                break;

              case 'memcache':
                $servers = preg_split('/[, ]+/', $globals->core->memcache);
                self::$backends['memcache'] = new PlMemcacheCache($servers);
                break;
            }
        }
        self::$backends[$type] = self::$backends[$storage];
        return self::$backends[$type];
    }


    /** Get the value associated with the key in the cache.
     *
     * If the value does not exists, and a callback is provided,
     * the value is built by calling the callback with the given
     * expiration time.
     *
     * @throw PlNotFoundInCacheException if the value is not in the
     *        cache and $callback is null.
     */
    private static function get($key, $type, $callback, $cbargs, $expire)
    {
        $backend = self::getBackend($type);
        return $backend->get($key, $type, $callback, $cbargs, $expire);
    }

    /** Invalidate the entry of the cache with the given name.
     */
    private static function invalidate($key, $type)
    {
        $backend = self::getBackend($type);
        return $backend->invalidate($key, $type);
    }

    /** Set the value associated with the key in the cache.
     */
    private static function set($key, $type, $var, $expire)
    {
        $backend = self::getBackend($type);
        return $backend->set($key, $type, $var, $expire);
    }

    /** Check if the key exists in the cache.
     */
    private static function has($key, $type)
    {
        $backend = self::getBackend($type);
        return $backend->has($key, $type);
    }

    /** Clear the cache.
     */
    private static function clear($type)
    {
        $backend = self::getBackend($type);
        $backend->clear($type);
    }


    /** Clear all the cached data.
     */
    public static function clearAll()
    {
        self::clearGlobal();
        self::clearSession();
        self::clearLocal();
    }

    /** Global data storage. Global data is independent from
     * the current session and can thus be shared by several
     * PHP instances (for example using memcache if enabled).
     *
     * Global data can expire. The expire argument follow the
     * semantic of the Memcache:: API:
     *  - 0 mean no timeout
     *  - <= 2592000 mean expires in $expire seconds
     *  - else $expire is an unix timestamp
     */

    public static function getGlobal($key, $callback = null, $cbargs = null,
                                     $expire = 0)
    {
        return self::get($key, self::TIMER, $callback, $cbargs, $expire);
    }

    public static function invalidateGlobal($key)
    {
        return self::invalidate($key, self::TIMER);
    }

    public static function setGlobal($key, $var, $expire = 0)
    {
        return self::set($key, self::TIMER, $var, $expire);
    }

    public static function hasGlobal($key)
    {
        return self::has($key, self::TIMER);
    }

    public static function clearGlobal()
    {
        return self::clear(self::TIMER);
    }


    /** Session data storage. Session data is session-dependent
     * and thus must not be shared between sessions but can
     * be stored in the $_SESSION php variable.
     */

    public static function getSession($key, $callback = null, $cbargs = null)
    {
        return self::get($key, self::SESSION, $callback, $cbargs, 0);
    }

    public static function invalidateSession($key)
    {
        return self::invalidate($key, self::SESSION);
    }

    public static function setSession($key, $var)
    {
        return self::set($key, self::SESSION, $var, 0);
    }

    public static function hasSession($key)
    {
        return self::has($key, self::SESSION);
    }

    public static function clearSession()
    {
        return self::clear(self::SESSION);
    }


    /** Script local data storage. This stores data that
     * expires at the end of the execution of the current
     * script (or page).
     */

    public static function getLocal($key, $callback = null, $cbargs = null)
    {
        return self::get($key, self::SCRIPT, $callback, $cbargs, 0);
    }

    public static function invalidateLocal($key)
    {
        return self::invalidate($key, self::SCRIPT);
    }

    public static function setLocal($key, $var)
    {
        return self::set($key, self::SCRIPT, $var, 0);
    }

    public static function hasLocal($key)
    {
        return self::has($key, self::SCRIPT);
    }

    public static function clearLocal()
    {
        return self::clear(self::SCRIPT);
    }
}


/** Exception thrown when trying to get the value associated
 * with a missing key.
 */
class PlNotFoundInCacheException extends PlException
{
    public function __construct($key, $type)
    {
        parent::__construct('Erreur lors de l\'accès aux données',
                            "Key '$key' not found in cache");
    }
}


/** Interface for the storage backend.
 */
interface PlCacheBackend
{
    /** Return true if the backend contains the given key
     * for the given storage type.
     */
    public function has($key, $type);

    /** Set the value for the given key and type.
     */
    public function set($key, $type, $var, $expire);

    /** Get the value for the given key and type.
     *
     * If the value is not found and a $callback is provided,
     * call the function, pass $cbargs as arguments and use
     * its output as the new value of the entry.
     */
    public function get($key, $type, $callback, $cbargs, $expire);

    /** Remove the entry from the cache.
     */
    public function invalidate($key, $type);

    /** Remove all the entries of the given type from the cache.
     */
    public function clear($type);
}

class PlDummyCache implements PlCacheBackend
{
    public function has($key, $type)
    {
        return false;
    }

    public function set($key, $type, $var, $expire)
    {
    }

    public function get($key, $type, $callback, $cbargs, $expire)
    {
        if (!is_null($callback)) {
            return call_user_func_array($callback, $cbargs);
        } else {
            throw new PlNotFoundInCacheException($key, $type);
        }
    }

    public function invalidate($key, $type)
    {
    }

    public function clear($type)
    {
    }
}

abstract class PlArrayCache implements PlCacheBackend
{
    protected function getData(array $data, $key, $type)
    {
        $key = $this->arrayKey($key, $type);
        if (!isset($data[$key])) {
            throw new PlNotFoundInCacheException($key, $type);
        }
        if ($type == PlCache::TIMER) {
            $entry = $data[$key];
            $timeout = $entry['timeout'];
            if (time() > $timeout) {
                throw new PlNotFoundInCacheException($key, $type);
            }
            return $entry['data'];
        }
        return $data[$key];
    }

    protected function buildData($key, $type, $var, $expire)
    {
        if ($type == PlCache::TIMER) {
            if ($expire == 0) {
                $expire = 2592000;
            }
            if ($expire <= 2592000) {
                $expire = time() + $expire;
            }
            return array('timeout' => $expire,
                         'data'    => $var);
        }
        return $var;
    }

    protected function getAndSetData(array $data, $key, $type,
                                     $callback, $cbargs, $expire)
    {
        if (is_null($callback)) {
            return $this->getData($data, $key, $type);
        } else {
            try {
                $value = $this->getData($data, $key, $type);
            } catch (PlNotFoundInCacheException $e) {
                $value = call_user_func_array($callback, $cbargs);
                $this->set($key, $type, $value, $expire);
            }
            return $value;
        }
    }

    protected abstract function arrayKey($key, $type);

    public function has($key, $type)
    {
        try {
            $this->get($key, $type, null, null, 0);
            return true;
        } catch (PlNotFoundInCacheException $e) {
            return false;
        }
    }
}

class PlStaticCache extends PlArrayCache
{
    private $data = array();

    protected function arrayKey($key, $type)
    {
        return $key;
    }

    public function get($key, $type, $callback, $cbargs, $expire)
    {
        return $this->getAndSetData($this->data, $key, $type,
                                    $callback, $cbargs, $expire);
    }

    public function set($key, $type, $var, $expire)
    {
        $this->data[$this->arrayKey($key, $type)]
            = $this->buildData($key, $type, $var, $expire);
    }

    public function invalidate($key, $type)
    {
        unset($this->data[$key]);
    }

    public function clear($type)
    {
        $this->data = array();
    }
}

class PlSessionCache extends PlArrayCache
{
    public function __construct()
    {
    }

    private function prefix($type)
    {
        return '__cache_' . $type . '_';
    }

    protected function arrayKey($key, $type)
    {
        return $this->prefix($type) . $key;
    }

    public function get($key, $type, $callback, $cbargs, $expire)
    {
        return $this->getAndSetData($_SESSION, $key, $type,
                                    $callback, $cbargs, $expire);
    }

    public function set($key, $type, $var, $expire)
    {
        S::set($this->arrayKey($key, $type),
               $this->buildData($key, $type, $var, $expire));
    }

    public function invalidate($key, $type)
    {
        S::kill($this->arrayKey($key, $type));
    }

    public function clear($type)
    {
        $prefix = $this->prefix($type);
        foreach ($_SESSION as $key=>$value) {
            if (starts_with($key, $prefix)) {
                unset($_SESSION[$key]);
            }
        }
    }
}

class PlMemcacheCache implements PlCacheBackend
{
    private $context;

    public function __construct(array $servers)
    {
        $this->context = new Memcache();
        foreach ($servers as $address) {
            /* XXX: Not IPv6 ready.
             */
            if (strpos($address, ':') !== false) {
                list($addr, $port) = explode(':', $address, 2);
                $this->context->addServer($addr, $port);
            } else {
                $this->context->addServer($address);
            }
        }
    }

    public function has($key, $type)
    {
        return $this->context->get($key) !== false;
    }

    public function get($key, $type, $callback, $cbargs, $expire)
    {
        $value = $this->context->get($key);
        if ($value === false) {
            if (is_null($callback)) {
                throw new PlNotFoundInCacheException($key);
            }
            $value = call_user_func_array($callback, $cbargs);
            $this->set($key, $type, $value, $expire);
        }
        return $value;
    }

    public function set($key, $type, $var, $expire)
    {
        return $this->context->set($key, $var, 0, $expire);
    }

    public function invalidate($key, $type)
    {
        return $this->context->delete($key);
    }

    public function clear($type)
    {
        return $this->context->flush();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
