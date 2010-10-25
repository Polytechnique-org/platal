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

// Includes entreprises we do not have into profile_job_enum.
// We first retrieve AX code, then add missing compagnies.
echo "Starts jobs inclusions.\n";
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

// We delete obvious duplicates.
XDB::rawExecute("DELETE  f
                   FROM  fusionax_activites   AS f
             INNER JOIN  profiles             AS p  ON (f.ax_id = p.ax_id)
             INNER JOIN  profile_job_enum     AS pe ON (pe.AX_code = f.Code_etab)
             INNER JOIN  profile_job          AS pj ON (p.pid = pj.pid AND pj.jobid = pe.id)");

// We first update the issues table.
XDB::rawExecute("INSERT INTO  profile_merge_issues (pid, issues)
                      SELECT  DISTINCT(p.pid), 'job'
                        FROM  profiles             AS p
                  INNER JOIN  fusionax_activites   AS f  ON (f.ax_id = p.ax_id)
                  INNER JOIN  fusionax_entreprises AS fe ON (fe.Code_etab = f.Code_etab)
                       WHERE  (fe.Raison_sociale = '' AND NOT EXISTS (SELECT  *
                                                                        FROM  profile_job AS pj
                                                                       WHERE  pj.pid = p.pid))
                              OR (fe.Raison_sociale != '' AND EXISTS (SELECT  *
                                                                        FROM  profile_job AS pj
                                                                       WHERE  pj.pid = p.pid))");
// Then we retrieve jobs without entreprise name.
XDB::rawExecute("INSERT INTO  profile_job (id, pid, jobid, pub, description)
                      SELECT  0, p.pid, NULL, IF(f.Annuaire = 1, 'ax', 'private'),
                              IF(f.Raison_sociale,
                                 IF(f.Libelle_fonctio, CONCAT(f.Raison_sociale, ' ', f.Libelle_fonctio), f.Raison_sociale),
                                 f.Libelle_fonctio)
                        FROM  fusionax_activites   AS f
                  INNER JOIN  profiles             AS p  ON (f.ax_id = p.ax_id)
                  INNER JOIN  fusionax_entreprises AS fe ON (fe.Code_etab = f.Code_etab AND fe.Raison_sociale = '')
                       WHERE  NOT EXISTS (SELECT  *
                                            FROM  profile_job AS pj
                                           WHERE  pj.pid = p.pid)");
// We insert complete jobs for profile already having jobs.
XDB::rawExecute("INSERT INTO  profile_job (id, pid, jobid, pub, description)
                      SELECT  MAX(pj.id) + 1, p.pid, pe.id, IF(f.Annuaire = 1, 'ax', 'private'),
                              IF(f.Raison_sociale,
                                 IF(f.Libelle_fonctio, CONCAT(f.Raison_sociale, ' ', f.Libelle_fonctio), f.Raison_sociale),
                                 f.Libelle_fonctio)
                        FROM  fusionax_activites   AS f
                  INNER JOIN  profiles             AS p  ON (f.ax_id = p.ax_id)
                  INNER JOIN  fusionax_entreprises AS fe ON (fe.Code_etab = f.Code_etab AND fe.Raison_sociale != '')
                  INNER JOIN  profile_job_enum     AS pe ON (pe.AX_code = f.Code_etab)
                  INNER JOIN  profile_job          AS pj ON (pj.pid = p.pid)
                    GROUP BY  p.pid");
// Delete everything that has already been inserted.
XDB::rawExecute("DELETE  f
                   FROM  fusionax_activites   AS f
             INNER JOIN  profiles             AS p  ON (f.ax_id = p.ax_id)
             INNER JOIN  fusionax_entreprises AS fe ON (fe.Code_etab = f.Code_etab)
                  WHERE  fe.Raison_sociale = ''
                         OR (fe.Raison_sociale != '' AND EXISTS (SELECT  *
                                                                   FROM  profile_job AS pj
                                                                  WHERE  pj.pid = p.pid))");
// We finally add new complete jobs.
$id = 0;
$continue = 1;
while ($continue > 0) {
    XDB::rawExecute("INSERT IGNORE INTO  profile_job (id, pid, jobid, pub, description)
                                 SELECT  $id, p.pid, pe.id, IF(f.Annuaire = 1, 'ax', 'private'),
                                         IF(f.Raison_sociale,
                                            IF(f.Libelle_fonctio, CONCAT(f.Raison_sociale, ' ', f.Libelle_fonctio), f.Raison_sociale),
                                            f.Libelle_fonctio)
                                   FROM  fusionax_activites   AS f
                             INNER JOIN  profiles             AS p  ON (f.ax_id = p.ax_id)
                             INNER JOIN  profile_job_enum     AS pe ON (pe.AX_code = f.Code_etab)");
    XDB::rawExecute("DELETE  f
                       FROM  fusionax_activites   AS f
                 INNER JOIN  profiles             AS p  ON (f.ax_id = p.ax_id)
                 INNER JOIN  profile_job_enum     AS pe ON (pe.AX_code = f.Code_etab)
                 INNER JOIN  profile_job          AS pj ON (p.pid = pj.pid AND pj.id = $id AND pj.jobid = pe.id)");
    $continue = XDB::affectedRows();
}
// We also have to add related phones and addresses.
XDB::rawExecute("INSERT IGNORE INTO  profile_addresses (type, pid, id, text)
                             SELECT  'job', p.pid, pj.id, f.text
                               FROM  fusionax_adresses AS f
                         INNER JOIN  profiles          AS p  ON (f.ax_id = p.ax_id)
                         INNER JOIN  profile_job_enum  AS pe ON (pe.AX_code = f.Code_etab)
                         INNER JOIN  profile_job       AS pj ON (p.pid = pj.pid AND pj.jobid = pe.id)
                              WHERE  f.Type_adr = 'E' AND f.text IS NOT NULL");
XDB::rawExecute("INSERT IGNORE INTO  profile_phones (link_type, link_id, tel_id, tel_type, pid, display_tel)
                             SELECT  'pro', pj.id, 0, 'fixed', p.pid, f.tel
                               FROM  fusionax_adresses AS f
                         INNER JOIN  profiles          AS p  ON (f.ax_id = p.ax_id)
                         INNER JOIN  profile_job_enum  AS pe ON (pe.AX_code = f.Code_etab)
                         INNER JOIN  profile_job       AS pj ON (p.pid = pj.pid AND pj.jobid = pe.id)
                              WHERE  f.Type_adr = 'E' AND f.tel != ''");
XDB::rawExecute("INSERT IGNORE INTO  profile_phones (link_type, link_id, tel_id, tel_type, pid, display_tel)
                             SELECT  'pro', pj.id, IF(f.tel = '', 0, 1), 'fax', p.pid, f.fax
                               FROM  fusionax_adresses AS f
                         INNER JOIN  profiles          AS p  ON (f.ax_id = p.ax_id)
                         INNER JOIN  profile_job_enum  AS pe ON (pe.AX_code = f.Code_etab)
                         INNER JOIN  profile_job       AS pj ON (p.pid = pj.pid AND pj.jobid = pe.id)
                              WHERE  f.Type_adr = 'E' AND f.fax != ''");
// Drops job related tables and addresses
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_entreprises');
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_activites');
XDB::rawExecute("DELETE FROM fusionax_adresses WHERE Type_adr = 'P'");
echo "Jobs inclusions finished.\n";

// Retrieves information from fusionax_anciens: promo, nationality, corps, email, phone, deathdate.
// Updates uncertain promotions, but when we are we are right.
echo "Starts various informations inclusions.\n";
XDB::rawExecute("UPDATE  profile_merge_issues AS pm, fusionax_anciens AS f, profiles AS p, profile_display AS pd, profile_education AS pe
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'promo'), 'promo'), pm.entry_year_ax = f.promotion_etude
                  WHERE  pm.pid = p.pid AND p.ax_id = f.ax_id AND p.pid NOT IN (18399,21099,40616)
                         AND pd.pid = p.pid AND pe.pid = p.pid AND FIND_IN_SET('primary', pe.flags)
                         AND pd.promo != CONCAT('X', f.promotion_etude)
                         AND !(f.promotion_etude = pe.entry_year + 1 AND pe.grad_year = pe.entry_year + 4)");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues, entry_year_ax)
                             SELECT  p.pid, 'promo', f.promotion_etude
                               FROM  profiles             AS p
                         INNER JOIN  profile_display   AS pd ON (p.pid = pd.pid)
                         INNER JOIN  profile_education AS pe ON (p.pid = pe.pid)
                         INNER JOIN  fusionax_anciens  AS f  ON (p.ax_id = f.ax_id)
                              WHERE  pd.promo != CONCAT('X', f.promotion_etude) AND p.pid NOT IN (18399,21099,40616)
                                     AND !(f.promotion_etude = pe.entry_year + 1 AND pe.grad_year = pe.entry_year + 4)");

// Updates nationality.
XDB::rawExecute('ALTER TABLE geoloc_countries ADD INDEX (licensePlate)');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.ax_id = f.ax_id)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite)
                    SET  p.nationality1 = g.iso_3166_1_a2
                  WHERE  p.nationality1 IS NULL');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.ax_id = f.ax_id)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite)
                    SET  p.nationality2 = g.iso_3166_1_a2
                  WHERE  p.nationality1 != g.iso_3166_1_a2 AND p.nationality2 IS NULL');
XDB::rawExecute('UPDATE  profiles         AS p
             INNER JOIN  fusionax_anciens AS f ON (p.ax_id = f.ax_id)
             INNER JOIN  geoloc_countries AS g ON (g.licensePlate = f.Code_nationalite)
                    SET  p.nationality3 = g.iso_3166_1_a2
                  WHERE  p.nationality1 != g.iso_3166_1_a2 AND p.nationality2 != g.iso_3166_1_a2 AND p.nationality3 IS NULL');
XDB::rawExecute('ALTER TABLE geoloc_countries DROP INDEX licensePlate');

// Updates corps.
XDB::rawExecute("INSERT IGNORE INTO  profile_corps (pid, original_corpsid, current_corpsid, rankid, corps_pub)
                             SELECT  p.pid, c.id, c.id, r.id, 'ax'
                               FROM  profiles AS p
                         INNER JOIN  fusionax_anciens        AS f ON (p.ax_id = f.ax_id)
                         INNER JOIN  profile_corps_enum      AS c ON (f.corps_sortie = c.abbreviation)
                         INNER JOIN  profile_corps_rank_enum AS r ON (f.grade = r.abbreviation)
                              WHERE  NOT EXISTS (SELECT  *
                                                   FROM  profile_corps AS pc
                                                  WHERE  p.pid = pc.pid AND pc.original_corpsid != 1)");
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
             INNER JOIN  fusionax_anciens AS f ON (p.ax_id = f.ax_id)
                    SET  p.email_directory = f.Mel_usage
                  WHERE  f.Mel_publiable != '0' AND f.Mel_usage != '' AND p.email_directory IS NULL");
XDB::rawExecute("INSERT IGNORE INTO  register_marketing (uid, email, type)
                             SELECT  ap.uid, f.Mel_usage, 'ax'
                               FROM  fusionax_anciens AS f
                         INNER JOIN  profiles         AS p  ON (p.ax_id = f.ax_id)
                         INNER JOIN  account_profiles AS ap ON (ap.pid = p.pid AND FIND_IN_SET('owner', perms))
                          LEFT JOIN  emails           AS e  ON (e.uid = ap.uid AND e.flags = 'active')
                              WHERE  f.Mel_usage != '' AND f.Mel_usage NOT LIKE '%@polytechnique.edu'
                                     AND f.Mel_usage NOT LIKE '%@polytechnique.org' AND f.Mel_usage NOT LIKE '%@m4x.org'
                                     AND f.Mel_usage NOT LIKE '%@melix.%' AND e.email IS NULL");

// Retrieves different deathdates.
XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  profiles         AS p ON (pm.pid = p.pid)
             INNER JOIN  fusionax_anciens AS f ON (f.ax_id = p.ax_id)
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'deathdate'), 'deathdate'), pm.deathdate_ax = f.Date_deces
                  WHERE  p.deathdate != f.Date_deces");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues, deathdate_ax)
                             SELECT  p.pid, 'deathdate', f.Date_deces
                               FROM  fusionax_anciens AS f
                         INNER JOIN  profiles         AS p ON (f.ax_id = p.ax_id)
                              WHERE  p.deathdate != f.Date_deces");
echo "Various informations inclusions finished.\n";

// Updates phone.
// We consider there is conflict if a profile has a phone in both databases.
echo "Starts phones inclusions.\n";
XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  profiles             AS p  ON (pm.pid = p.pid)
             INNER JOIN  profile_phones       AS pp ON (pp.pid = p.pid AND pp.link_type = 'user' AND pp.tel_id = 0)
             INNER JOIN  fusionax_anciens     AS f  ON (f.ax_id = p.ax_id)
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'phone'), 'phone')
                  WHERE  f.tel_mobile != ''");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues)
                             SELECT  p.pid, 'phone'
                               FROM  fusionax_anciens AS f
                         INNER JOIN  profiles         AS p ON (f.ax_id = p.ax_id)
                         INNER JOIN  profile_phones   AS pp ON (pp.pid = p.pid AND pp.link_type = 'user' AND pp.tel_id = 0)
                              WHERE  f.tel_mobile != ''");

XDB::rawExecute("INSERT INTO  profile_phones (pid, link_type, tel_id, tel_type, display_tel, pub)
                      SELECT  p.pid, 'user', 0, 'mobile', f.tel_mobile, 'ax'
                        FROM  fusionax_anciens AS f
                  INNER JOIN  profiles         AS p ON (f.ax_id = p.ax_id)
                       WHERE  NOT EXISTS (SELECT  *
                                            FROM  profile_phones AS pp
                                           WHERE  pp.pid = p.pid AND pp.link_type = 'user' AND pp.tel_id = 0)
                              AND f.tel_mobile != ''");
XDB::rawExecute("INSERT INTO  profile_phones (pid, link_type, tel_id, tel_type, display_tel, pub)
                      SELECT  p.pid, 'user', MAX(pp.tel_id) + 1, 'mobile', f.tel_mobile, 'ax'
                        FROM  fusionax_anciens AS f
                  INNER JOIN  profiles         AS p  ON (f.ax_id = p.ax_id)
                  INNER JOIN  profile_phones   AS pp ON (pp.pid = p.pid AND pp.link_type = 'user')
                       WHERE  f.tel_mobile != ''
                    GROUP BY  p.pid");
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_anciens');
echo "Phones inclusions finished.\n";

// Retrieves addresses from AX database (one address per preofile maximum).
echo "Starts addresses inclusions.\n";
XDB::rawExecute('ALTER TABLE profile_addresses ADD INDEX (text(20))');
XDB::rawExecute("DELETE  f
                   FROM  fusionax_adresses AS f
             INNER JOIN  profiles          AS p  ON (f.ax_id = p.ax_id)
             INNER JOIN  profile_addresses AS pa ON (pa.pid = p.pid AND pa.type = 'home')
                  WHERE  pa.text = f.text");
// Deletes addresses of unknown type.
XDB::rawExecute("DELETE FROM  fusionax_adresses
                       WHERE  Type_adr != 'E' AND Type_adr != 'P'");

XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  profiles             AS p  ON (pm.pid = p.pid)
             INNER JOIN  profile_addresses    AS pa ON (pa.pid = p.pid AND pa.type = 'home' AND pa.id = 0)
             INNER JOIN  fusionax_adresses    AS f  ON (f.ax_id = p.ax_id)
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'address'), 'address')
                  WHERE  f.text IS NOT NULL");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues)
                             SELECT  p.pid, 'address'
                               FROM  fusionax_adresses AS f
                         INNER JOIN  profiles          AS p ON (f.ax_id = p.ax_id)
                         INNER JOIN  profile_addresses AS pa ON (pa.pid = p.pid AND pa.type = 'home' AND pa.id = 0)
                              WHERE  f.text IS NOT NULL");

XDB::rawExecute("INSERT INTO  profile_addresses (pid, type, id, pub, text)
                      SELECT  p.pid, 'home', 0, 'ax', f.text
                        FROM  fusionax_adresses AS f
                  INNER JOIN  profiles          AS p ON (f.ax_id = p.ax_id)
                       WHERE  NOT EXISTS (SELECT  *
                                            FROM  profile_addresses AS pa
                                           WHERE  pa.pid = p.pid AND pa.type = 'home' AND pa.id = 0)
                              AND f.text IS NOT NULL");
XDB::rawExecute("INSERT INTO  profile_addresses (pid, type, id, pub, text)
                      SELECT  p.pid, 'home', MAX(pa.id) + 1, 'ax', f.text
                        FROM  fusionax_adresses AS f
                  INNER JOIN  profiles          AS p  ON (f.ax_id = p.ax_id)
                  INNER JOIN  profile_addresses AS pa ON (pa.pid = p.pid AND pa.type = 'home')
                       WHERE  f.text IS NOT NULL
                    GROUP BY  p.pid");
XDB::rawExecute("INSERT INTO  profile_phones (pid, link_type, tel_id, tel_type, display_tel, pub, link_id)
                      SELECT  p.pid, 'address', IF(pp.tel_id IS NULL, 0, MAX(pp.tel_id) + 1), 'fixed', f.tel, 'ax', pa.id
                        FROM  fusionax_adresses AS f
                  INNER JOIN  profiles          AS p  ON (f.ax_id = p.ax_id)
                  INNER JOIN  profile_addresses AS pa ON (pa.pid = p.pid AND pa.type = 'home' AND f.text = pa.text)
                   LEFT JOIN  profile_phones    AS pp ON (pp.pid = p.pid AND pp.link_type = 'address' AND pp.link_id = pa.id)
                       WHERE  f.tel != ''
                    GROUP BY  p.pid");
XDB::rawExecute("INSERT INTO  profile_phones (pid, link_type, tel_id, tel_type, display_tel, pub, link_id)
                      SELECT  p.pid, 'address', IF(pp.tel_id IS NULL, 0, MAX(pp.tel_id) + 1), 'fax', f.fax, 'ax', pa.id
                        FROM  fusionax_adresses AS f
                  INNER JOIN  profiles          AS p  ON (f.ax_id = p.ax_id)
                  INNER JOIN  profile_addresses AS pa ON (pa.pid = p.pid AND pa.type = 'home' AND f.text = pa.text)
                   LEFT JOIN  profile_phones    AS pp ON (pp.pid = p.pid AND pp.link_type = 'address' AND pp.link_id = pa.id)
                       WHERE  f.fax != ''
                    GROUP BY  p.pid");
XDB::rawExecute('ALTER TABLE profile_addresses DROP INDEX text');
XDB::rawExecute('DROP TABLE IF EXISTS fusionax_adresses');
echo "Addresses inclusions finished.\n";

// Retrieves education from AX database. This is the hardest part since AX only kept education as an unformated string.
echo "Starts educations inclusions.\n";
// Insert ids into fusionax_formations to prevent many joins.
XDB::rawExecute('UPDATE  fusionax_formations           AS f
              LEFT JOIN  profile_education_enum        AS pe ON (pe.name = f.Intitule_formation)
              LEFT JOIN  profile_education_degree_enum AS pd ON (pd.abbreviation = f.Intitule_diplome)
              LEFT JOIN  profile_education_field_enum  AS pf ON (pf.field = f.Descr_formation)
                    SET  f.eduid = pe.id, f.degreeid = pd.id, f.fieldid = pf.id');
// Updates non complete educations.
XDB::rawExecute("UPDATE  profile_education             AS e
             INNER JOIN  profiles                      AS p  ON (e.pid = p.pid)
             INNER JOIN  fusionax_formations           AS f  ON (f.ax_id = p.ax_id)
             INNER JOIN  profile_education_degree_enum AS pd ON (e.degreeid = pd.id)
             INNER JOIN  profile_education_degree_enum AS fd ON (f.degreeid = fd.id)
                    SET  e.eduid = f.eduid
                  WHERE  NOT FIND_IN_SET('primary', e.flags) AND e.eduid IS NULL AND pd.level = fd.level");
XDB::rawExecute("UPDATE  profile_education   AS e
             INNER JOIN  profiles            AS p ON (e.pid = p.pid)
             INNER JOIN  fusionax_formations AS f ON (f.ax_id = p.ax_id)
                    SET  e.degreeid = f.degreeid
                  WHERE  NOT FIND_IN_SET('primary', e.flags) AND e.degreeid IS NULL AND e.eduid = f.eduid");
// Deletes duplicates.
XDB::rawExecute("DELETE  f
                   FROM  fusionax_formations           AS f
             INNER JOIN  profiles                      AS p  ON (f.ax_id = p.ax_id)
             INNER JOIN  profile_education_degree_enum AS fd ON (fd.abbreviation = f.Intitule_diplome)
             INNER JOIN  profile_education             AS e  ON (e.pid = p.pid AND NOT FIND_IN_SET('primary', e.flags))
             INNER JOIN  profile_education_degree_enum AS pd ON (pd.id = e.degreeid)
                  WHERE  f.eduid = e.eduid AND fd.level = pd.level");
// Updates merge_issues table.
XDB::rawExecute("UPDATE  profile_merge_issues AS pm
             INNER JOIN  profiles             AS p ON (pm.pid = p.pid)
             INNER JOIN  fusionax_formations  AS f ON (f.ax_id = p.ax_id)
                    SET  pm.issues = IF(pm.issues, CONCAT(pm.issues, ',', 'education'), 'education')");
XDB::rawExecute("INSERT IGNORE INTO  profile_merge_issues (pid, issues)
                             SELECT  p.pid, 'education'
                               FROM  fusionax_formations AS f
                         INNER JOIN  profiles            AS p ON (f.ax_id = p.ax_id)");

$id = 0;
$continue = 1;
while ($continue > 0) {
    XDB::rawExecute("INSERT IGNORE INTO  profile_education (id, pid, eduid, degreeid, fieldid, program)
                                 SELECT  $id, p.pid, f.eduid, f.degreeid, f.fieldid, f.Descr_formation
                                   FROM  fusionax_formations AS f
                             INNER JOIN  profiles            AS p  ON (f.ax_id = p.ax_id)");
    XDB::rawExecute("DELETE  f
                       FROM  fusionax_formations AS f
                 INNER JOIN  profiles            AS p  ON (f.ax_id = p.ax_id)
                 INNER JOIN  profile_education   AS pe ON (pe.pid = p.pid AND pe.id = $id AND pe.eduid = f.eduid AND pe.degreeid = f.degreeid
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
