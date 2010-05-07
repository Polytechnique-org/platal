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

class DateParserTest extends PlTestCase
{
    protected function assertSameDate($d1, $d2)
    {
        $this->assertTrue($d1 instanceof DateTime);
        $this->assertTrue($d2 instanceof DateTime);
        $this->assertEquals($d1->format('c'), $d2->format('c'));
    }

    protected function assertNotSameDate($d1, $d2)
    {
        $this->assertTrue($d1 instanceof DateTime);
        $this->assertTrue($d2 instanceof DateTime);
        $this->assertNotEquals($d1->format('c'), $d2->format('c'));
    }

    public function testNumeric()
    {
        $this->assertSameDate(make_datetime('12000101'), new DateTime('1200-01-01'));
        $this->assertSameDate(make_datetime('20100101'), new DateTime('2010-01-01'));
        $this->assertSameDate(make_datetime('20100101124213'), new DateTime('2010-01-01 12:42:13'));
        $this->assertSameDate(make_datetime('1273232546'), new DateTime('@1273232546'));
        $this->assertSameDate(make_datetime(1273232546), new DateTime('@1273232546'));

        $this->assertNotSameDate(make_datetime('12000101'), new DateTime('1200-01-02'));
        $this->assertNotSameDate(make_datetime('20100101'), new DateTime('2010-01-02'));
        $this->assertNotSameDate(make_datetime('20100101124213'), new DateTime('2010-01-01 12:42:14'));
        $this->assertNotSameDate(make_datetime('1273232546'), new DateTime('@1273232547'));
        $this->assertNotSameDate(make_datetime(1273232546), new DateTime('@1273232547'));
    }

    public function testText()
    {
        $this->assertSameDate(make_datetime('2010-01-01'), new DateTime('2010-01-01'));
        $this->assertSameDate(make_datetime('1600-01-01'), new DateTime('1600-01-01'));
        $this->assertSameDate(make_datetime('2010-01-01 08:09:10'), new DateTime('2010-01-01 08:09:10'));

        $this->assertNotSameDate(make_datetime('2010-01-01'), new DateTime('2010-01-02'));
        $this->assertNotSameDate(make_datetime('1600-01-01'), new DateTime('1600-01-02'));
        $this->assertNotSameDate(make_datetime('2010-01-01 08:09:10'), new DateTime('2010-01-01 08:09:11'));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
