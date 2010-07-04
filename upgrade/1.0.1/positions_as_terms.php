#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';

$globals->debug = 0; //do not store backtraces

$data = implode('', file('arbo-UTF8.xml'));
$parser = xml_parser_create();
xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
xml_parse_into_struct($parser, $data, $values, $tags);
xml_parser_free($parser);

XDB::execute('INSERT INTO `profile_job_term_enum` (`name`, `full_name`) VALUES ("Emplois", "Emplois")');

$opened_nodes = array();
$broader_ids = array(XDB::insertId());

XDB::execute('INSERT INTO profile_job_term_relation VALUES (0, {?}, "narrower", "original"), ({?}, {?}, "narrower", "computed")',
             $broader_ids[0], $broader_ids[0], $broader_ids[0]);

// loop through the structures
foreach ($values as $val) {
    if (($val['type'] == 'open' || $val['type'] == 'complete') && !empty($val['attributes']['intitule'])) {
        $intitule = $val['attributes']['intitule'];
        if (mb_strtoupper($intitule) == $intitule) {
            $intitule = ucfirst(mb_strtolower($intitule));
        }
        $res = XDB::execute('INSERT INTO  profile_job_term_enum (name, full_name)
                                  VALUES  ({?}, {?})',
                            $intitule, $intitule.' (emploi'.(($val['type'] == 'open')?'s':'').')');
        $newid = XDB::insertId();
        array_unshift($broader_ids, $newid);
        array_unshift($opened_nodes, $val['tag']);
        foreach ($broader_ids as $i => $bid) {
            XDB::execute('INSERT INTO profile_job_term_relation VALUES ({?}, {?}, "narrower", {?})',
                         $bid, $newid, ($i == 1)?'original':'computed');
        }
    }
    if (count($opened_nodes) > 0 && $val['tag'] == $opened_nodes[0] && ($val['type'] == 'close' || $val['type'] == 'complete')) {
        array_shift($broader_ids);
        array_shift($opened_nodes);
    }
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
