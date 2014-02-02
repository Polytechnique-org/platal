<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

class NameTest extends PlTestCase
{
    private static function checkPlatal()
    {
        global $platal;

        if (is_null($platal)) {
            $platal = new Xorg();
        }
    }

    public static function nameProvider()
    {
        return array(
            array('jacob', 'Jacob', 'Jacob'),
            array('pierre-alexis', 'Pierre-Alexis', 'Pierre-Alexis'),
            array('de gaulle', 'de Gaulle', 'Gaulle'),
            array("d'abcdef", "d'Abcdef", "Abcdef"),
            array("o'brian", "O'Brian", "O'Brian"),
            array("malloc'h", "Malloc'h", "Malloc'h"),
            array("D'IRUMBERRY DE SALABERRY", "d'Irumberry de Salaberry", "Irumberry de Salaberry"),
            array("LE BOUCHER D'HEROUVILLE", "Le Boucher d'Herouville", "Le Boucher d'Herouville"),
            array("ÖZTÜRK-N'Dong-Nzue", "Öztürk-N'Dong-Nzue", "Öztürk-N'Dong-Nzue"),
            array('MAC NAMARA', 'Mac Namara', 'MacNamara'),
            array('MACNAMARA', 'Macnamara', 'Macnamara'),
            array('MCNAMARA', 'Mcnamara', 'Macnamara')
        );
    }

    /**
     * @dataProvider nameProvider
     */
    public function testName($name, $capitalized_name, $sort_name)
    {
        self::checkPlatal();
        require_once 'name.func.inc.php';

        $test = capitalize_name($name);
        $this->assertEquals($test, $capitalized_name);
        $this->assertEquals(build_sort_name('', $test), $sort_name);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
