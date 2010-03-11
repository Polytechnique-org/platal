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

class ArrayIteratorTest extends PlTestCase
{
    public function testSimple()
    {
        $it = PlIteratorUtils::fromArray(array(1, 2, 3, 4));
        $this->assertEquals(4, $it->total());

        $this->assertEquals(array('keys' => array(0), 'value' => 1), $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(array('keys' => array(1), 'value' => 2), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(array('keys' => array(2), 'value' => 3), $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertEquals(array('keys' => array(3), 'value' => 4), $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());
    }

    public function testSimpleFlat()
    {
        $it = PlIteratorUtils::fromArray(array(1, 2, 3, 4), 1, true);
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
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
