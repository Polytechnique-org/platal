<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

class MiniWikiTest extends PlTestCase
{
    public static function miniwikiProvider()
    {
        return array(
            array("test\r\nwith CR+LF", "test with CR+LF", "test\nwith CR+LF"),
            array("line1\\\\\nline2", "line1<br /> line2", "line1\nline2"),
            array("par1\n\npar2", "<p>par1</p><p>par2</p>", "par1\n\npar2"),
            array(
                "Table:\n||A1||B1||\n||A2||B2||",
                '<p>Table:</p><table class="tinybicol"><tr><td>A1</td><td>B1</td></tr><tr><td>A2</td><td>B2</td></tr></table><p></p>',
                "Table:\n|A1|B1|\n|A2|B2|"
            ),
            array(
                "List:\n* item 1\n* item 2\n* item 3\n",
                "<p>List:</p> <ul><li> item 1</li><li> item 2</li><li> item 3</li></ul> <p></p>",
                "List:\n\xc2\xa0- item 1\n\xc2\xa0- item 2\n\xc2\xa0- item 3"
            ),
            array(
                "Enum:\n# item 1\n# item 2\n# item 3\n",
                "<p>Enum:</p> <ol><li> item 1</li><li> item 2</li><li> item 3</li></ol> <p></p>",
                "Enum:\n# item 1\n# item 2\n# item 3",
            ),
            array("'''bold'''", "<strong>bold</strong>", "*bold*"),
            array("''italic''", "<em>italic</em>", "/italic/"),
            array("'+big+'", "<big>big</big>", "*big*"),
            array("'-small-'", "<small>small</small>", "small"),
            array("'^super^'", "<sup>super</sup>", "super"),
            array("'_sub_'", "<sub>sub</sub>", "sub"),
            array("{+under+}", "<ins>under</ins>", "_under_"),
            array("{-strike-}", "<del>strike</del>", "-strike-"),
            array(
                "%#f00%Red%% and %yellow%Yellow%%",
                "<span style='color: #f00;'>Red</span> and <span style='color: yellow;'>Yellow</span>",
                "Red and Yellow"
            ),
            array(
                "[+big1+] [++big2++] [+++big3+++]",
                "<span style='font-size:120%'>big1</span> <span style='font-size:144%'>big2</span> <span style='font-size:173%'>big3</span>",
                "big1 big2 big3"
            ),
            array(
                "part1\n----\npart2\n-------\npart3",
                "part1 <hr/> part2 <hr/> part3",
                "part1\n-- \n\npart2\n-- \n\npart3"
            ),
            array(
                "https://polytechnique.org",
                '<a href="https://polytechnique.org">https://polytechnique.org</a>',
                '<https://polytechnique.org>'
            ),
            array(
                "www.polytechnique.org",
                '<a href="http://www.polytechnique.org">www.polytechnique.org</a>',
                '<http://www.polytechnique.org>'
            ),
            array(
                "[[https://www.polytechnique.org|The website]]",
                '<a href="https://www.polytechnique.org">The website</a>',
                'The website <https://www.polytechnique.org>'
            ),
            array(
                "test@example.com",
                '<a href="mailto:test@example.com">test@example.com</a>',
                '<test@example.com>'
            ),
        );
    }

    public function testHelp()
    {
        $this->assertTrue(count(MiniWiki::help()) > 0);
    }


    /**
     * @dataProvider miniwikiProvider
     */
    public function testHTML($wiki, $html, $text)
    {
        $this->assertSame($html, MiniWiki::WikiToHTML($wiki));
    }

    /**
     * @dataProvider miniwikiProvider
     */
    public function testText($wiki, $html, $text)
    {
        $this->assertSame($text, MiniWiki::WikiToText($wiki));
    }

    public function testTitle()
    {
        $wikiTitle = "!title1\n!!title11\n!!!title111\n!!title12\n!title2";

        // Test without activating title
        $this->assertSame(
            '!title1&nbsp;!!title11&nbsp;!!!title111&nbsp;!!title12&nbsp;!title2',
            MiniWiki::WikiToHTML($wikiTitle));
        $this->assertSame($wikiTitle, MiniWiki::WikiToText($wikiTitle));

        // Test with title
        $this->assertSame(
            '<h1>title1</h1> <h2>title11</h2> <h3>title111</h3> <h2>title12</h2> <h1>title2</h1>',
            MiniWiki::WikiToHTML($wikiTitle, true));
        $this->assertSame(
            "title1\ntitle11\ntitle111\ntitle12\ntitle2",
            MiniWiki::WikiToText($wikiTitle, false, 0, 0, true));
    }
}
