<?php
require_once("__init__.php");
    
require_once('shell_tester.php');
require_once('mock_objects.php');

class MyReporter extends SimpleReporter {
    function MyReporter() {
        HtmlReporter::sendNoCacheHeaders();
        $this->SimpleReporter();
    }

    function paintFooter($test_name) {
        global $tfile;
        $class = $this->getFailCount() + $this->getExceptionCount() > 0 ?  "red" : "green";
        print "<div class='$class'>";
        print "<h1><a href='".basename($tfile)."'>$test_name</a> (";
        print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
        print ")</h1>\n";
        print "<strong>" . $this->getPassCount() . "</strong> passes | ";
        print "<strong>" . $this->getFailCount() . "</strong> fails | ";
        print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
        print "</div>\n";
        flush();
    }
}

echo <<<EOF
<html>
  <head>
    <title>ALL TESTS</title>
    <style type="text/css">
      body { padding: 0px; margin: 0px;}
      div { float: left; color: white; padding: 1ex; border: 1px dashed white; }
      h1  { padding: 0px; margin: 0px;  font-size: 120%; }
      a , a:visited { color: inherit; }
      .red   { background-color: red; }
      .green { background-color: green; }
      
    </style>
  </head>
  <body>
EOF;

foreach (glob(PATH.'/*_*.php') as $tfile) {
    $reporter = new MyReporter;
    require_once($tfile);
}

print "</body>\n</html>\n";
?>
