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

class FormatBlah implements XDBFormat
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function format()
    {
        return 'blah' . $this->text . 'blah';
    }
}

class XDBTest extends PlTestCase
{
    public function testEscapeString()
    {
        $this->assertSame("'blah'", XDB::format('{?}', 'blah'));
        $this->assertSame("'blah\\''", XDB::format('{?}', "blah'"));
        $this->assertSame("'bl\\'ah'", XDB::format('{?}', "bl'ah"));
        $this->assertSame("'\\'blah\\''", XDB::format('{?}', "'blah'"));
    }

    public function testEscapeInt()
    {
        $this->assertSame("1", XDB::format('{?}', 1));
    }

    public function testEscapeFlagSet()
    {
        $flagset = new PlFlagSet();
        $flagset->addFlag('toto');
        $this->assertSame("'toto'", XDB::format('{?}', $flagset));
        $flagset->addFlag('titi');
        $this->assertSame("'toto,titi'", XDB::format('{?}', $flagset));
        $flagset->addFlag('titi');
        $this->assertSame("'toto,titi'", XDB::format('{?}', $flagset));
    }

    public function testEscapeArray()
    {
        $this->assertSame("(1, 'toto')", XDB::format('{?}', array(1, 'toto')));
    }

    public function testEscapeFormat()
    {
        $this->assertSame('blahblah', XDB::format('{?}', new FormatBlah('')));
        $this->assertSame('blahblahblah', XDB::format('{?}', new FormatBlah('blah')));
        $this->assertSame('blahBloumblah', XDB::format('{?}', new FormatBlah('Bloum')));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
