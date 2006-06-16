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

// {{{ class Env

class Env
{
    // {{{ function _get

    function _get($key, $default)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }
    
    // }}}
    // {{{ function has

    function has($key)
    {
        return isset($_REQUEST[$key]);
    }
    
    // }}}
    // {{{ function kill
    
    function kill($key)
    {
        unset($_REQUEST[$key]);
    }

    // }}}
    // {{{ function get
    
    function get($key, $default='')
    {
        return (string)Env::_get($key, $default);
    }

    // }}}
    // {{{ function &getMixed
    
    function &getMixed($key, $default=null)
    {
        return Env::_get($key, $default);
    }

    // }}}
    // {{{ function getBool
    
    function getBool($key, $default=false)
    {
        return (bool)Env::_get($key, $default);
    }

    // }}}
    // {{{ function getInt
    
    function getInt($key, $default=0)
    {
        $i = Env::_get($key, $default);
        return preg_match(',^[0-9]+$,', $i) ? intval($i) : $default;
    }

    // }}}
}

// }}}
// {{{ class Post

class Post
{
    // {{{ function _get

    function _get($key, $default)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    
    // }}}
    // {{{ function has

    function has($key)
    {
        return isset($_POST[$key]);
    }
    
    // }}}
    // {{{ function kill
    
    function kill($key)
    {
        unset($_POST[$key]);
    }

    // }}}
    // {{{ function get
    
    function get($key, $default='')
    {
        return (string)Post::_get($key, $default);
    }

    // }}}
    // {{{ function &getMixed
    
    function &getMixed($key, $default=null)
    {
        return Post::_get($key, $default);
    }

    // }}}
    // {{{ function getBool
    
    function getBool($key, $default=false)
    {
        return (bool)Post::_get($key, $default);
    }

    // }}}
    // {{{ function getInt
    
    function getInt($key, $default=0)
    {
        $i = Post::_get($key, $default);
        return preg_match(',^[0-9]+$,', $i) ? intval($i) : $default;
    }

    // }}}
}

// }}}
// {{{ class Get

class Get
{
    // {{{ function _get

    function _get($key, $default)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    // }}}
    // {{{ function has

    function has($key)
    {
        return isset($_GET[$key]);
    }
    
    // }}}
    // {{{ function kill
    
    function kill($key)
    {
        unset($_GET[$key]);
    }

    // }}}
    // {{{ function get
    
    function get($key, $default='')
    {
        return (string)Get::_get($key, $default);
    }

    // }}}
    // {{{ function &getMixed
    
    function &getMixed($key, $default=null)
    {
        return Get::_get($key, $default);
    }

    // }}}
    // {{{ function getBool
    
    function getBool($key, $default=false)
    {
        return (bool)Get::_get($key, $default);
    }

    // }}}
    // {{{ function getInt
    
    function getInt($key, $default=0)
    {
        $i = Get::_get($key, $default);
        return preg_match(',^[0-9]+$,', $i) ? intval($i) : $default;
    }

    // }}}
}

// }}}
// {{{ class Session

class Session
{
    // {{{ function _get

    function _get($key, $default)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    // }}}
    // {{{ function has

    function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    // }}}
    // {{{ function kill
    
    function kill($key)
    {
        unset($_SESSION[$key]);
    }

    // }}}
    // {{{ function get
    
    function get($key, $default='')
    {
        return (string)Session::_get($key, $default);
    }

    // }}}
    // {{{ function &getMixed
    
    function &getMixed($key, $default=null)
    {
        return Session::_get($key, $default);
    }

    // }}}
    // {{{ function getBool
    
    function getBool($key, $default=false)
    {
        return (bool)Session::_get($key, $default);
    }

    // }}}
    // {{{ function getInt
    
    function getInt($key, $default=0)
    {
        $i = Session::_get($key, $default);
        return preg_match(',^[0-9]+$,', $i) ? intval($i) : $default;
    }

    // }}}
}

// }}}
// {{{ class Cookie

class Cookie
{
    // {{{ function _get

    function _get($key, $default)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }
    
    // }}}
    // {{{ function has

    function has($key)
    {
        return isset($_COOKIE[$key]);
    }
    
    // }}}
    // {{{ function kill
    
    function kill($key)
    {
        unset($_COOKIE[$key]);
    }

    // }}}
    // {{{ function get
    
    function get($key, $default='')
    {
        return (string)Cookie::_get($key, $default);
    }

    // }}}
    // {{{ function &getMixed
    
    function &getMixed($key, $default=null)
    {
        return Cookie::_get($key, $default);
    }

    // }}}
    // {{{ function getBool
    
    function getBool($key, $default=false)
    {
        return (bool)Cookie::_get($key, $default);
    }

    // }}}
    // {{{ function getInt
    
    function getInt($key, $default=0)
    {
        $i = Cookie::_get($key, $default);
        return preg_match(',^[0-9]+$,', $i) ? intval($i) : $default;
    }

    // }}}
}

// }}}

function fix_gpc_magic(&$item, $key) {
    if (is_array($item)) {
        array_walk($item, 'fix_gpc_magic');
    } else {
        $item = stripslashes($item);
    }
}

function unfix_gpc_magic(&$item, $key) {
    if (is_array($item)) {
        array_walk($item, 'unfix_gpc_magic');
    } else {
        $item = addslashes($item);
    }
}

if (ini_get("magic_quotes_gpc")) {
    array_walk($_GET, 'fix_gpc_magic');
    array_walk($_POST, 'fix_gpc_magic');
    array_walk($_COOKIE, 'fix_gpc_magic');
    array_walk($_REQUEST, 'fix_gpc_magic');
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
