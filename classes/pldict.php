<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

class PlDict
{
    private $array;

    public function __construct(array $array = array())
    {
        $this->array = $array;
    }

    private function _get($key, $default)
    {
        return isset($this->array[$key]) ? $this->array[$key] : $default;
    }

    public function has($key)
    {
        return isset($this->array[$key]);
    }

    public function kill($key)
    {
        unset($this->array[$key]);
    }

    public function set($key, $value)
    {
        $this->array[$key] = $value;
    }

    public function v($key, $default = null)
    {
        return $this->_get($key, $default);
    }

    public function b($key, $default = false)
    {
        return (bool)$this->_get($key, $default);
    }

    public function s($key, $default = '')
    {
        return (string)$this->_get($key, $default);
    }

    public function t($key, $default = '')
    {
        return trim($this->s($key, $default));
    }

    public function blank($key, $strict = false)
    {
        if (!$this->has($key)) {
            return true;
        }
        $var = $strict ? $this->s($key) : $this->t($key);
        return empty($var);
    }

    public function i($key, $default = 0)
    {
        $i = $this->_get($key, $default);
        return (is_int($i) || ctype_digit($i)) ? intval($i) : $default;
    }

    public function l(array $keys)
    {
        return array_map(array($this, 'v'), $keys);
    }

    public function dict()
    {
        return $this->array;
    }

    public function count()
    {
        return count($this->array);
    }

    public function merge(array $array)
    {
        $this->array = array_merge($this->array, $array);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
