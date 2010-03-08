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

class PlIteratorUtils
{
    /** Build an iterator over an array.
     * @param array The array.
     * @param depth The depth of iteration.
     * @return an iterator that return entries in the form
     *          array(key   => array(0 => key_for_depth0 [, 1 => key_for_depths1, ...]),
     *                value => the value);
     */
    public static function fromArray(array $array, $depth = 1, $flat = false)
    {
        return new PlArrayIterator($array, $depth, $flat);
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


    /** Build an iterator that contains only the elements of the given iterator that
     * match the given condition. The condition should be a callback that takes an element
     * and returns a boolean.
     * @param iterator The source iterator
     * @param callback The condition
     * @return an iterator
     */
    public static function filter(PlIterator $iterator, $callback)
    {
        return new PlFilterIterator($iterator, $callback);
    }


    /** Build an iterator that transforms the element of another iterator. The callback
     * takes an element and transform it into another one. Be careful: if the result
     * of the callback is null, the iteration ends.
     * @param iterator The source iterator
     * @param callback The transformer.
     */
    public static function map(PlIterator $iterator, $callback)
    {
        return new PlMapIterator($iterator, $callback);
    }

    /** Build an iterator whose values are iterators too; such a 'subIterator' will end
     * when the value of $callback changes
     * @param iterator The source iterator
     * @param callback The callback for detecting changes.
     * @return an iterator
     */
    public static function subIterator(PlIterator $iterator, $callback)
    {
        return new SubIterator($iterator, $callback);
    }

    /** Returns the callback for '$x -> $x[$key]';
     * @param $key the index to retrieve in arrays
     * @return a callback
     */
    public static function arrayValueCallback($key)
    {
        $callback = new _GetArrayValueCallback($key);
        return array($callback, 'get');
    }

    /** Returns the callback for '$x -> $x->prop';
     * @param $property The property to retrieve
     * @return a callback
     */
    public static function objectPropertyCallback($property)
    {
        $callback = new _GetObjectPropertyCallback($property);
        return array($callback, 'get');
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
    private $_flat;

    public function __construct(array &$array, $depth = 1, $flat = false)
    {
        $this->array  =& $array;
        $this->depth  = $depth;
        $this->_total = $this->count($array, $depth - 1);
        $this->_pos   = 0;
        $this->_first = false;
        $this->_last  = false;
        $this->_flat  = $flat;

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
        if ($this->_flat) {
            return $val;
        } else {
            return array('keys'  => $keys,
                         'value' => $val);
        }
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


class PlFilterIterator implements PlIterator {
    private $source;
    private $callback;
    private $element;
    private $start;

    public function __construct(PlIterator $source, $callback)
    {
        $this->source = $source;
        $this->callback = $callback;
        $this->start = true;
        $this->element = null;
    }

    private function fetchNext()
    {
        do {
            $current = $this->source->next();
            if (!$current) {
                $this->element = null;
                $this->start   = false;
                return;
            }
            $res = call_user_func($this->callback, $current);
            if ($res) {
                $this->element = $current;
                return;
            }
        } while (true);
    }

    public function next()
    {
        if ($this->element && $this->start) {
            $this->start = false;
        }
        $elt = $this->element;
        if ($elt) {
            $this->fetchNext();
        }
        return $elt;
    }

    public function total()
    {
        /* This is an approximation since the correct total
         * cannot be computed without fetching all the elements
         */
        return $this->source->total();
    }

    public function first()
    {
        return $this->start;
    }

    public function last()
    {
        return !$this->start && !$this->element;
    }
}


class PlMapIterator implements PlIterator
{
    private $source;
    private $callback;

    public function __construct(PlIterator $source, $callback)
    {
        $this->source = $source;
        $this->callback = $callback;
    }

    public function next()
    {
        $elt = $this->source->next();
        if ($elt) {
            return call_user_func($this->callback, $elt);
        } else {
            return null;
        }
    }

    public function total()
    {
        return $this->source->total();
    }

    public function first()
    {
        return $this->source->first();
    }

    public function last()
    {
        return $this->source->last();
    }
}

class PlSubIterator implements PlIterator
{
    private $source;
    private $callback;
    private $next = null; // The next item, if it has been fetched too early by a subiterator

    public function __construct(PlIterator $source, $callback)
    {
        $this->source = $source;
        $this->callback = $callback;
    }

    public function next()
    {
        if ($this->last()) {
            return null;
        } else {
            return new PlInnerSubIterator($this->source, $this->callback, $this, $this->next);
        }
    }

    /** This will always be a too big number, but the actual result can't be easily computed
     */
    public function total()
    {
        return $this->source->total();
    }

    public function last()
    {
        return ($this->source->last() && $this->next == null);
    }

    public function first()
    {
        return $this->source->first();
    }

    // Called by a subiterator to "rewind" the core iterator
    public function setNext($item)
    {
        $this->next = $item;
    }
}

class PlInnerSubIterator implements PlIterator
{
    private $source;
    private $callback;
    private $parent;

    private $next; // Not null if the source has to be "rewinded"

    private $curval = null;
    private $curelt = null;
    private $stepped = false;
    private $over = false;

    public function __construct(PlIterator $source, $callback, PlSubIterator $parent, $next = null)
    {
        $this->source = $source;
        $this->callback = $callback;
        $this->parent = $parent;
        $this->next = $next;
    }

    public function value()
    {
        $this->_step();
        return $this->curval;
    }

    // Move one step, if the current element has been used
    private function _step()
    {
        if ($this->stepped) {
            return;
        }

        if ($this->next != null) {
            $this->curelt = $this->next;
            $this->next = null;
        } else {
            $elt = $this->source->next();
        }
        $this->stepped = true;
    }

    public function next()
    {
        $this->_step();
        $this->stepped = false;

        if ($this->elt) {
            $val = call_user_func($this->callback, $this->elt);
            if ($val == $this->curval) {
                $this->curval = $val;
                return $this->elt;
            } else {
                $this->parent->setNext($this->elt);
            }
        }
        $this->over = true;
        return null;
    }

    /** This will always be a too big number, but the actual result can't be easily computed
     */
    public function total()
    {
        return $this->source->total();
    }

    public function last()
    {
        return $this->over;
    }

    public function first()
    {
        return false;
    }

}

// Wrapper class for 'arrayValueCallback' (get field $key of the given array)
class _GetArrayValueCallback
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function get(array $arr)
    {
        if (array_key_exists($this->key, $arr)) {
            return $arr[$this->key];
        } else {
            return null;
        }
    }
}

// Wrapper class for 'objectPropertyCallback' (get property ->$blah of the given object)
class _GetObjectPropertyCallback
{
    private $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function get($obj)
    {
        $p = $this->property;
        return @$obj->$p;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
