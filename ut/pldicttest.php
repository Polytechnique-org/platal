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

class PlDictTest extends PlTestCase
{
    private function checkEmpty(PlDict $dict, $key)
    {
        $this->assertSame(0, $dict->count());

        $this->assertFalse($dict->has($key));
        $this->assertTrue($dict->blank($key));
        $this->assertNull($dict->v($key));
        $this->assertSame(0, $dict->i($key));
        $this->assertSame('', $dict->s($key));
        $this->assertSame('', $dict->t($key));
        $this->assertFalse($dict->b($key));
        $this->assertSame(array(), $dict->dict());
    }

    public function testEmpty()
    {
        $dict = new PlDict();
        $this->checkEmpty($dict, 'key');
    }

    public function testPrefilled()
    {
        $dict = new PlDict(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                 'd' => '  ', 'e' => 42, 'f' => array('a', 'b')));
        $this->assertSame(6, $dict->count());
        $this->assertTrue($dict->has('a'));
        $this->assertTrue($dict->has('b'));
        $this->assertTrue($dict->has('c'));
        $this->assertTrue($dict->has('d'));
        $this->assertTrue($dict->has('e'));
        $this->assertTrue($dict->has('f'));
        $this->assertFalse($dict->has('g'));
        $this->assertSame(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                'd' => '  ', 'e' => 42, 'f' => array('a', 'b')), $dict->dict());
    }

    public function testInsertion()
    {
        $dict = new PlDict();
        $this->assertFalse($dict->has('a'));
        $dict->set('a', '12');
        $this->assertTrue($dict->has('a'));
        $this->assertFalse($dict->has('b'));
        $dict->set('b', false);
        $this->assertTrue($dict->has('b'));
        $this->assertFalse($dict->has('c'));
        $dict->set('c', "\n\n hello world !   ");
        $this->assertTrue($dict->has('c'));
        $this->assertFalse($dict->has('d'));
        $dict->set('d', '  ');
        $this->assertTrue($dict->has('d'));
        $this->assertFalse($dict->has('e'));
        $dict->set('e', 42);
        $this->assertTrue($dict->has('e'));
        $this->assertFalse($dict->has('f'));
        $dict->set('f', array('a', 'b'));
        $this->assertTrue($dict->has('f'));
        $this->assertFalse($dict->has('g'));

        $this->assertSame(6, $dict->count());
        $this->assertSame(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                'd' => '  ', 'e' => 42, 'f' => array('a', 'b')), $dict->dict());

        $dict = new PlDict(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   "));
        $this->assertSame(3, $dict->count());
        $dict->set('d', '  ');
        $dict->set('e', 42);
        $dict->set('f', array('a', 'b'));

        $this->assertSame(6, $dict->count());
        $this->assertSame(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                'd' => '  ', 'e' => 42, 'f' => array('a', 'b')), $dict->dict());
    }

    public function testKill()
    {
        $dict = new PlDict(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                 'd' => '  ', 'e' => 42, 'f' => array('a', 'b')));
        $this->assertSame(6, $dict->count());
        $this->assertTrue($dict->has('a'));
        $this->assertTrue($dict->has('b'));
        $this->assertTrue($dict->has('c'));
        $this->assertTrue($dict->has('d'));
        $this->assertTrue($dict->has('e'));
        $this->assertTrue($dict->has('f'));
        $this->assertFalse($dict->has('g'));

        $dict->kill('a');
        $this->assertSame(5, $dict->count());
        $this->assertFalse($dict->has('a'));
        $this->assertTrue($dict->has('b'));
        $this->assertTrue($dict->has('c'));
        $this->assertTrue($dict->has('d'));
        $this->assertTrue($dict->has('e'));
        $this->assertTrue($dict->has('f'));
        $this->assertFalse($dict->has('g'));

        $dict->kill('g');
        $this->assertSame(5, $dict->count());
        $this->assertFalse($dict->has('a'));
        $this->assertTrue($dict->has('b'));
        $this->assertTrue($dict->has('c'));
        $this->assertTrue($dict->has('d'));
        $this->assertTrue($dict->has('e'));
        $this->assertTrue($dict->has('f'));
        $this->assertFalse($dict->has('g'));

        $this->assertSame(array('b' => false, 'c' => "\n\n hello world !   ",
                                'd' => '  ', 'e' => 42, 'f' => array('a', 'b')), $dict->dict());
    }

    public function testMerge()
    {
        $dict = new PlDict(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   "));
        $this->assertSame(3, $dict->count());
        $dict->merge(array('d' => '  ', 'e' => 42, 'f' => array('a', 'b')));

        $this->assertSame(6, $dict->count());
        $this->assertSame(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                'd' => '  ', 'e' => 42, 'f' => array('a', 'b')), $dict->dict());
    }

    public function testInt()
    {
        $dict = new PlDict(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                 'd' => '  ', 'e' => 42, 'f' => array('a', 'b')));
        $this->assertSame(6, $dict->count());

        $this->assertSame(12, $dict->i('a', 42));
        $this->assertSame(42, $dict->i('b', 42));
        $this->assertSame(0, $dict->i('b'));
        $this->assertSame(42, $dict->i('c', 42));
        $this->assertSame(0, $dict->i('c'));
        $this->assertSame(42, $dict->i('d', 42));
        $this->assertSame(0, $dict->i('d'));
        $this->assertSame(42, $dict->i('e', 42));
        $this->assertSame(42, $dict->i('e'));
        $this->assertSame(42, $dict->i('f', 42));
        $this->assertSame(0, $dict->i('f'));
    }

    public function testString()
    {
        $dict = new PlDict(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                 'd' => '  ', 'e' => 42, 'f' => array('a', 'b')));
        $this->assertSame(6, $dict->count());

        $this->assertSame('12', $dict->s('a', ' blah'));
        $this->assertSame('12', $dict->s('a'));
        $this->assertSame('12', $dict->t('a', ' blah'));
        $this->assertSame('12', $dict->t('a'));

        $this->assertSame('', $dict->s('b', ' blah'));
        $this->assertSame('', $dict->s('b', ''));
        $this->assertSame('', $dict->t('b', ' blah'));
        $this->assertSame('', $dict->t('b', ''));

        $this->assertSame("\n\n hello world !   ", $dict->s('c', ' blah'));
        $this->assertSame("\n\n hello world !   ", $dict->s('c'));
        $this->assertSame("hello world !", $dict->t('c', ' blah'));
        $this->assertSame("hello world !", $dict->t('c'));

        $this->assertSame('  ', $dict->s('d', ' blah'));
        $this->assertSame('  ', $dict->s('d'));
        $this->assertSame('', $dict->t('d', ' blah'));
        $this->assertSame('', $dict->t('d'));

        $this->assertSame('42', $dict->s('e', ' blah'));
        $this->assertSame('42', $dict->s('e'));
        $this->assertSame('42', $dict->t('e', ' blah'));
        $this->assertSame('42', $dict->t('e'));

        $this->assertSame('Array', $dict->s('f', ' blah'));
        $this->assertSame('Array', $dict->s('f'));
        $this->assertSame('Array', $dict->t('f', ' blah'));
        $this->assertSame('Array', $dict->t('f'));

        $this->assertSame(' blah', $dict->s('g', ' blah'));
        $this->assertSame('', $dict->s('g'));
        $this->assertSame('blah', $dict->t('g', ' blah'));
        $this->assertSame('', $dict->t('g'));
    }

    public function testValue()
    {
        $dict = new PlDict(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                 'd' => '  ', 'e' => 42, 'f' => array('a', 'b')));
        $this->assertSame(6, $dict->count());

        $this->assertSame('12', $dict->v('a'));
        $this->assertSame('12', $dict->v('a', 'blah'));
        $this->assertSame(false, $dict->v('b'));
        $this->assertSame(false, $dict->v('b', 'blah'));
        $this->assertSame("\n\n hello world !   ", $dict->v('c'));
        $this->assertSame("\n\n hello world !   ", $dict->v('c', 'blah'));
        $this->assertSame('  ', $dict->v('d'));
        $this->assertSame('  ', $dict->v('d', 'blah'));
        $this->assertSame(42, $dict->v('e'));
        $this->assertSame(42, $dict->v('e', 'blah'));
        $this->assertSame(array('a', 'b'), $dict->v('f'));
        $this->assertSame(array('a', 'b'), $dict->v('f', 'blah'));
        $this->assertNull($dict->v('g'));
        $this->assertSame('blah', $dict->v('g', 'blah'));
    }

    public function testBlank()
    {
        $dict = new PlDict(array('a' => '12', 'b' => false, 'c' => "\n\n hello world !   ",
                                 'd' => '  ', 'e' => 42, 'f' => array('a', 'b')));
        $this->assertSame(6, $dict->count());

        $this->assertFalse($dict->blank('a'));
        $this->assertFalse($dict->blank('a', true));
        $this->assertTrue($dict->blank('b'));
        $this->assertTrue($dict->blank('b', true));
        $this->assertFalse($dict->blank('c'));
        $this->assertFalse($dict->blank('c', true));
        $this->assertTrue($dict->blank('d'));
        $this->assertFalse($dict->blank('d', true));
        $this->assertFalse($dict->blank('e'));
        $this->assertFalse($dict->blank('e', true));
        $this->assertFalse($dict->blank('f'));
        $this->assertFalse($dict->blank('f', true));
        $this->assertTrue($dict->blank('g'));
        $this->assertTrue($dict->blank('g', true));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
