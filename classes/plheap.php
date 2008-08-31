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

/** Implements a heap. The order of the elements is determined by the given
 * comparator: the smaller element is the head of the heap.
 */
class PlHeap
{
    private $comparator;
    private $content = array();

    public function __construct($comparator)
    {
        $this->comparator = $comparator;
    }

    public function pop()
    {
        if (empty($this->content)) {
            return null;
        }
        return array_shift($this->content);
    }

    public function push($elt)
    {
        $start = 0;
        $end   = count($this->content);

        while ($start < $end) {
            $middle = floor(($start + $end) / 2);
            $comp   = call_user_func($this->comparator, $elt, $this->content[$middle]);
            if ($comp < 0) {
                $end = $middle;
            } else if ($comp > 0) {
                $start = $middle + 1;
            } else {
                array_splice($this->content, $middle, 0, array($elt));
                return;
            }
        }
        array_splice($this->content, $start, 0, array($elt));
    }

    public function count()
    {
        return count($this->content);
    }

    public function iterator()
    {
        return PlIterator::fromArray($this->content);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
