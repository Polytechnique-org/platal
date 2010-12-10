#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';

$globals->debug = 0; // Do not store backtraces.
XDB::startTransaction();

// Drops temporary tables and views used to checked if the merge was possible.
XDB::rawExecute('DROP VIEW IF EXISTS fusionax_xorg_anciens');
XDB::rawExecute('DROP VIEW IF EXISTS fusionax_deceased');
XDB::rawExecute('DROP VIEW IF EXISTS fusionax_promo');
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_import');

// Fills pid fields in all table, to avoid to many joins.
foreach (array('fusionax_activites', 'fusionax_adresses', 'fusionax_anciens', 'fusionax_formations') as $table) {
    XDB::rawExecute("UPDATE  $table   AS f
                 INNER JOIN  profiles AS p ON (f.ax_id = p.ax_id)
                        SET  f.pid = p.pid");
    XDB::rawExecute("DELETE FROM $table WHERE pid IS NULL");
}

// Includes entreprises we do not have into profile_job_enum.
// We first retrieve AX code, then add missing compagnies.
echo "Starts jobs inclusions.\n";
XDB::rawExecute('ALTER TABLE profile_job_enum ADD INDEX (name(20))');
XDB::rawExecute('ALTER TABLE profile_job_enum ADD INDEX (acronym(20))');
XDB::rawExecute('ALTER TABLE profile_job_enum ADD INDEX (AX_code)');
XDB::rawExecute("UPDATE  profile_job_enum, fusionax_entreprises
                    SET  profile_job_enum.AX_code = fusionax_entreprises.Code_etab
                  WHERE  (profile_job_enum.name = fusionax_entreprises.Raison_sociale AND profile_job_enum.name != '' AND fusionax_entreprises.Raison_sociale != '')
                         OR (profile_job_enum.name = fusionax_entreprises.Sigle AND profile_job_enum.name != '' AND fusionax_entreprises.Sigle != '')
                         OR (profile_job_enum.acronym = fusionax_entreprises.Sigle AND profile_job_enum.acronym != '' AND fusionax_entreprises.Sigle != '')
                         OR (profile_job_enum.acronym = fusionax_entreprises.Raison_sociale AND profile_job_enum.acronym != '' AND fusionax_entreprises.Raison_sociale != '')");
XDB::rawExecute("INSERT IGNORE INTO  profile_job_enum (name, acronym, AX_code)
                             SELECT  f.Raison_sociale, f.Sigle, f.Code_etab
                               FROM  fusionax_entreprises AS f
                              WHERE  f.Raison_sociale != ''
                                     AND NOT EXISTS (SELECT  *
                                                       FROM  profile_job_enum AS j
                                                      WHERE  f.Code_etab = j.AX_code)");

// Includes jobs we do not have into profile_job_enum.
// There are 3 cases:
//  - the job is incomplete (ie no compagny name) : this is an issue,
//  - the job is complete but the profile already has a job or more : this is an issue,
//  - the job is complete and the the profile has no previous job : there is no issue.
// We delete obvious duplicates and avoid multiple joins.
XDB::rawExecute("DELETE  f
                   FROM  fusionax_activites   AS f
             INNER JOIN  profile_job_enum     AS pe ON (pe.AX_code = f.Code_etab)
             INNER JOIN  profile_job          AS pj ON (f.pid = pj.pid AND pj.jobid = pe.id)");
foreach (array('fusionax_activites', 'fusionax_adresses') as $table) {
    XDB::rawExecute("UPDATE  $table           AS f
                 INNER JOIN  profile_job_enum AS pe ON (f.Code_etab = pe.AX_code)
                        SET  f.jobid = pe.id");
}
XDB::rawExecute('ALTER TABLE profile_job_enum DROP INDEX name');
XDB::rawExecute('ALTER TABLE profile_job_enum DROP INDEX acronym');
XDB::rawExecute('ALTER TABLE profile_job_enum DROP INDEX AX_code');
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_entreprises');

// We first update the issues table.
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues)
                             SELECT  DISTINCT(f.pid), 'job'
                               FROM  fusionax_activites AS f
                              WHERE  f.jobid IS NULL OR EXISTS (SELECT  *
                                                                  FROM  profile_job AS pj
                                                                 WHERE  pj.pid = f.pid)");
// We then add new jobs.
$id = 0;
$continue = 1;
while ($continue > 0) {
    XDB::rawExecute("INSERT IGNORE INTO  profile_job (id, pid, jobid, pub, description)
                                 SELECT  $id, pid, jobid, IF(Annuaire = 1, 'ax', 'private'), description
                                   FROM  fusionax_activites");
    XDB::rawExecute("DELETE  f
                       FROM  fusionax_activites AS f
                 INNER JOIN  profile_job        AS pj ON (f.pid = pj.pid AND pj.id = $id AND pj.description = f.description)
                      WHERE  pj.jobid = f.jobid OR (pj.jobid IS NULL AND f.jobid IS NULL)");
    $continue = XDB::affectedRows();
}
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_activites');
// We also have to add related phones and addresses.
XDB::rawExecute("INSERT IGNORE INTO  profile_addresses (type, pid, id, text)
                             SELECT  'job', f.pid, pj.id, f.text
                               FROM  fusionax_adresses AS f
                         INNER JOIN  profile_job       AS pj ON (f.pid = pj.pid AND pj.jobid = f.jobid)
                              WHERE  f.Type_adr = 'E' AND f.text IS NOT NULL");
XDB::rawExecute("INSERT IGNORE INTO  profile_phones (link_type, link_id, tel_id, tel_type, pid, display_tel)
                             SELECT  'pro', pj.id, 0, 'fixed', f.pid, f.tel
                               FROM  fusionax_adresses AS f
                         INNER JOIN  profile_job       AS pj ON (f.pid = pj.pid AND pj.jobid = f.jobid)
                              WHERE  f.Type_adr = 'E' AND f.tel != ''");
XDB::rawExecute("INSERT IGNORE INTO  profile_phones (link_type, link_id, tel_id, tel_type, pid, display_tel)
                             SELECT  'pro', pj.id, IF(f.tel = '', 0, 1), 'fax', f.pid, f.fax
                               FROM  fusionax_adresses AS f
                         INNER JOIN  profile_job       AS pj ON (f.pid = pj.pid AND pj.jobid = f.jobid)
                              WHERE  f.Type_adr = 'E' AND f.fax != ''");
XDB::rawExecute("DELETE FROM fusionax_adresses WHERE Type_adr = 'E'");
echo "Jobs inclusions finished.\n";

// Retrieves information from fusionax_anciens: promo, nationality, corps, email, phone, deathdate.
// Updates uncertain promotions, but when we are we are right.
echo "Starts various informations inclusions.\n";
XDB::rawExecute("UPDATE  profile_merge_issues AS pm, fusionax_anciens AS f, profile_display AS pd, profile_education AS pe
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'promo'), 'promo'), pm.entry_year_ax = f.promotion_etude
                  WHERE  pm.pid = f.pid AND f.pid NOT IN (18399,21099,40616)
                         AND pd.pid = f.pid AND pe.pid = f.pid AND FIND_IN_SET('primary', pe.flags)
                         AND pd.promo != CONCAT('X', f.promotion_etude)
                         AND !(f.promotion_etude = pe.entry_year + 1 AND pe.grad_year = pe.entry_year + 4)");

XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues, entry_year_ax)
                             SELECT  f.pid, 'promo', f.promotion_etude
                               FROM  fusionax_anciens  AS f
                         INNER JOIN  profile_display   AS pd ON (f.pid = pd.pid)
                         INNER JOIN  profile_education AS pe ON (f.pid = pe.pid)
                              WHERE  pd.promo != CONCAT('X', f.promotion_etude) AND f.pid NOT IN (18399,21099,40616)
                                     AND !(f.promotion_etude = pe.entry_year + 1 AND pe.grad_year = pe.entry_year + 4)");

// Updates nationality.
XDB::rawExecute('ALTER TABLE geoloc_countries ADD INDEX (licensePlate)');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.pid = f.pid)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite AND g.nationalityFR IS NOT NULL)
                    SET  p.nationality1 = g.iso_3166_1_a2
                  WHERE  p.nationality1 IS NULL');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.pid = f.pid)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite AND g.nationalityFR IS NOT NULL)
                    SET  p.nationality2 = g.iso_3166_1_a2
                  WHERE  p.nationality1 != g.iso_3166_1_a2 AND p.nationality2 IS NULL');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.pid = f.pid)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite AND g.nationalityFR IS NOT NULL)
                    SET  p.nationality3 = g.iso_3166_1_a2
                  WHERE  p.nationality1 != g.iso_3166_1_a2 AND p.nationality2 != g.iso_3166_1_a2 AND p.nationality3 IS NULL');
XDB::rawExecute('ALTER TABLE geoloc_countries DROP INDEX licensePlate');

// Updates corps.
XDB::rawExecute("INSERT IGNORE INTO  profile_corps (pid, original_corpsid, current_corpsid, rankid, corps_pub)
                             SELECT  f.pid, c.id, c.id, r.id, 'ax'
                               FROM  fusionax_anciens        AS f
                         INNER JOIN  profile_corps_enum      AS c ON (f.corps_sortie = c.abbreviation)
                         INNER JOIN  profile_corps_rank_enum AS r ON (f.grade = r.abbreviation)
                              WHERE  NOT EXISTS (SELECT  *
                                                   FROM  profile_corps AS pc
                                                  WHERE  f.pid = pc.pid AND pc.original_corpsid != 1)");
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

// Updates email_directory.
XDB::rawExecute("UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.pid = f.pid)
                    SET  p.email_directory = f.Mel_usage
                  WHERE  f.Mel_publiable = 1 AND f.Mel_usage != '' AND p.email_directory IS NULL");
XDB::rawExecute("INSERT IGNORE INTO  register_marketing (uid, email, type)
                             SELECT  ap.uid, f.Mel_usage, 'ax'
                               FROM  fusionax_anciens AS f
                         INNER JOIN  account_profiles AS ap ON (ap.pid = f.pid AND FIND_IN_SET('owner', perms))
                          LEFT JOIN  emails           AS e  ON (e.uid = ap.uid AND e.flags = 'active')
                              WHERE  f.Mel_usage != '' AND f.Mel_usage NOT LIKE '%@polytechnique.edu'
                                     AND f.Mel_usage NOT LIKE '%@polytechnique.org' AND f.Mel_usage NOT LIKE '%@m4x.org'
                                     AND f.Mel_usage NOT LIKE '%@melix.%' AND e.email IS NULL");

// Retrieves different deathdates.
XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  profiles         AS p ON (pm.pid = p.pid)
             INNER JOIN  fusionax_anciens AS f ON (f.pid = p.pid)
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'deathdate'), 'deathdate'), pm.deathdate_ax = f.Date_deces
                  WHERE  p.deathdate != f.Date_deces");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues, deathdate_ax)
                             SELECT  f.pid, 'deathdate', f.Date_deces
                               FROM  fusionax_anciens AS f
                         INNER JOIN  profiles         AS p ON (f.pid = p.pid)
                              WHERE  p.deathdate != f.Date_deces");
echo "Various informations inclusions finished.\n";

// Updates phone.
// We consider there is conflict if a profile has a phone in both databases.
echo "Starts phones inclusions.\n";
XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  fusionax_anciens     AS f  ON (f.pid = pm.pid)
             INNER JOIN  profile_phones       AS pp ON (pp.pid = f.pid AND pp.link_type = 'user' AND pp.tel_id = 0)
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'phone'), 'phone')
                  WHERE  f.tel_mobile != ''");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues)
                             SELECT  f.pid, 'phone'
                               FROM  fusionax_anciens AS f
                         INNER JOIN  profile_phones   AS pp ON (pp.pid = f.pid AND pp.link_type = 'user' AND pp.tel_id = 0)
                              WHERE  f.tel_mobile != '' AND f.Mob_publiable = 1");

XDB::rawExecute("INSERT INTO  profile_phones (pid, link_type, tel_id, tel_type, display_tel, pub)
                      SELECT  f.pid, 'user', 0, 'mobile', f.tel_mobile, 'ax'
                        FROM  fusionax_anciens AS f
                       WHERE  NOT EXISTS (SELECT  *
                                            FROM  profile_phones AS pp
                                           WHERE  pp.pid = f.pid AND pp.link_type = 'user' AND pp.tel_id = 0)
                              AND f.tel_mobile != '' AND f.Mob_publiable = 1");
XDB::rawExecute("INSERT INTO  profile_phones (pid, link_type, tel_id, tel_type, display_tel, pub)
                      SELECT  f.pid, 'user', MAX(pp.tel_id) + 1, 'mobile', f.tel_mobile, 'ax'
                        FROM  fusionax_anciens AS f
                  INNER JOIN  profile_phones   AS pp ON (pp.pid = f.pid AND pp.link_type = 'user')
                       WHERE  f.tel_mobile != '' AND f.Mob_publiable = 1
                    GROUP BY  f.pid");
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_anciens');
echo "Phones inclusions finished.\n";

// Retrieves addresses from AX database (one address per preofile maximum).
echo "Starts addresses inclusions.\n";
XDB::rawExecute('ALTER TABLE profile_addresses ADD INDEX (text(20))');
XDB::rawExecute("DELETE  f
                   FROM  fusionax_adresses AS f
             INNER JOIN  profile_addresses AS pa ON (pa.pid = f.pid AND pa.type = 'home')
                  WHERE  pa.text = f.text");
// Deletes addresses of unknown type.
XDB::rawExecute("DELETE FROM  fusionax_adresses
                       WHERE  Type_adr != 'E' AND Type_adr != 'P'");

XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  fusionax_adresses    AS f  ON (f.pid = pm.pid)
             INNER JOIN  profile_addresses    AS pa ON (pa.pid = f.pid AND pa.type = 'home' AND pa.id = 0)
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'address'), 'address')
                  WHERE  f.text IS NOT NULL");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues)
                             SELECT  f.pid, 'address'
                               FROM  fusionax_adresses AS f
                         INNER JOIN  profile_addresses AS pa ON (pa.pid = f.pid AND pa.type = 'home' AND pa.id = 0)
                              WHERE  f.text IS NOT NULL");

XDB::rawExecute("INSERT INTO  profile_addresses (pid, type, id, pub, text)
                      SELECT  f.pid, 'home', IF(pa.id IS NULL , 0, MAX(pa.id) + 1), 'ax', f.text
                        FROM  fusionax_adresses AS f
                   LEFT JOIN  profile_addresses AS pa ON (pa.pid = f.pid AND pa.type = 'home')
                       WHERE  f.text IS NOT NULL
                    GROUP BY  f.pid");
XDB::rawExecute("INSERT INTO  profile_phones (pid, link_type, tel_id, tel_type, display_tel, pub, link_id)
                      SELECT  f.pid, 'address', IF(pp.tel_id IS NULL, 0, MAX(pp.tel_id) + 1), 'fixed', f.tel, 'ax', pa.id
                        FROM  fusionax_adresses AS f
                  INNER JOIN  profile_addresses AS pa ON (pa.pid = f.pid AND pa.type = 'home' AND f.text = pa.text)
                   LEFT JOIN  profile_phones    AS pp ON (pp.pid = f.pid AND pp.link_type = 'address' AND pp.link_id = pa.id)
                       WHERE  f.tel != ''
                    GROUP BY  f.pid");
XDB::rawExecute("INSERT INTO  profile_phones (pid, link_type, tel_id, tel_type, display_tel, pub, link_id)
                      SELECT  f.pid, 'address', IF(pp.tel_id IS NULL, 0, MAX(pp.tel_id) + 1), 'fax', f.fax, 'ax', pa.id
                        FROM  fusionax_adresses AS f
                  INNER JOIN  profile_addresses AS pa ON (pa.pid = f.pid AND pa.type = 'home' AND f.text = pa.text)
                   LEFT JOIN  profile_phones    AS pp ON (pp.pid = f.pid AND pp.link_type = 'address' AND pp.link_id = pa.id)
                       WHERE  f.fax != ''
                    GROUP BY  f.pid");
XDB::rawExecute('ALTER TABLE profile_addresses DROP INDEX text');
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_adresses');
echo "Addresses inclusions finished.\n";

// Retrieves education from AX database. This is the hardest part since AX only kept education as an unformated string.
echo "Starts educations inclusions.\n";
// Deletes empty educations.
XDB::rawExecute("DELETE FROM  fusionax_formations
                       WHERE  Intitule_formation = '' AND Intitule_diplome = '' AND Descr_formation = ''");
// Insert ids into fusionax_formations to prevent many joins.
XDB::rawExecute('UPDATE  fusionax_formations           AS f
              LEFT JOIN  profile_education_enum        AS pe ON (pe.name = f.Intitule_formation)
              LEFT JOIN  profile_education_degree_enum AS pd ON (pd.abbreviation = f.Intitule_diplome)
              LEFT JOIN  profile_education_field_enum  AS pf ON (pf.field = f.Descr_formation)
                    SET  f.eduid = pe.id, f.degreeid = pd.id, f.fieldid = pf.id');
// Updates non complete educations.
XDB::rawExecute("UPDATE  profile_education             AS e
             INNER JOIN  fusionax_formations           AS f  ON (f.pid = e.pid)
             INNER JOIN  profile_education_degree_enum AS pd ON (e.degreeid = pd.id)
             INNER JOIN  profile_education_degree_enum AS fd ON (f.degreeid = fd.id)
                    SET  e.eduid = f.eduid
                  WHERE  NOT FIND_IN_SET('primary', e.flags) AND e.eduid IS NULL AND pd.level = fd.level");
XDB::rawExecute("UPDATE  profile_education   AS e
             INNER JOIN  fusionax_formations AS f ON (f.pid = e.pid)
                    SET  e.degreeid = f.degreeid
                  WHERE  NOT FIND_IN_SET('primary', e.flags) AND e.degreeid IS NULL AND e.eduid = f.eduid");
// Deletes duplicates.
XDB::rawExecute("DELETE  f
                   FROM  fusionax_formations           AS f
             INNER JOIN  profile_education_degree_enum AS fd ON (fd.abbreviation = f.Intitule_diplome)
             INNER JOIN  profile_education             AS e  ON (e.pid = f.pid AND NOT FIND_IN_SET('primary', e.flags))
             INNER JOIN  profile_education_degree_enum AS pd ON (pd.id = e.degreeid)
                  WHERE  f.eduid = e.eduid AND fd.level = pd.level");
// Updates merge_issues table.
XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  fusionax_formations  AS f ON (f.pid = pm.pid)
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'education'), 'education')");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues)
                             SELECT  pid, 'education'
                               FROM  fusionax_formations");

$id = 0;
$continue = 1;
while ($continue > 0) {
    XDB::rawExecute("INSERT IGNORE INTO  profile_education (id, pid, eduid, degreeid, fieldid, program)
                                 SELECT  $id, pid, eduid, degreeid, fieldid, Descr_formation
                                   FROM  fusionax_formations");
    XDB::rawExecute("DELETE  f
                       FROM  fusionax_formations AS f
                 INNER JOIN  profile_education   AS pe ON (pe.pid = f.pid AND pe.id = $id AND pe.eduid = f.eduid AND pe.degreeid = f.degreeid
                                                           AND pe.fieldid = f.fieldid AND pe.program = f.Descr_formation)");
    $continue = XDB::affectedRows();
}
// Updates merge_issues table (eduid and degreeid should never be empty).
XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  profile_education    AS pe ON (pe.pid = pm.pid)
                    SET  pm.issues = CONCAT(pm.issues, ',', 'education')
                  WHERE  NOT FIND_IN_SET('education', pm.issues) AND (pe.eduid = '' OR pe.eduid IS NULL OR pe.degreeid = '' OR pe.degreeid IS NULL)");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues)
                             SELECT  pid, 'education'
                               FROM  profile_education
                              WHERE  eduid = '' OR eduid IS NULL OR degreeid = '' OR degreeid IS NULL");

XDB::rawExecute('DROP TABLE IF EXISTS fusionax_formations');
echo "Educations inclusions finished.\n";

echo "All inclusions are done.\n";

XDB::commit();

/* vim:set et sw=4 sts=4 ts=4: */
?>
