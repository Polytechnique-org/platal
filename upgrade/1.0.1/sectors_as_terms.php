#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';

$globals->debug = 0; //do not store backtraces

XDB::execute('INSERT INTO `profile_job_term_enum` (`name`, `full_name`) VALUES ("Secteurs d\'activité", "Secteurs d\'activité")');
$root_sector_id = XDB::insertId();
XDB::execute('INSERT INTO `profile_job_term_relation` VALUES (0, {?}, "narrower", "original"), ({?}, {?}, "narrower", "computed")',
             $root_sector_id, $root_sector_id, $root_sector_id);

// loops through sectors
$sectors = XDB::iterator('SELECT `id`, `name` FROM `profile_job_sector_enum`');
while ($oldsector = $sectors->next()) {
    // adds sector as term
    XDB::execute('INSERT INTO `profile_job_term_enum` (`name`, `full_name`) VALUES ( {?}, {?} )', $oldsector['name'], $oldsector['name'].' (secteur)');
    $sector_id = XDB::insertId();
    // links to root for sectors
    XDB::execute('INSERT INTO `profile_job_term_relation` VALUES ({?}, {?}, "narrower", "original"), ({?}, {?}, "narrower", "computed")',
                 $root_sector_id, $sector_id, $sector_id, $sector_id);
    // adds sector term to linked jobs and linked mentorships
    XDB::execute('INSERT INTO `profile_job_term`
                  SELECT  `pid`, `id`, {?}, "original"
                    FROM  `profile_job`
                   WHERE  `sectorid` = {?} AND `subsectorid` = 0',
                $sector_id, $oldsector['id']);
    XDB::execute('INSERT INTO `profile_mentor_term`
                  SELECT  `pid`, {?}
                    FROM  `profile_mentor_sector`
                   WHERE  `sectorid` = {?} AND `subsectorid` = 0',
                $sector_id, $oldsector['id']);
    // loops through subsectors
    $subsectors = XDB::iterator('SELECT `id`, `name` FROM `profile_job_subsector_enum` WHERE sectorid = {?}', $oldsector['id']);
    while ($oldsubsector = $subsectors->next()) {
        if ($oldsubsector['name'] == $oldsector['name']) {
            // adds sector term to jobs and mentorships linked to subsector with same name as sector
            XDB::execute('INSERT INTO `profile_job_term`
                          SELECT  `pid`, `id`, {?}, "original"
                            FROM  `profile_job`
                           WHERE  `sectorid` = {?} AND `subsectorid` = {?}',
                $sector_id, $oldsector['id'], $oldsubsector['id']);
            XDB::execute('INSERT INTO `profile_mentor_term`
                          SELECT  `pid`, {?}
                            FROM  `profile_mentor_sector`
                           WHERE  `sectorid` = {?} AND `subsectorid` = {?}',
                $sector_id, $oldsector['id'], $oldsubsector['id']);
            continue;
        }
        // adds subsector as term
        XDB::execute('INSERT INTO `profile_job_term_enum` (`name`, `full_name`) VALUES ( {?}, {?} )', $oldsubsector['name'], $oldsubsector['name'].' (secteur)');
        $subsector_id = XDB::insertId();
        // links to root for sectors and to sector
        XDB::execute('INSERT INTO `profile_job_term_relation` VALUES ({?}, {?}, "narrower", "computed"), ({?}, {?}, "narrower", "original"), ({?}, {?}, "narrower", "computed")',
                     $root_sector_id, $subsector_id, $sector_id, $subsector_id, $subsector_id, $subsector_id);
        // adds subsector term to linked jobs and mentorships
        XDB::execute('INSERT INTO `profile_job_term`
                      SELECT  `pid`, `id`, {?}, "original"
                        FROM  `profile_job`
                       WHERE  `sectorid` = {?} AND `subsectorid` = {?}',
            $subsector_id, $oldsector['id'], $oldsubsector['id']);
        XDB::execute('INSERT INTO `profile_mentor_term`
                      SELECT  `pid`, {?}
                        FROM  `profile_mentor_sector`
                       WHERE  `sectorid` = {?} AND `subsectorid` = {?}',
            $subsector_id, $oldsector['id'], $oldsubsector['id']);
    }
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
