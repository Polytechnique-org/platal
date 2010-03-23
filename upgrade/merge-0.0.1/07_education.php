#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';

$globals->debug = 0; //do not store backtraces

// get degree list
$res = XDB::iterator("SELECT  id, abbreviation AS name
                        FROM  profile_education_degree_enum
                    ORDER BY  id");
foreach ($res as $item) {
    $degree_list[$item[1]] = $item[0];
}

// get degree's level list
$res = XDB::iterator("SELECT  id, level AS name
                        FROM  profile_education_degree_enum
                    ORDER BY  id");
foreach ($res as $item) {
    $level_list [$item[1]] = $item[0];
}

// get university list
$res = XDB::iterator("SELECT  id, IF(abbreviation = '', name, abbreviation) AS name
                        FROM  profile_education_enum
                    ORDER BY  id");
foreach ($res as $item) {
    $university_list [$item[1]] = $item[0];
}

// get field list
$res = XDB::iterator("SELECT  id, field AS name
                        FROM  profile_education_field_enum
                    ORDER BY  id");
foreach ($res as $item) {
    $field_list [$item[1]] = $item[0];
}

// get Xorg education data
$res = XDB::query("SELECT  p.uid, d.abbreviation AS degree, IF(e.abbreviation = '', e.name, e.abbreviation) AS university, p.program, p.id AS no
                     FROM  profile_education             AS p
               INNER JOIN  profile_education_enum        AS e ON (p.eduid = e.id)
               INNER JOIN  profile_education_degree_enum AS d ON (p.degreeid = d.id)
                    WHERE  p.id != 100
                 ORDER BY  p.uid");
$xorg_edu = $res->fetchAllAssoc();

// get AX education data
$res = XDB::iterator("SELECT  u.user_id AS uid, f.Intitule_diplome AS degree, f.Intitule_formation AS university,
                              CONCAT(Descr_formation, ' ', tmp_1, ' ', tmp_2, ' ', tmp_3, ' ', tmp_4) AS program
                        FROM  fusionax_formations   AS f
                  INNER JOIN  fusionax_xorg_anciens AS u ON (f.id_ancien = u.matricule_ax)
                    ORDER BY  u.user_id");
$ax_edu = $res->fetchAllAssoc();

// merge education data
$nb_merge_succes = 0;
$nb_total = 0;
$xorg = next($xorg_edu);
while ($ax = next($ax_edu)) {
    array_walk($ax, 'trim');
    if (($ax['degree'] == '') && ($ax['university'] = '')) {
        continue;
    }
    while ($xorg['uid'] && ($xorg['uid'] < $ax['uid'])) {
        $xorg = next($xorg_edu);
    }

    $no = 0;
    if($xorg['uid'] == $ax['uid']) {
        $uid = $xorg['uid'];
        $i = 0;

        while (($xorg['uid'] == $uid) && (!merge($ax, $xorg))) {
            $xorg = next($xorg_edu);
            $i++;
            $no++;
        }
        while ($xorg['uid'] == $uid) {
            $xorg = next($xorg_edu);
            $no++;
        }

        if ($i > 0) {
            $i = $no;
        } else {
            $i = $no - 1;
        }
        while ($i != 0) {
            $xorg = prev($xorg_edu);
            $i--;
        }
        if ($ax['no']) {
            $no = $ax['no'];
            $nb_merge_succes++;
        }
    }
    adapt_ax($ax);
    XDB::execute("REPLACE INTO  profile_education (uid, degreeid, eduid, program, fieldid, id)
                        VALUES  {?}, {?}, {?}, {?}, {?}, {?}",
                 $ax['uid'], $ax['degree'], $ax['university'], $ax['program'], $ax['field'], $no);
    $nb_total++;
    if (($nb_total % 1000) == 0) {
        echo ".";
    }
}

echo "\n";
echo "$nb_merge_succes educations were succesfully merged among $nb_total entries.\n";

// auxilliary functions

// replaces AX data by corresponding id in Xorg database
function adapt_ax(&$ax)
{
    if ($field_list[$ax['program']]) {
        $ax['field'] = $field_list[$ax['program']];
        $ax['program'] = null;
    }
    $ax['degree'] = $degree_list[$ax['degree']];
    $ax['university'] = $university_list[$ax['university']];
}

// tries to merge two educations into ax and returns 1 in case of merge
function merge(&$ax, $xorg)
{
    if ($ax['degree'] == '') {
        if ($ax['university'] != $xorg['university']) {
            return 0;
        }
        $ax['degree'] = $xorg['degree'];
        $ax['university'] = $xorg['university'];
    } else {
        if ($ax['university'] == '') {
            if (($level_list[$ax['degree']] == $level_list[$xorg['degree']]) || ($xorg['degree'] == "Dipl.") || ($ax['degree'] == "Dipl.")) {
                if ($xorg['degree'] != "Dipl.") {
                    $ax['degree'] = $xorg['degree'];
                }
                $ax['university'] = $xorg['university'];
            } else {
                return 0;
            }
        } else {
            if (($ax['university'] == $xorg['university']) &&
                (($level_list[$ax['degree']] == $level_list[$xorg['degree']]) || ($xorg['degree'] == "Dipl.") || ($ax['degree'] == "Dipl."))) {
                if ($xorg['degree'] != "Dipl.") {
                    $ax['degree'] = $xorg['degree'];
                }
            } else {
                return 0;
            }
        }
    }
    if ($xorg['program']) {
        $ax['field'] = $field_list[$ax['program']];
        $ax['program'] = $xorg['program'];
    }
    $ax['no'] = $xorg['no'];
    return 1;
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
