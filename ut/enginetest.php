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

class TestPlatal extends Platal
{
    public function force_login(PlPage $page) {
        throw new Exception('Force login called in a test');
    }
}
class TestSession extends PlSession
{
    protected function doAuth($level) {
        throw new Exception('Not implemented test method');
    }

    protected function makePerms($perms, $is_admin) {
        throw new Exception('Not implemented test method');
    }

    protected function startSessionAs($user, $level) {
        throw new Exception('Not implemented test method');
    }

    public function loggedLevel() {
        return AUTH_MDP;
    }

    public function startAvailableAuth() {
        return true;
    }

    public function sureLevel() {
        return AUTH_MDP;
    }

    public function tokenAuth($login, $token) {
        throw new Exception('Not implemented test method');
    }
}
define('PL_GLOBALS_CLASS', 'PlGlobals');
define('PL_SESSION_CLASS', 'TestSession');
define('PL_PAGE_CLASS', 'PlPage');
define('PL_LOGGER_CLASS', 'PlLogger');
new TestPlatal();

class TestPage extends PlPage
{
    public function run() {
    }
}

class EngineTest extends PlTestCase
{
    public static function blahCallback(PlPage $page)
    {
        return 'blah';
    }

    public static function blihCallback(PlPage $page)
    {
        $args = func_get_args();
        array_shift($args);
        return 'blih-' . implode('-', $args);
    }

    public static function fooCallback(PlPage $page, $arg1 = null)
    {
        if (is_null($arg1)) {
            return 'foo';
        } else {
            return 'foo-' . $arg1;
        }
    }

    public static function barCallback(PlPage $page, $arg1 = null, $arg2 = null)
    {
        if (is_null($arg1)) {
            return 'bar';
        } else if (is_null($arg2)) {
            return 'bar-' . $arg1;
        } else {
            return 'bar-' . $arg1 . '-' . $arg2;
        }
    }


    public static function hookProvider()
    {
        return array(
            array(PL_WIKI, new PlWikiHook(), array('a')),
            array(PL_WIKI, new PlWikiHook(), array('a', 'b')),
            array(PL_WIKI, new PlWikiHook(), array('a', 'b', 'c')),

            array('blah', new PlStdHook(array('EngineTest', 'blahCallback')), array('a')),
            array('blah', new PlStdHook(array('EngineTest', 'blahCallback')), array('a', 'b')),
            array('blah', new PlStdHook(array('EngineTest', 'blahCallback')), array('a', 'b', 'c')),

            array('blih-', new PlStdHook(array('EngineTest', 'blihCallback')), array('a')),
            array('blih-b', new PlStdHook(array('EngineTest', 'blihCallback')), array('a', 'b')),
            array('blih-b-c', new PlStdHook(array('EngineTest', 'blihCallback')), array('a', 'b', 'c')),

            array('foo', new PlStdHook(array('EngineTest', 'fooCallback')), array('a')),
            array('foo-b', new PlStdHook(array('EngineTest', 'fooCallback')), array('a', 'b')),
            array('foo-b', new PlStdHook(array('EngineTest', 'fooCallback')), array('a', 'b', 'c')),

            array('bar', new PlStdHook(array('EngineTest', 'barCallback')), array('a')),
            array('bar-b', new PlStdHook(array('EngineTest', 'barCallback')), array('a', 'b')),
            array('bar-b-c', new PlStdHook(array('EngineTest', 'barCallback')), array('a', 'b', 'c')),
        );
    }

    /**
     * @dataProvider hookProvider
     */
    public function testHook($res, $hook, $args)
    {
        $page = new TestPage();
        $this->assertEquals($res, $hook->call($page, $args));
    }


    public static function dispatchProvider()
    {
        return array(
            array('blih-', 'test', 'test'),
            array('blih-', 'test', 'test/'),
            array('blih-machin', 'test', 'test/machin'),
            array('blih-machin-truc', 'test', 'test/machin/truc'),

            array('blih-hiboo', 'test', 'test/hiboo'),
            array('foo', 'test/coucou', 'test/coucou'),
            array('foo-', 'test/coucou', 'test/coucou/'),
            array('foo-blah', 'test/coucou', 'test/coucou/blah'),
            array('foo-blah', 'test/coucou', 'test/coucou/blah/truc')
        );
    }

    /**
     * @dataProvider dispatchProvider
     */
    public function testDispatch($res, $expmatched, $path)
    {
        $tree = new PlHookTree();
        $tree->addChildren(array(
            'test' => new PlStdHook(array('EngineTest', 'blihCallback')),
            'test1' => new PlStdHook(array('EngineTest', 'blahCallback')),
            'tes' => new PlStdHook(array('EngineTest', 'blahCallback')),
            'test/coucou' => new PlStdHook(array('EngineTest', 'fooCallback')),
            'test/hook' => new PlStdHook(array('EngineTest', 'barCallback'))
        ));

        $page = new TestPage();
        $p = explode('/', $path);
        list($hook, $matched, $remain, $aliased) = $tree->findChild($p);
        $matched = join('/', $matched);
        $this->assertEquals($expmatched, $matched);
        array_unshift($remain, $matched);
        $this->assertEquals($res, $hook->call($page, $remain));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
