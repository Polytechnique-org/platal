<?php
require_once("__init__.php");
require_once('include/lists.inc.php');

class TestOfList extends UnitTestCase {
    function TestOfList() {
        $this->UnitTestCase('List functions');
    }

    function test_list_sort_owners() {
        $l1 = Array('xavier.merlin.1998@polytechnique.org',
                'xavier.merlin.1991@polytechnique.org',
                'pierre.habouzit.2000@polytechnique.org',
                'yann.chevalier.2000@polytechnique.org',
                'foobar@baz.org');

        $t = list_sort_owners($l1);
        $this->assertIdentical($t[0][0]['l'], 'foobar@baz.org');

        $t1991 = array_values($t[1991]);
        $this->assertIdentical($t1991[0]['l'], 'xavier.merlin.1991');
        
        $t1998 = array_values($t[1998]);
        $this->assertIdentical($t1998[0]['l'], 'xavier.merlin.1998');
        
        $t2000 = array_values($t[2000]);
        $this->assertIdentical($t2000[0]['l'], 'yann.chevalier.2000');
        $this->assertIdentical($t2000[1]['l'], 'pierre.habouzit.2000');
        
        $t = list_sort_owners($l1, false);
        $this->assertIdentical($t[0][0]['l'], 'foobar@baz.org');

        $tC = array_values($t['C']);
        $this->assertIdentical($tC[0]['l'], 'yann.chevalier.2000');
        
        $tH = array_values($t['H']);
        $this->assertIdentical($tH[0]['l'], 'pierre.habouzit.2000');

        $tM = array_values($t['M']);
        $this->assertIdentical($tM[0]['l'], 'xavier.merlin.1991');
        $this->assertIdentical($tM[1]['l'], 'xavier.merlin.1998');
    }
}

$test = &new TestOfList();
$test->run($reporter);
?>
