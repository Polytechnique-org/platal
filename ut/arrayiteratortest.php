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

require_once dirname(__FILE__) . '/../include/test.inc.php';

class ArrayIteratorTest extends PlTestCase
{
    public function testSimple()
    {
        $it = PlIteratorUtils::fromArray(array(1, 2, 3, 4));
        $this->assertSame(4, $it->total());

        $this->assertSame(array('keys' => array(0), 'value' => 1), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array(1), 'value' => 2), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array(2), 'value' => 3), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array(3), 'value' => 4), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testSimpleFlat()
    {
        $it = PlIteratorUtils::fromArray(array(1, 2, 3, 4), 1, true);
        $this->assertSame(4, $it->total());

        $this->assertSame(1, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(2, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(3, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testAssoc()
    {
        $it = PlIteratorUtils::fromArray(array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4));
        $this->assertSame(4, $it->total());

        $this->assertSame(array('keys' => array('a'), 'value' => 1), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array('b'), 'value' => 2), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array('c'), 'value' => 3), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array('d'), 'value' => 4), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testAssocFlat()
    {
        $it = PlIteratorUtils::fromArray(array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4), 1, true);
        $this->assertSame(4, $it->total());

        $this->assertSame(1, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(2, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(3, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testDepth()
    {
        $it = PlIteratorUtils::fromArray(array(array(1, 2), array(3, 4)), 1);
        $this->assertSame(2, $it->total());

        $this->assertSame(array('keys' => array(0), 'value' => array(1, 2)), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array(1), 'value' => array(3, 4)), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());


        $it = PlIteratorUtils::fromArray(array(array(1, 2), array(3, 4)), 2);
        $this->assertSame(4, $it->total());

        $this->assertSame(array('keys' => array(0, 0), 'value' => 1), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array(0, 1), 'value' => 2), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array(1, 0), 'value' => 3), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array(1, 1), 'value' => 4), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testDepthFlat()
    {
        $it = PlIteratorUtils::fromArray(array(array(1, 2), array(3, 4)), 1, true);
        $this->assertSame(2, $it->total());

        $this->assertSame(array(1, 2), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(3, 4), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());


        $it = PlIteratorUtils::fromArray(array(array(1, 2), array(3, 4)), 2, true);
        $this->assertSame(4, $it->total());

        $this->assertSame(1, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(2, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(3, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testDepthAssoc()
    {
        $it = PlIteratorUtils::fromArray(array('a' => array('b' => 1, 'c' => 2), 'd' => array('e' => 3, 'f' => 4)), 1);
        $this->assertSame(2, $it->total());

        $this->assertSame(array('keys' => array('a'), 'value' => array('b' => 1, 'c' => 2)), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array('d'), 'value' => array('e' => 3, 'f' => 4)), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());


        $it = PlIteratorUtils::fromArray(array('a' => array('b' => 1, 'c' => 2), 'd' => array('e' => 3, 'f' => 4)), 2);
        $this->assertSame(4, $it->total());

        $this->assertSame(array('keys' => array('a', 'b'), 'value' => 1), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array('a', 'c'), 'value' => 2), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array('d', 'e'), 'value' => 3), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('keys' => array('d', 'f'), 'value' => 4), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testDepthAssocFlat()
    {
        $it = PlIteratorUtils::fromArray(array('a' => array('b' => 1, 'c' => 2), 'd' => array('e' => 3, 'f' => 4)), 1, true);
        $this->assertSame(2, $it->total());

        $this->assertSame(array('b' => 1, 'c' => 2), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array('e' => 3, 'f' => 4), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());


        $it = PlIteratorUtils::fromArray(array('a' => array('b' => 1, 'c' => 2), 'd' => array('e' => 3, 'f' => 4)), 2, true);
        $this->assertSame(4, $it->total());

        $this->assertSame(1, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(2, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(3, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
