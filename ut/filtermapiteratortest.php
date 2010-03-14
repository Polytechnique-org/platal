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

class FilterMapIteratorTest extends PlTestCase
{
    public static function filter($e)
    {
        return $e > 8;
    }

    public function testFilter()
    {
        $it = PlIteratorUtils::fromArray(array(1, 10, 3, 9, 42, -23, 9, 8), 1, true);
        $it = PlIteratorUtils::filter($it, array('FilterMapIteratorTest', 'filter'));
        $this->assertLessThanOrEqual(8, $it->total());
        $this->assertGreaterThanOrEqual(4, $it->total());

        $this->assertSame(10, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(9, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(42, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(9, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }

    public static function map($e)
    {
        return $e * 2;
    }

    public function testMap()
    {
        $it = PlIteratorUtils::fromArray(array(1, 2, 3, 4, 7, 512), 1, true);
        $it = PlIteratorUtils::map($it, array('FilterMapIteratorTest', 'map'));
        $this->assertSame(6, $it->total());

        $this->assertSame(2, $it->next());
        $this->assertTrue($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(4, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(6, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(8, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(14, $it->next());
        $this->assertFalse($it->first());
        $this->assertFalse($it->last());

        $this->assertSame(1024, $it->next());
        $this->assertFalse($it->first());
        $this->assertTrue($it->last());

        $this->assertNull($it->next());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
