<?php
require_once("__init__.php");
require_once('include/user.func.inc.php');
require_once('xorg/page.inc.php');

Mock::generate('XorgPage');

class TestOfXOrgUsers extends UnitTestCase {
    function TestOfXOrgUsers() {
        $this->UnitTestCase('XOrg Users Test');
    }

    function test_get_user_login() {
        global $page;
        $page = new MockXorgPage($this, 'index.tpl');
        $page->expectCallCount('trig',5);
        

        $login = get_user_login(18742);
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        $login = get_user_login(100000000);
        $this->assertIdentical($login, false);


        $login = get_user_login("madcoder@melix.org");
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        $login = get_user_login("madcoder@melix.net");
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        $login = get_user_login("devnullr@melix.net");
        $this->assertIdentical($login, false);
       

        $login = get_user_login("madcoder@polytechnique.org");
        $this->assertIdentical($login, 'madcoder');
        $login = get_user_login("madcoder@polytechnique.org", true);
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        $login = get_user_login("madcoder@m4x.org");
        $this->assertIdentical($login, 'madcoder');
        $login = get_user_login("qwerty@polytechnique.org");
        $this->assertIdentical($login, false);
        

        $login = get_user_login("pierre.habouzit.2000");
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        $login = get_user_login("pierre.habouzit.2001");
        $this->assertIdentical($login, false);
        
        
        $login = get_user_login("madcoder@olympe.madism.org");
        $this->assertIdentical($login, 'pierre.habouzit.2000');
        $login = get_user_login("qwerty@olympe.madism.org");
        $this->assertIdentical($login, false);
        
        $page->tally();
        unset($page);
    }
}

$test = &new TestOfXOrgUsers();
$test->run($reporter);
?>
