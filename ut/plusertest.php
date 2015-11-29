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

class PlUserTest extends PlTestCase
{
    public static function nameProvider()
    {
        return array(
            array('Jean', 'Du Pont', '42', 'jean.du-pont.42'),
            array('Buzz', "L'éclair", 'TOY', 'buzz.leclair.toy'),
            array('a/b+c--d=3!', "I \xe2\x99\xa5 Plat/al", 'Blah', 'ab_c-d3.i-platal.blah'),
        );
    }

    /**
     * @dataProvider nameProvider
     */
    public function testMakeHrid($firstname, $lastname, $category, $hrid)
    {
        $this->assertSame($hrid, PlUser::makeHrid($firstname, $lastname, $category));
    }
}
