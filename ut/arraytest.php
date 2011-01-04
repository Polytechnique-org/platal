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

class ArrayTest extends PlTestCase
{
    public static function flattenProvider()
    {
        return array(
            array(array(1, 2, 3, 4), array(1, 2, 3, 4)),
            array(array(1, 2, array(3, 4)), array(1, 2, 3, 4)),
            array(array(array(1, 2), array(3), array(4)), array(1, 2, 3, 4)),
            array(array(array(array(1, 2)), array(3), 4), array(1, 2, 3, 4))
        );
    }

    /**
     * @dataProvider flattenProvider
     */
    public function testFlatten(array $src, array $res)
    {
        $this->assertSame($res, pl_flatten($src));
    }
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
