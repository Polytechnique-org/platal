#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';
require_once '../../classes/address.php';
require_once '../../classes/phone.php';

$globals->debug = 0; // Do not store backtraces.

/* Drops temporary tables and views used to checked if the merge was possible. */
XDB::rawExecute('DROP VIEW IF EXISTS fusionax_xorg_anciens');
XDB::rawExecute('DROP VIEW IF EXISTS fusionax_deceased');
XDB::rawExecute('DROP VIEW IF EXISTS fusionax_promo');
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_import');

/* Includes entreprises we do not have into profile_job_enum. */
XDB::rawExecute('INSERT INTO  profile_job_enum (name, acronym, AX_code)
                      SELECT  f.Raison_sociale, f.Sigle, f.Code_etab
                        FROM  fusionax_entreprises AS f
                       WHERE  NOT EXISTS (SELECT  *
                                            FROM  profile_job_enum AS j
                                           WHERE  j.name = f.Raison_sociale OR j.name = f.Sigle OR f.Code_etab = j.AX_code)');
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_entreprises');

/* Includes jobs we do not have into profile_job_enum. */
$jobsAX = XDB::rawIterator('SELECT  p.pid, pje.name, pje.id, IF(f.Annuaire = 1, \'ax\', \'private\') AS pub, p.ax_id,
                                    IF(f.Raison_sociale, CONCAT(f.Raison_sociale, CONCAT(\' \', f.Libelle_fonctio), f.Libelle_fonctio) AS description
                              FROM  fusionax_activities AS f
                        INNER JOIN  profile_job_enum    AS pje ON (pje.AX_code = f.Code_etab)
                        INNER JOIN  profiles            AS p   ON (f.id_ancien = p.ax_id)
                          ORDER BY  p.pid');
$jobsXorg = XDB::rawIterator('SELECT  p.pid, pj.id, pje.name, pje.acronym
                                FROM  profile_job         AS pj
                          INNER JOIN  profile_job_enum    AS pje ON (pje.id = pj.jobid)
                          INNER JOIN  profiles            AS p   ON (p.pid = pj.pid)
                          INNER JOIN  fusionax_activities AS f   ON (f.id_ancien = p.ax_id)
                            ORDER BY  p.pid, pj.id');
$jobXorg = $jobsXorg->next();
while ($jobAX = $jobsAX->next()) {
    $already = false;
    $id = 0;
    while ($jobXorg['pid'] == $jobAX['pid']) {
        if ($jobXorg['name'] == $jobAX['name'] || $jobXorg['acronym'] == $jobAX['name']) {
            $jobXorg = $jobsXorg->next();
            $jobAX = $jobsAX->next();
            $already = true;
        }
        list($pid, $id, $name, $acronym) = $jobXorg;
    }
    if (!$already) {
        ++$id;
        XDB::execute('INSERT INTO  profile_job (id, pid, jobid, description, pub)
                           VALUES  {?}, {?}, {?}, {?}',
                     $id, $jobsAX['pid'], $jobsAX['id'], $jobsAX['description'], $jobsAX['pub']);
        $res = XDB::query("SELECT  CONCAT(Ligne1, IF(Ligne2 != '', CONCAT('\n', Ligne2), ''),
                                              IF(Ligne3 != '', CONCAT('\n', Ligne3), ''),
                                              '\n', IF(code_postal, code_postal, zip_cedex), ' ', zip_cedex) AS address,
                                   tel, fax
                             FROM  fusionax_adresses
                            WHERE  Type_adr = 'E' and id_ancien = {?}", $jobsAX['ax_id']);
        $res = $res->fetchOneRow();
        $phone = new Phone(array('display' => $res['tel'], 'link_id' => $id, 'pid' => $jobsAX['pid'], 'type' => 'fixed', 'link_type' => Phone::LINK_JOB, 'pub' => $jobsAX['pub']));
        $fax = new Phone(array('display' => $res['fax'], 'link_id' => $id, 'pid' => $jobsAX['pid'], 'type' => 'fax', 'link_type' => Phone::LINK_JOB, 'pub' => $jobsAX['pub']));
        $address = new Address(array('type' => Address:LINK_JOB, 'text' => $res['address'], 'pid' => $jobsAX['pid'], 'id' => $id));
        $phone->save();
        $fax->save();
        $address->save();
        if ($id > 1) {
            XDB::execute("UPDATE  profile_merge_issues
                             SET  issues = IF(issues, CONCAT(issues, ',', 'job'), 'job')
                           WHERE  pid = {?}", $jobsAX['pid']);
        }
    }
}
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_activities');
XDB::rawExecute('DELETE FROM fusionax_adresses WHERE  Type_adr = \'E\'');

/* Retrieves information from fusionax_anciens: promo, nationality, corps, email, phone, deathdate */
/* Updates uncertain promotions, but when we are we are right. */
XDB::rawExecute("UPDATE  profile_merge_issues
                    SET  issues = IF(issues, CONCAT(issues, ',', 'promo'), 'promo'), entry_year_ax = f.promotion_etude
                  WHERE  EXISTS (SELECT  *
                                   FROM  profiles          AS p
                             INNER JOIN  profile_display   AS pd ON (p.pid = pd.pid)
                             INNER JOIN  profile_education AS pe ON (p.pid = pe.pid)
                             INNER JOIN  fusionax_anciens  AS f  ON (p.ax_id = f.ax_id)
                                  WHERE  pd.promo != CONCAT('X', f.promotion_etude)
                                         AND !(f.promotion_etude = pe.entry_year + 1 AND pe.grad_year = pe.entry_year + 4))
                         AND pid NOT IN (18399,21099,40616)");

/* Updates nationality. */
XDB::rawExecute('ALTER TABLE geoloc_pays ADD INDEX (license_plate);');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.ax_id = f.ax_id)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite)
                    SET  p.nationality1 = g.a2
                  WHERE  p.nationality1 IS NULL;');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.ax_id = f.ax_id)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite)
                    SET  p.nationality2 = g.a2
                  WHERE  p.nationality1 != g.a2 AND p.nationality2 IS NULL;');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.ax_id = f.ax_id)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite)
                    SET  p.nationality3 = g.a2
                  WHERE  p.nationality1 != g.a2 AND p.nationality2 != g.a2 AND p.nationality3 IS NULL;');
XDB::rawExecute('ALTER TABLE geoloc_pays DROP INDEX (license_plate)');

/* Updates corps. */
XDB::rawExecute('REPLACE IGNORE INTO  profile_corps (pid, original_corpsid, current_corpsid, rankid, corps_pub)
                              SELECT  p.pid, c.id, c.id, r.id, \'ax\'
                                FROM  profiles AS p
                          INNER JOIN  fusionax_anciens        AS f ON (p.ax_id = f.ax_id)
                          INNER JOIN  profile_corps_enum      AS c ON (f.corps_sortie = c.abbreviation)
                          INNER JOIN  profile_corps_rank_enum AS r ON (f.grade = r.abbreviation)
                               WHERE  NOT EXISTS (SELECT  *
                                                    FROM  profile_corps AS pc
                                                   WHERE  p.pid = pc.pid AND pc.original_corpsid != 1)');
XDB::rawExecute("UPDATE  profile_corps      AS c
             INNER JOIN  profile_corps_enum AS e ON (c.original_corpsid = e.id)
              LEFT JOIN  profile_corps_enum AS a ON (a.name = 'Aucun (anc. démissionnaire)')
                    SET  c.original_corpsid = a.id
                  WHERE  e.name = 'Ancien élève étranger'");
XDB::rawExecute("UPDATE  profile_corps_enum
                    SET  name = 'Aucun'
                  WHERE  name = 'Aucun (anc. démissionnaire)'");
XDB::rawExecute("DELETE FROM  profile_corps_enum
                       WHERE  name = 'Ancien élève étranger'");

/* Updates email_directory. */
XDB::rawExecute("INSERT IGNORE INTO  profile_directory (pid, email_directory)
                             SELECT  p.pid, f.Mel_usage
                               FROM  fusionax_anciens AS f
                         INNER JOIN  profiles         AS p ON (p.ax_id = f.ax_id)
                              WHERE  f.Mel_publiable != '0' AND f.Mel_usage != ''");
XDB::rawExecute("INSERT IGNORE INTO  register_marketing (uid, email, type)
                             SELECT  ap.uid, f.Mel_usage, 'ax'
                               FROM  fusionax_anciens AS f
                         INNER JOIN  profiles         AS p  ON (p.ax_id = f.ax_id)
                         INNER JOIN  account_profiles AS ap ON (ap.pid = p.pid AND FIND_IN_SET('owner', perms))
                          LEFT JOIN  emails           AS e  ON (e.uid = ap.uid AND e.flags = 'active')
                              WHERE  f.Mel_usage != '' AND f.Mel_usage NOT LIKE '%@polytechnique.edu'
                                     AND f.Mel_usage NOT LIKE '%@polytechnique.org' AND f.Mel_usage NOT LIKE '%@m4x.org'
                                     AND f.Mel_usage NOT LIKE '%@melix.%' AND e.email IS NULL");

/* Updates phone. */
$phonesXorg = Phone::iterate(array(), array('user'));
$phonesAX = XDB::rawIterator("SELECT  p.pid, f.tel_mobile AS display, 'user' AS link_type, 'mobile' AS type, 'ax' AS pub
                                FROM  fusionax_anciens AS f
                          INNER JOIN  profiles         AS p ON (f.ax_id = p.ax_id)
                               WHERE  f.tel_mobile IS NOT NULL
                            ORDER BY  p.pid");
$phoneXorg = $phonesXorg->next();
while ($phoneAX = new Phone($phonesAX->next())) {
    $already = false;
    $id = 0;
    $phoneAX->format();
    while ($phoneXorg->pid() == $phoneAX->pid()) {
        if ($phoneXorg->display == $phoneAX->display) {
            $already = true;
        }
        ++$id;
        $phoneXorg = $phonesXorg->next();
    }
    if (!$already) {
        $phoneAX->setId($id);
        $phoneAX->save();
        if ($id > 0) {
            XDB::execute("UPDATE  profile_merge_issues
                             SET  issues = IF(issues, CONCAT(issues, ',', 'phone'), 'phone')
                           WHERE  pid = {?}", $phoneAX->pid());
        }
    }
}

/* Retrieves different deathdates. */
XDB::rawExecute("UPDATE  profile_merge_issues AS pi
                    SET  issues = IF(issues, CONCAT(issues, ',', 'deathdate'), 'deathdate'), deathdate_ax = f.Date_deces
             INNER JOIN  profiles         AS p ON (pi.pid = p.pid)
             INNER JOIN  fusionax_anciens AS f ON (f.ax_id = p.ax_id)
                  WHERE  p.deathdate != f.Date_deces");
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_anciens');

/* Retrieves addresses from AX database (one address per user maximum). */
$addressesAX = XDB::rawIterator("SELECT  CONCAT(Ligne1, IF(Ligne2 != '', CONCAT('\n', Ligne2), ''),
                                                IF(Ligne3 != '', CONCAT('\n', Ligne3), ''),
                                                '\n', IF(code_postal, code_postal, zip_cedex), ' ', zip_cedex) AS text,
                                         f.tel, f.fax, p.pid, 'home' AS type, 'ax' AS pub
                                   FROM  fusionax_adresses AS f
                             INNER JOIN  profiles          AS p ON (f.id_ancien = p.ax_id)
                                  WHERE  f.Type_adr = 'E' and f.id_ancien = {?} AND Ligne1 != ''
                               ORDER BY  p.pid");
$addressesXorg = Address::iterate(array(), array('home'));
$addressXorg = $addressesXorg->next();
while ($addressAX = new Address($addressesAX->next())) {
    $already = false;
    $id = 0;
    $addressAX->format();
    $addressAX->phones[0] = array('display' => $addressAX->tel, 'type' => 'fixed');
    $addressAX->phones[1] = array('display' => $addressAX->fax, 'type' => 'fax');
    while ($addressXorg->pid == $addressAX->pid) {
        if ($addressXorg->text == $addressAX->text) {
            $already = true;
        }
        ++$id;
        $addressXorg = $addressesXorg->next();
    }
    if (!$already) {
        $addressAX->setId($id);
        $addressAX->save();
        if ($id > 0) {
            XDB::execute("UPDATE  profile_merge_issues
                             SET  issues = IF(issues, CONCAT(issues, ',', 'address'), 'address')
                           WHERE  pid = {?}", $addressAX->pid);
        }
    }
}
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_adresses');

/* Retrieves education from AX database. This is the hardest part since AX only kept education as an unformated string. */
// {{{ First, we need to build a few lists.
$degree_list = $level_list = $university_list = $field_list = array();
$res = XDB::rawIterator('SELECT  id, abbreviation AS name
                           FROM  profile_education_degree_enum
                       ORDER BY  id');
while ($res as $item->next()) {
    $degree_list[$item[1]] = $item[0];
}
$res = XDB::rawIterator('SELECT  level, abbreviation AS name
                           FROM  profile_education_degree_enum
                       ORDER BY  id');
while ($res as $item->next()) {
    $level_list[$item[1]] = $item[0];
}
$res = XDB::rawIterator("SELECT  id, IF(abbreviation = '', name, abbreviation) AS name
                           FROM  profile_education_enum
                       ORDER BY  id");
while ($res as $item->next()) {
    $university_list[$item[1]] = $item[0];
}
$res = XDB::rawIterator('SELECT  id, field AS name
                           FROM  profile_education_field_enum
                       ORDER BY  id');
while ($res as $item->next()) {
    $field_list[$item[1]] = $item[0];
}
// }}}
$edusXorg = XDB::rawIterator("SELECT  p.pid, d.abbreviation AS degree, IF(e.abbreviation = '', e.name, e.abbreviation) AS university,
                                      pe.program, pe.id AS no
                                FROM  profile_education             AS pe
                          INNER JOIN  profiles                      AS p ON (pe.pid = p.pid)
                          INNER JOIN  fusionax_formations           AS f ON (f.id_ancien = p.ax_id)
                          INNER JOIN  profile_education_enum        AS e ON (pe.eduid = e.id)
                          INNER JOIN  profile_education_degree_enum AS d ON (pe.degreeid = d.id)
                               WHERE  NOT FIND_IN_SET('primary', pe.flags)
                            ORDER BY  p.pid, pe.id");
$edusAX = XDB::rawIterator("SELECT  p.pid, f.Intitule_diplome AS degree, f.Intitule_formation AS university, f.Descr_formation AS program
                              FROM  fusionax_formations AS f
                        INNER JOIN  profiles            AS p ON (f.id_ancien = p.ax_id)
                          ORDER BY  p.pid");
$eduXorg = $edusXorg->next();
while ($eduAX = $edusAX->next()) {
    $id = 0;
    while ($eduXorg['pid'] == $eduAX['pid']) {
        if ($eduXorg['university'] == $eduAX['university'] && $level_list[$eduXorg['degree']] == $level_list[$eduAX['degree']]) {
            $already = true;
        }
        ++$id;
        $eduXorg = $edusXorg->next();
    }
    if (isset($field_list[$eduAX['program']])) {
        $fieldid = $field_list[$eduAX['program']];
        $program = null;
    } else {
        $fieldid = null;
        $program = $eduAX['program'];
    }
    if (!$already) {
        XDB::execute('INSERT INTO  profile_education (pid, degreeid, eduid, program, fieldid, id)
                           VALUES  {?}, {?}, {?}, {?}, {?}, {?}',
                     $eduAX['pid'], $degree_list[$eduAX['degree']], $university_list[$eduAX['university']],
                     $program, $fieldid, $id);
        if ($id > 0) {
            XDB::execute("UPDATE  profile_merge_issues
                             SET  issues = IF(issues, CONCAT(issues, ',', 'education'), 'education')
                           WHERE  pid = {?}", $addressAX->pid);
        }
    }
}
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_formations');

/* vim:set et sw=4 sts=4 ts=4: */
?>
