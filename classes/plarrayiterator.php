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

class PlArrayIterator implements PlIterator
{
    private $array;
    private $depth;

    private $_its = array();

    private $_total;
    private $_first;
    private $_last;
    private $_pos;

    public function __construct(array &$array, $depth = 1)
    {
        $this->array  =& $array;
        $this->depth  = $depth;
        $this->_total = $this->count($array, $depth - 1);
        $this->_pos   = 0;
        $this->_first = false;
        $this->_last  = false;

        for ($i = 0 ; $i < $depth ; ++$i) {
            if ($i == 0) {
                $this->_its[] = $array;
            } else {
                $this->_its[] = current($this->_its[$i - 1]);
            }
            reset($this->_its[$i]);
        }
    }

    private function count(array &$array, $depth)
    {
        if ($depth == 0) {
            return count($array);
        } else {
            $sum = 0;
            foreach ($array as &$item) {
                $sum += $this->count($item, $depth - 1);
            }
            return $sum;
        }
    }

    private function nextArray($depth)
    {
        if ($depth == 0) {
            return;
        }
        $this->_its[$depth] = next($this->_its[$depth - 1]);
        if ($this->_its[$depth] === false) {
            $this->nextArray($depth - 1);
            if ($this->_its[$depth - 1] === false) {
                return;
            }
            $this->_its[$depth] = current($this->_its[$depth - 1]);
        }
        reset($this->_its[$depth]);
    }

    public function next()
    {
        ++$this->_pos;
        $this->_first = ($this->_total > 0 && $this->_pos == 1);
        $this->_last  = ($this->_pos == $this->_total);
        if ($this->_pos > $this->_total) {
            return null;
        }

        $val = current($this->_its[$this->depth - 1]);
        if ($val === false) {
            $this->nextArray($this->depth - 1);
            $val = current($this->_its[$this->depth - 1]);
            if ($val === false) {
                return null;
            }
        }
        $keys = array();
        for ($i = 0 ; $i < $this->depth ; ++$i) {
            $keys[] = key($this->_its[$i]);
        }
        next($this->_its[$this->depth - 1]);
        return array('keys'  => $keys,
                     'value' => $val);
    }

    public function total()
    {
        return $this->_total;
    }

    public function first()
    {
        return $this->_first;
    }

    public function last()
    {
        return $this->_last;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
