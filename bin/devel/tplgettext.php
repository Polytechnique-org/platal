#!/usr/bin/php
<?php
    foreach($argv as $i=>$file) {
	if($i == 0) continue;
	$fp = fopen($file,'r');
	$fc = fread($fp,filesize($file));
	fclose($fp);

	preg_match_all('!(""".*?""")!s', $fc, $res);
	if(count($res[1])) {
	    $fp = fopen($file.'.py','w');
	    foreach($res[1] as $l) {
		fwrite($fp,'_('.stripslashes($l).")\n");
	    }
	    fclose($fp);
	}
    }
?>
