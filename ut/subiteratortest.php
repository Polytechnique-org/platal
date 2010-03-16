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

class SubIteratorTest extends PlTestCase
{
    public function testSimple()
    {
        $iter = PlIteratorUtils::fromArray(array('a' => 1,
                                                 'b' => 1,
                                                 'c' => 2,
                                                 'd' => 3));
        $cb = PlIteratorUtils::arrayValueCallback('value');

        $it = PlIteratorUtils::subIterator($iter, $cb);

        $subit = $it->next();
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $v = $subit->next();
        $this->assertSame(1, $subit->value());
        $this->assertSame(array('keys' => array('a'), 'value' => 1), $v);

        $this->assertTrue($subit->first());
        $this->assertFalse($subit->last());

        $this->assertSame(array('keys' => array('b'), 'value' => 1), $subit->next());
        $this->assertFalse($subit->first());
        $this->assertTrue($subit->last());

        $this->assertNull($subit->next());

        $this->assertTrue($it->first());

        $subit2 = $it->next();
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(2, $subit2->value());
        $this->assertSame(array('keys' => array('c'), 'value' => 2), $subit2->next());

        $this->assertTrue($subit2->first());
        $this->assertTrue($subit2->last());

        $this->assertNull($subit2->next());

        $subit3 = $it->next();
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(3, $subit3->value());
        $this->assertSame(array('keys' => array('d'), 'value' => 3), $subit3->next());

        $this->assertTrue($subit3->first());
        $this->assertTrue($subit3->last());

        $this->assertNull($subit3->next());

        $this->assertTrue($it->last());
        $this->assertNull($it->next());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
