<?php
require_once("__init__.php");
require_once('include/xorg/env.inc.php');

class TestOfEnv extends UnitTestCase {
    function TestOfEnv() {
        $this->UnitTestCase('Env access');
    }

    function test_get() {
        $_REQUEST['foo'] = 'baz';
        $this->assertIdentical(Env::get('foo'), 'baz');
        
        $_REQUEST['foo'] = 123;
        $this->assertIdentical(Env::get('foo'), '123');

        $_REQUEST['foo'] = '123';
        $this->assertIdentical(Env::get('foo'), '123');

        $this->assertIdentical(Env::get('bar'), '');
        $this->assertIdentical(Env::get('bar', 'bar'), 'bar');
    }
    
    function test_getMixed() {
        $_REQUEST['foo'] = 'baz';
        $this->assertIdentical(Env::getMixed('foo'), 'baz');
        
        $_REQUEST['foo'] = 123;
        $this->assertIdentical(Env::getMixed('foo'), 123);

        $_REQUEST['foo'] = Array(1,'a');
        $this->assertIdentical(Env::getMixed('foo'), Array(1,'a'));

        $this->assertIdentical(Env::getMixed('bar'), '');
        $this->assertIdentical(Env::getMixed('bar', 'bar'), 'bar');
    }
    
    function test_getBool() {
        $_REQUEST['foo'] = 'baz';
        $this->assertIdentical(Env::getBool('foo'), true);
        
        $_REQUEST['foo'] = 123;
        $this->assertIdentical(Env::getBool('foo'), true);

        $_REQUEST['foo'] = '123';
        $this->assertIdentical(Env::getBool('foo'), true);

        $this->assertIdentical(Env::getBool('bar'), false);
        $this->assertIdentical(Env::getBool('bar', true), true);
    }
    
    function test_getInt() {
        $_REQUEST['foo'] = 'baz';
        $this->assertIdentical(Env::getInt('foo'), 0);
        $this->assertIdentical(Env::getInt('foo', 10), 10);
        
        $_REQUEST['foo'] = 123;
        $this->assertIdentical(Env::getInt('foo'), 123);

        $_REQUEST['foo'] = '123';
        $this->assertIdentical(Env::getInt('foo'), 123);

        $this->assertIdentical(Env::getInt('bar'), 0);
        $this->assertIdentical(Env::getInt('bar', 123), 123);
    }
    
    function test_kill() {
        $_REQUEST['foo'] = 'baz';
        Env::kill('foo');
        $this->assertFalse(isset($_REQUEST['foo']));
    }
    
    function test_other_class() {
        $_POST['foo'] = 'baz';
        Post::kill('foo');
        $this->assertFalse(isset($_POST['foo']));
        
        $_GET['foo'] = 'baz';
        Get::kill('foo');
        $this->assertFalse(isset($_GET['foo']));
    }
}

$test = &new TestOfEnv();
$test->run($reporter);
?>
