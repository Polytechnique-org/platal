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

class ParallelIteratorTest extends PlTestCase
{
    public function testSameCallback()
    {
        $m = PlIteratorUtils::fromArray(array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5));
        $s1 = PlIteratorUtils::fromArray(array('X' => 1, 'Y' => 2, 'Z' => 4));
        $s2 = PlIteratorUtils::fromArray(array('aaa' => 2, 'bbb' => 3, 'ccc' => 6));

        $cb = PlIteratorUtils::arrayValueCallback('value');

        $its = array(0 => $m, 1 => $s1, 2 => $s2);
        $cbs = array(0 => $cb, 1 => $cb, 2 => $cb);

        $it = PlIteratorUtils::parallelIterator($its, $cbs, 0);

        $this->assertSame(5, $it->total());

        $this->assertSame(array(0 => array('keys' => array('a'), 'value' => 1),
                                1 => array('keys' => array('X'), 'value' => 1),
                                2 => null), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(0 => array('keys' => array('b'), 'value' => 2),
                                1 => array('keys' => array('Y'), 'value' => 2),
                                2 => array('keys' => array('aaa'), 'value' => 2)
                            ), $it->next());

        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(0 => array('keys' => array('c'), 'value' => 3),
                                1 => null,
                                2 => array('keys' => array('bbb'), 'value' => 3),
                            ), $it->next());

        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(0 => array('keys' => array('d'), 'value' => 4),
                                1 => array('keys' => array('Z'), 'value' => 4),
                                2 => null,
                            ), $it->next());

        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(0 => array('keys' => array('e'), 'value' => 5),
                                1 => null,
                                2 => null,
                            ), $it->next());

        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public function testOneCallback()
    {
        $m = PlIteratorUtils::fromArray(array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5));
        $s1 = PlIteratorUtils::fromArray(array('X' => 1, 'Y' => 2, 'Z' => 4));
        $s2 = PlIteratorUtils::fromArray(array('aaa' => 2, 'bbb' => 3, 'ccc' => 6));

        $cb = PlIteratorUtils::arrayValueCallback('value');

        $its = array(0 => $m, 1 => $s1, 2 => $s2);

        $it = PlIteratorUtils::parallelIterator($its, $cb, 0);

        $this->assertSame(5, $it->total());

        $this->assertSame(array(0 => array('keys' => array('a'), 'value' => 1),
                                1 => array('keys' => array('X'), 'value' => 1),
                                2 => null), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(0 => array('keys' => array('b'), 'value' => 2),
                                1 => array('keys' => array('Y'), 'value' => 2),
                                2 => array('keys' => array('aaa'), 'value' => 2)
                            ), $it->next());

        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(0 => array('keys' => array('c'), 'value' => 3),
                                1 => null,
                                2 => array('keys' => array('bbb'), 'value' => 3),
                            ), $it->next());

        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(0 => array('keys' => array('d'), 'value' => 4),
                                1 => array('keys' => array('Z'), 'value' => 4),
                                2 => null,
                            ), $it->next());

        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(array(0 => array('keys' => array('e'), 'value' => 5),
                                1 => null,
                                2 => null,
                            ), $it->next());

        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
