<?php
require_once("__init__.php");
require_once('include/xorg/errors.inc.php');

class TestOfXOrgErrors extends UnitTestCase {
    function TestOfXOrgErrors() {
        $this->UnitTestCase('XOrgErrors Test');
    }

    function testCreatingXOrgErrors() {
        $errors = new XOrgErrors();
        $this->assertIdentical($errors->errs, Array());
        $this->assertIdentical($errors->failure, false);
    }

    function testTrigger() {
        $errors = new XOrgErrors();
        $errors->trigger("Foo error");
        $this->assertIdentical($errors->errs, Array("Foo error"));
        $this->assertIdentical($errors->failure, false);
    }

    function testFail() {
        $errors = new XOrgErrors();
        $errors->fail("Foo error");
        $this->assertIdentical($errors->errs, Array("Foo error"));
        $this->assertIdentical($errors->failure, true);
    }
}

$test = &new TestOfXOrgErrors();
$test->run(new HtmlReporter());
?>
