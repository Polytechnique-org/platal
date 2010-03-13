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

require_once dirname(__FILE__) . '/../include/test.inc.php';

class HeapTest extends PlTestCase
{
    public static function compare($a, $b)
    {
        return $a - $b;
    }

    public function testHeap()
    {
        $heap = new PlHeap(array('HeapTest', 'compare'));
        $this->assertEquals(0, $heap->count());
        $this->assertNull($heap->pop());

        $heap->push(1);
        $this->assertEquals(1, $heap->count());
        $this->assertEquals(1, $heap->pop());
        $this->assertEquals(0, $heap->count());
        $this->assertNull($heap->pop());

        $heap->push(2);
        $heap->push(1);
        $heap->push(4);
        $heap->push(3);
        $this->assertEquals(4, $heap->count());
        $this->assertEquals(1, $heap->pop());
        $this->assertEquals(3, $heap->count());
        $this->assertEquals(2, $heap->pop());
        $this->assertEquals(2, $heap->count());
        $heap->push(-1);
        $this->assertEquals(3, $heap->count());
        $this->assertEquals(-1, $heap->pop());
        $this->assertEquals(2, $heap->count());
        $this->assertEquals(3, $heap->pop());
        $this->assertEquals(1, $heap->count());
        $this->assertEquals(4, $heap->pop());
        $this->assertEquals(0, $heap->count());
        $this->assertNull($heap->pop());
    }

    public function testHeapIt()
    {
        $heap = new PlHeap(array('HeapTest', 'compare'));
        $heap->push(2);
        $heap->push(1);
        $heap->push(4);
        $heap->push(3);

        $it = $heap->iterator();
        $this->assertEquals(4, $it->total());

        $this->assertEquals(1, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(2, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(3, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
        $this->assertEquals(4, $heap->count());
    }

    public function testMergeSortedIterator()
    {
        $its = array();
        $its[] = PlIteratorUtils::fromArray(array(2, 4, 8, 16), 1, true);
        $its[] = PlIteratorUtils::fromArray(array(3, 9, 27), 1, true);
        $its[] = PlIteratorUtils::fromArray(array(4, 16, 32), 1, true);
        $it = PlIteratorUtils::merge($its, array('HeapTest', 'compare'));
        $this->assertEquals(10, $it->total());

        $this->assertEquals(2, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(3, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(8, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(9, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(16, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(16, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(27, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(32, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testMergeUnsortedIterator()
    {
        $its = array();
        $its[] = PlIteratorUtils::fromArray(array(8, 4, 16, 2), 1, true);
        $its[] = PlIteratorUtils::fromArray(array(3, 27, 9), 1, true);
        $its[] = PlIteratorUtils::fromArray(array(32, 4, 16), 1, true);
        $it = PlIteratorUtils::merge($its, array('HeapTest', 'compare'), false);
        $this->assertEquals(10, $it->total());

        $this->assertEquals(2, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(3, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(8, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(9, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(16, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(16, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(27, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(32, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
