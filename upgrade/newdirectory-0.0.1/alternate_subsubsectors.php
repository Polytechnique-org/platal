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

// loop through the structures
foreach ($values as $val) {
    if ($val['tag'] == 'grand-domaine' && $val['type'] == 'open') {
        $res = XDB::execute('INSERT INTO  profile_job_sector_enum (name)
                                  VALUES  ({?})',
                            ucfirst(strtolower($val['attributes']['intitule'])));
        $sectorid = XDB::insertId();
    }
    if ($val['tag'] == 'domaine' && $val['type'] == 'open') {
        $res = XDB::execute('INSERT INTO  profile_job_subsector_enum (sectorid, name)
                                  VALUES  ({?}, {?})',
                            $sectorid, $val['attributes']['intitule']);
        $subsectorid = XDB::insertId();
    }
    if ($val['tag'] == 'domaine-intermediaire' && $val['type'] == 'open') {
        $res = XDB::execute('INSERT INTO  profile_job_subsector_enum (sectorid, name, flags)
                                  VALUES  ({?}, {?}, \'optgroup\')',
                            $sectorid, $val['attributes']['intitule']);
    }
    if ($val['tag'] == 'fiche' && $val['type'] == 'open') {
        $res = XDB::execute('INSERT INTO  profile_job_subsubsector_enum (sectorid, subsectorid, name)
                                  VALUES  ({?}, {?}, {?})',
                            $sectorid, $subsectorid, $val['attributes']['intitule']);
        $subsubsectorid = XDB::insertId();
        $id = 0;
    }
    if ($val['tag'] == 'appellation' && $val['type'] == 'complete') {
        $res = XDB::execute('INSERT INTO  profile_job_alternates (id, subsubsectorid, name)
                                  VALUES  ({?}, {?}, {?})',
                            $id, $subsubsectorid, $val['attributes']['intitule']);
        ++$id;
    }
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
