<?php
require_once("__init__.php");
    
require_once('shell_tester.php');
require_once('mock_objects.php');

class AllTests extends GroupTest {
    function AllTests() {
        $this->GroupTest('All tests');
        foreach (glob(PATH.'/*_*.php') as $tfile) {
            $this->addTestFile($tfile);
        }
    }
}

$test = &new AllTests();

if (SimpleReporter::inCli())
{
    exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

?>
