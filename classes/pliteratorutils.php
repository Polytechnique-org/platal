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

class PlIteratorUtils
{
    /** Build an iterator over an array.
     * @param array The array.
     * @param depth The depth of iteration.
     * @return an iterator that return entries in the form
     *          array(key   => array(0 => key_for_depth0 [, 1 => key_for_depths1, ...]),
     *                value => the value);
     */
    public static function fromArray(array $array, $depth = 1)
    {
        return new PlArrayIterator($array, $depth);
    }


    /** Sort an iterator using the given sort callback.
     * @param iterator The iterator to sort.
     * @param callback The callback for comparison.
     * @return a new iterator with the entries sorted.
     */
    public static function sort(PlIterator $iterator, $callback)
    {
        $heap = new PlHeap($callback);
        while ($item = $iterator->next()) {
            $heap->push($item);
        }
        return $heap->iterator();
    }


    /** Merge several iterator into a unique one.
     * @param iterators Array of iterators.
     * @param callback  The callback for comparison.
     * @param sorted    Tell wether the iterators are already sorted using the given callback.
     * @return an iterator.
     */
    public static function merge(array $iterators, $callback, $sorted = true)
    {
        return new PlMergeIterator($iterators, $callback, $sorted);
    }
}


/** Iterates over an array.
 */
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


/** Iterator that return the result of a merge of several iterators.
 */
class PlMergeIterator implements PlIterator
{
    /* The heap is field with entries with the form:
     *  array('it' => id of the iterator this entry come from,
     *        'value' => value of the entry).
     */
    private $heap;
    private $preComputed = false;
    private $comparator;
    private $iterators;
    private $_total;
    private $pos;

    public function __construct(array $iterators, $callback, $sorted = true)
    {
        $this->heap = new PlHeap(array($this, 'compare'));
        $this->_total = 0;
        $this->comparator = $callback;
        if ($sorted) {
            $this->iterators = $iterators;
            foreach ($this->iterators as $key => &$it) {
                $this->_total += $it->total();
                $item = $it->next();
                if (!is_null($item)) {
                    $this->heap->push(array('it' => $key, 'value' => $item));
                }
            }
        } else {
            $this->preComputed = true;
            foreach ($iterators as $key => &$it) {
                $this->_total += $it->total();
                while (!is_null($item = $it->next())) {
                    $this->heap->push(array('it' => $key, 'value' => $item));
                }
            }
        }
        $this->pos = 0;
    }

    /** Compare two entries of the heap using the comparator of the user.
     */
    public function compare($a, $b)
    {
        $cp = call_user_func($this->comparator, $a['value'], $b['value']);
        if ($cp == 0) {
            return $a['it'] - $b['it'];
        }
        return $cp;
    }

    public function total()
    {
        return $this->_total;
    }

    public function next()
    {
        ++$this->pos;
        $entry = $this->heap->pop();
        if (is_null($entry)) {
           return null;
        }
        if ($this->preComputed) {
            return $entry['value'];
        }
        $it = $entry['it'];
        $item = $this->iterators[$it]->next();
        if (!is_null($item)) {
            $this->heap->push(array('it' => $it, 'value' => $item));
        }
        return $entry['value'];
    }

    public function last()
    {
        return $this->heap->count() == 0;
    }

    public function first()
    {
        return $this->pos == 1;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
