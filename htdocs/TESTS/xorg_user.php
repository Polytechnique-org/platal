<?php
require_once("__init__.php");
require_once("xorg.globals.inc.php");
XorgGlobals::init();

require_once('include/user.func.inc.php');

class TestOfXOrgUsers extends UnitTestCase {
    function TestOfXOrgUsers() {
        $this->UnitTestCase('XOrg Users Test');
    }

    function test_get_user_login() {
        global $page;

        $login = get_user_login(18742);
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        
        $login = get_user_login(100000000);
        $this->assertIdentical($login, false);


        $login = get_user_login("madcoder@melix.org");
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        
        $login = get_user_login("madcoder@melix.net");
        $this->assertIdentical($login, 'pierre.habouzit.2000');
       

        $login = get_user_login("madcoder@polytechnique.org");
        $this->assertIdentical($login, 'madcoder');
        
        $login = get_user_login("madcoder@m4x.org");
        $this->assertIdentical($login, 'madcoder');
        
        
        $login = get_user_login("pierre.habouzit.2000");
        $this->assertIdentical($login, 'pierre.habouzit.2000');
       
        $login = get_user_login("pierre.habouzit.2001");
        $this->assertIdentical($login, false);
        
        
        $login = get_user_login("madcoder@olympe.madism.org");
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        
        unset($page);
    }
}

$test = &new TestOfXOrgUsers();
$test->run(new HtmlReporter());
?>
