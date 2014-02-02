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

class PlIteratorUtils
{
    /** Builds a new empty iterator
     */
    public static function emptyIterator()
    {
        return new PlEmptyIterator();
    }

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
     * when the value of $callback changes;
     * WARNING: will fast-forward the current subiterator until it is over !
     * @param iterator The source iterator
     * @param callback The callback for detecting changes. XXX: Might be called twice on a given object
     * @return an iterator
     */
    public static function subIterator(PlIterator $iterator, $callback)
    {
        return new PlSubIterator($iterator, $callback);
    }

    /** Build an iterator that will iterate over the given set of iterators, returning consistent
     * sets of values (i.e only the values for which the result of the callback is the same as that
     * for the master)
     * @param iterators The set of iterators
     * @param callbacks A list of callbacks (one for each iterator), or a single, common, callback
     * @param master The id of the "master" iterator in the first list
     */
    public static function parallelIterator(array $iterators, $callbacks, $master)
    {
        return new PlParallelIterator($iterators, $callbacks, $master);
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

    /** Returns a wrapper around the PlIterator suitable for foreach() iterations
     */
    public static function foreachIterator(PlIterator $iterator)
    {
        return new SPLIterator($iterator);
    }
}

/** Empty iterator
 */
class PlEmptyIterator implements PlIterator
{
    public function first()
    {
        return false;
    }

    public function last()
    {
        return false;
    }

    public function next()
    {
        return null;
    }

    public function total()
    {
        return 0;
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


class PlFilterIterator implements PlIterator
{
    private $pos;
    private $source;
    private $callback;
    private $element;

    public function __construct(PlIterator $source, $callback)
    {
        $this->source   = $source;
        $this->callback = $callback;
        $this->pos      = 0;
        $this->element  = $this->fetchNext();
    }

    private function fetchNext()
    {
        do {
            $current = $this->source->next();
            if (is_null($current) || call_user_func($this->callback, $current)) {
                return $current;
            }
        } while (true);
    }

    public function next()
    {
        ++$this->pos;
        $elt = $this->element;
        if (!is_null($this->element)) {
            $this->element = $this->fetchNext();
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
        return $this->pos == 1;
    }

    public function last()
    {
        return is_null($this->element);
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
    private $pos = 0;
    private $sub = null;

    public function __construct(PlIterator $source, $callback)
    {
        $this->source = $source;
        $this->callback = $callback;
    }

    /** WARNING: this will "fast-forward" the subiterator to its end
     */
    public function next()
    {
        if ($this->last()) {
            return null;
        } else {
            if ($this->sub != null) {
                while (!$this->sub->last()) {
                    $this->sub->next();
                }
            }

            if ($this->last()) {
                return null;
            }

            ++$this->pos;
            $this->sub = new PlInnerSubIterator($this->source, $this->callback, $this, $this->next);
            return $this->sub;
        }
    }

    /** This will always be a too big number, but the actual result can't be easily computed
     */
    public function total()
    {
        return $this->source->total();
    }

    /** This will only return true if the current subiterator was the last one,
     * and if it has been fully used
     */
    public function last()
    {
        if ($this->sub != null && !$this->sub->last()) {
            return false;
        }
        return ($this->source->last() && $this->next == null);
    }

    public function first()
    {
        return $this->pos == 1;
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
    private $val = null;
    private $pos = 0;
    private $stepped = false;
    private $over = false;

    public function __construct(PlIterator $source, $callback, PlSubIterator $parent, $next = null)
    {
        $this->source = $source;
        $this->callback = $callback;
        $this->parent = $parent;
        $this->next = $next;
        $this->parent->setNext(null);
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
            if ($this->source->last()) {
                $this->over = true;
                return;
            }
            $this->curelt = $this->source->next();
        }

        if ($this->pos == 0) {
            $this->val = call_user_func($this->callback, $this->curelt);
            $this->curval = $this->val;
        } else {
            $this->curval = call_user_func($this->callback, $this->curelt);
        }

        $this->stepped = true;
    }

    public function next()
    {
        if ($this->over) {
            return null;
        }

        $this->_step();

        if ($this->over) {
            return null;
        }

        ++$this->pos;
        $this->stepped = false;

        if ($this->val == $this->curval) {
            return $this->curelt;
        }

        $this->parent->setNext($this->curelt);
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
        if ($this->over) {
            return true;
        }
        $this->_step();
        return $this->over || ($this->val != $this->curval);
    }

    public function first()
    {
        return $this->pos == 1;
    }

}

/** Builds an iterator over a set of iterators, from which one is given as 'master';
 * The arguments are :
 *  - An array of iterators, to iterate simultaneously
 *  - An array of callbacks (one attached to each iterator), or a single callback (to
 *      use for all iterators)
 *  - The id of the 'master' iterator
 *
 * This ParallelIterator will iterate over the iterators, and, at each
 * step of the master iterator, it will apply the callbacks to the corresponding
 * iterators and return the values of the "slaves" for which the callback returned the
 * same value as the callback of the master.
 *
 * The callback should compute some kind of index, and never return the same value
 * twice for a given iterator
 *
 * It is assumed that, if the callback for a slave doesn't have the same value
 * as the value for the master, this means that there is a "hole" in the values
 * of that slave.
 *
 * Example :
 *   - The callback is 'get the first cell'
 *   - The master is :
 *      [0, 1], [1, 13], [2, 42]
 *   - The first slave (slave1) is :
 *      [0, 'a'], [2, 'b']
 *   - The second slave (slave2) is :
 *      [1, 42], [2, 0]
 * The resulting iterator would yield :
 * - array(master => [0, 1], slave1 => [0, 'a'], slave2 => null)
 * - array(master => [1, 13], slave1 => null, slave2 => [1, 42])
 * - array(master => [2, 42], slave1 => [1, 'b'], slave2 => [2, 0])
 */
class PlParallelIterator implements PlIterator
{
    private $iterators;
    private $ids;
    private $callbacks;

    private $master_id;
    private $master;

    private $over = array();
    private $stepped = array();
    private $current_elts = array();
    private $callback_res = array();

    public function __construct(array $iterators, $callbacks, $master)
    {
        $this->iterators = $iterators;
        $this->master_id = $master;
        $this->master = $iterators[$master];

        $this->ids = array_keys($iterators);

        $v = array_values($callbacks);
        if (is_array($v[0])) {
            $this->callbacks = $callbacks;
        } else {
            $this->callbacks = array();
            foreach ($this->ids as $id) {
                $this->callbacks[$id] = $callbacks;
            }
        }

        foreach ($this->ids as $id) {
            $this->stepped[$id] = false;
            $this->over[$id] = false;
            $this->current_elts[$id] = null;
            $this->callback_res[$id] = null;
        }
    }

    private function step($id)
    {
        if ($this->stepped[$id]) {
            return;
        }

        // Don't do anything if the iterator is at its end
        if ($this->over[$id]) {
            $this->stepped[$id] = true;
            return;
        }

        $it = $this->iterators[$id];
        $nxt = $it->next();
        $this->stepped[$id] = true;
        if ($nxt === null) {
            $this->over[$id] = true;
            $this->current_elts[$id] = null;
            $this->callback_res[$id] = null;
            return;
        }
        $res = call_user_func($this->callbacks[$id], $nxt);
        $this->current_elts[$id] = $nxt;
        $this->callback_res[$id] = $res;
    }

    private function stepAll()
    {
        foreach ($this->ids as $id) {
            $this->step($id);
        }
    }

    public function next()
    {
        $this->stepAll();
        if ($this->current_elts[$this->master_id] === null) {
            return null;
        }

        $res = array();
        $master = $this->callback_res[$this->master_id];
        foreach ($this->ids as $id) {
            if ($this->callback_res[$id] == $master) {
                $res[$id] = $this->current_elts[$id];
                $this->stepped[$id] = false;
            } else {
                $res[$id] = null;
            }
        }

        return $res;
    }

    public function first()
    {
        return $this->master->first();
    }

    public function total()
    {
        return $this->master->total();
    }

    public function last()
    {
        return $this->master->last();
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

// Wrapper class to build a SPL iterator from a PlIterator
class SPLIterator implements Iterator
{
    private $it;
    private $pos;
    private $value;

    public function __construct(PlIterator $it)
    {
        $this->it = $it;
        $this->pos = 0;
        $this->value = $this->it->next();
    }

    public function rewind()
    {
        if ($this->pos != 0) {
            throw new Exception("Rewind not supported on this iterator");
        }
    }

    public function current()
    {
        return $this->value;
    }

    public function key()
    {
        return $this->pos;
    }

    public function next()
    {
        ++$this->pos;
        $this->value = $this->it->next();
    }

    public function valid()
    {
        return !!$this->value;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
