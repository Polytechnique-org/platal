#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';
require_once 'class/jobterms.php';

$globals->debug = 0; //do not store backtraces

$terms = XDB::iterator('SELECT `jtid`, `name` FROM `profile_job_term_enum`');
while ($term = $terms->next()) {
    $tokens = array_unique(JobTerms::tokenize($term['name']));
    if (!count($tokens)) {
        continue;
    }
    $values = array();
    foreach ($tokens as $t) {
        $values[] = '('.XDB::escape($t).','.XDB::escape($term['jtid']).')';
    }
    XDB::execute('INSERT IGNORE INTO `profile_job_term_search` (`search`,`jtid`) VALUES '.implode(',',$values));
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
