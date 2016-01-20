<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

/**
 * Module to merge data from AX database: this will only be used once on
 * production site and should be removed afterwards.
 *
 * Module to import data from another database of alumni that had
 * different schemas. The organization that used this db is called AX
 * hence the name of this module.
 *
 * Datas are stored in an export file.
 */
class FusionAxModule extends PLModule
{
    function handlers()
    {
        if (Platal::globals()->merge->state == 'pending') {
            return array(
                'fusionax'                  => $this->make_hook('index',    AUTH_PASSWD, 'admin'),
                'fusionax/import'           => $this->make_hook('import',   AUTH_PASSWD, 'admin'),
                'fusionax/view'             => $this->make_hook('view',     AUTH_PASSWD, 'admin'),
                'fusionax/ids'              => $this->make_hook('ids',      AUTH_PASSWD, 'admin'),
                'fusionax/deceased'         => $this->make_hook('deceased', AUTH_PASSWD, 'admin'),
                'fusionax/promo'            => $this->make_hook('promo',    AUTH_PASSWD, 'admin'),
                'fusionax/names'            => $this->make_hook('names',    AUTH_PASSWD, 'admin'),
                'fusionax/edu'              => $this->make_hook('edu',      AUTH_PASSWD, 'admin'),
                'fusionax/corps'            => $this->make_hook('corps',    AUTH_PASSWD, 'admin')
            );
        } elseif (Platal::globals()->merge->state == 'done') {
            return array(
                'fusionax'                  => $this->make_hook('index',            AUTH_PASSWD, 'admin,edit_directory'),
                'fusionax/issues'           => $this->make_hook('issues',           AUTH_PASSWD, 'admin,edit_directory'),
                'fusionax/issues/deathdate' => $this->make_hook('issues_deathdate', AUTH_PASSWD, 'admin,edit_directory'),
                'fusionax/issues/promo'     => $this->make_hook('issues_promo',     AUTH_PASSWD, 'admin,edit_directory'),
            );
        }
    }


    function handler_index($page)
    {
        if (Platal::globals()->merge->state == 'pending') {
            $page->changeTpl('fusionax/index.tpl');
        } elseif (Platal::globals()->merge->state == 'done') {
            $issueList = array(
                'name'      => 'noms',
                'job'       => 'emplois',
                'address'   => 'adresses',
                'promo'     => 'promotions',
                'deathdate' => 'dates de décès',
                'phone'     => 'téléphones',
                'education' => 'formations',
            );
            $issues = XDB::rawFetchOneAssoc("SELECT  COUNT(*) AS total,
                                                     SUM(FIND_IN_SET('name', issues))      DIV 1 AS name,
                                                     SUM(FIND_IN_SET('job', issues))       DIV 2 AS job,
                                                     SUM(FIND_IN_SET('address', issues))   DIV 3 AS address,
                                                     SUM(FIND_IN_SET('promo', issues))     DIV 4 AS promo,
                                                     SUM(FIND_IN_SET('deathdate', issues)) DIV 5 AS deathdate,
                                                     SUM(FIND_IN_SET('phone', issues))     DIV 6 AS phone,
                                                     SUM(FIND_IN_SET('education', issues)) DIV 7 AS education
                                               FROM  profile_merge_issues
                                              WHERE  issues IS NOT NULL OR issues != ''");
            $page->changeTpl('fusionax/issues.tpl');
            $page->assign('issues', $issues);
            $page->assign('issueList', $issueList);
        }
    }

    /** Import de l'annuaire de l'AX depuis l'export situé dans le home de jacou */
    function handler_import($page, $action = 'index', $file = '')
    {
        global $globals;
        if ($action == 'index') {
            $page->changeTpl('fusionax/import.tpl');
            return;
        }

        // toutes les actions sont faites en ajax en utilisant jquery
        header('Content-type: text/javascript; charset=utf-8');

        // log des actions
        $report = array();

        $modulepath = realpath(dirname(__FILE__) . '/fusionax/') . '/';
        $spoolpath = realpath(dirname(__FILE__) . '/../spool/fusionax/') . '/';

        if ($action == 'launch') {
            if ($file == '') {
                $report[] = 'Nom de fichier non renseigné.';
            } elseif (!file_exists(dirname(__FILE__) . '/../spool/fusionax/' . $file)) {
                $report[] = 'Le fichier ne se situe pas au bon endroit.';
            } else {
                // séparation de l'archive en fichiers par tables
                $file = $spoolpath . $file;
                // Split export into specialised files
                exec('grep "^AD" ' . $file . ' > ' . $spoolpath . 'Adresses.txt');
                exec('grep "^AN" ' . $file . ' > ' . $spoolpath . 'Anciens.txt');
                exec('grep "^FO.[0-9]\{4\}[MD][0-9]\{3\}.Etudiant" ' . $file . ' > ' . $spoolpath . 'Formations_MD.txt');
                exec('grep "^FO.[0-9]\{4\}[MD][0-9]\{3\}.Doct. de" ' . $file . ' >> ' . $spoolpath . 'Formations_MD.txt');
                exec('grep "^FO" ' . $file . ' > ' . $spoolpath . 'Formations.txt');
                exec('grep "^AC" ' . $file . ' > ' . $spoolpath . 'Activites.txt');
                exec('grep "^EN" ' . $file . ' > ' . $spoolpath . 'Entreprises.txt');
                exec($modulepath . 'formation.pl');
                exec('mv -f ' . $spoolpath . 'Formations_out.txt ' . $spoolpath . 'Formations.txt');
                exec('mv -f ' . $spoolpath . 'Formations_MD_out.txt ' . $spoolpath . 'Formations_MD.txt');
                $report[] = 'Fichier parsé.';
                $report[] = 'Import dans la base en cours...';
                XDB::execute("UPDATE  profiles
                                 SET  ax_id = NULL
                               WHERE  ax_id = ''");
                $next = 'integrateSQL';
            }
        } elseif ($action == 'integrateSQL') {
            // intégration des données dans la base MySQL
            // liste des fichiers sql à exécuter
            $filesSQL = array(
                0 => 'Activites.sql',
                1 => 'Adresses.sql',
                2 => 'Anciens.sql',
                3 => 'Formations.sql',
                4 => 'Entreprises.sql',
                5 => 'Formations_MD.sql'
            );
            if ($file != '') {
                // récupère le contenu du fichier sql
                $queries = explode(';', file_get_contents($modulepath . $filesSQL[$file]));
                $db = mysqli_init();
                $db->options(MYSQLI_OPT_LOCAL_INFILE, true);
                $db->real_connect($globals->dbhost, $globals->dbuser, $globals->dbpwd, $globals->dbdb);
                $db->autocommit(true);
                $db->set_charset($globals->dbcharset);
                foreach ($queries as $q) {
                    if (trim($q)) {
                        // coupe le fichier en requêtes individuelles
                        if (substr($q, 0, 2) == '--') {
                            // affiche les commentaires dans le report
                            $lines = explode("\n", $q);
                            $l = $lines[0];
                            $report[] = addslashes($l);
                        }
                        // exécute la requête
                        $res = $db->query(str_replace('{?}', $spoolpath, $q));
                        if ($res === false) {
                            throw new XDBException($q, $db->error);
                        }
                    }
                }
                $db->close();
                // trouve le prochain fichier à exécuter
                $nextfile = $file + 1;
            } else {
                $nextfile = 0;
            }
            if ($nextfile > 5) {
                // tous les fichiers ont été exécutés, on passe à l'étape suivante
                $next = 'adds1920';
            } else {
                // on passe au fichier suivant
                $next = 'integrateSQL/' . $nextfile;
            }
        } elseif ($action == 'adds1920') {
            // Adds promotion 1920 from AX db.
            $report[] = 'Ajout de la promotion 1920';
            $res = XDB::iterator('SELECT  prenom, Nom_complet, ax_id
                                    FROM  fusionax_anciens
                                   WHERE  promotion_etude = 1920;');

            $eduSchools = DirEnum::getOptions(DirEnum::EDUSCHOOLS);
            $eduSchools = array_flip($eduSchools);
            $eduDegrees = DirEnum::getOptions(DirEnum::EDUDEGREES);
            $eduDegrees = array_flip($eduDegrees);
            $degreeid = $eduDegrees[Profile::DEGREE_X];
            $entry_year = 1920;
            $grad_year = 1923;
            $promo = 'X1920';
            $hrpromo = '1920';
            $sex = 'male';
            $xorgId = 19200000;
            $type = 'x';

            while ($new = $res->next()) {
                $firstname = $new['prenom'];
                $lastname = $new['Nom_complet'];
                $ax_id = $new['ax_id'];
                $hrid = User::makeHrid($firstname, $lastname, $hrpromo);
                $res1 = XDB::query('SELECT  COUNT(*)
                                      FROM  accounts
                                     WHERE  hruid = {?}', $hrid);
                $res2 = XDB::query('SELECT  COUNT(*)
                                      FROM  profiles
                                     WHERE  hrpid = {?}', $hrid);
                if (is_null($hrid) || $res1->fetchOneCell() > 0 || $res2->fetchOneCell() > 0) {
                    $report[] = $ax_id . ' non ajouté';
                }
                $fullName = $firstname . ' ' . $lastname;
                $directoryName = $lastname . ' ' . $firstname;
                ++$xorgId;

                XDB::execute('INSERT INTO  profiles (hrpid, xorg_id, ax_id, sex)
                                   VALUES  ({?}, {?}, {?}, {?})',
                             $hrid, $xorgId, $ax_id, $sex);
                $pid = XDB::insertId();
                XDB::execute('INSERT INTO  profile_public_names (pid, lastname_initial, firstname_initial, lastname_main, firstname_main)
                                   VALUES  ({?}, {?}, {?}, {?}, {?})',
                             $pid, $lastname, $firstname, $lastname, $firstname);
                XDB::execute('INSERT INTO  profile_display (pid, yourself, public_name, private_name,
                                                            directory_name, short_name, sort_name, promo)
                                   VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                             $pid, $firstname, $fullName, $fullName, $directoryName, $fullName, $directoryName, $promo);
                XDB::execute('INSERT INTO  profile_education (pid, eduid, degreeid, entry_year, grad_year, flags)
                                   VALUES  ({?}, {?}, {?}, {?}, {?}, {?})',
                             $pid, $eduSchools[Profile::EDU_X], $degreeid, $entry_year, $grad_year, 'primary');
                XDB::execute('INSERT INTO  accounts (hruid, type, is_admin, state, full_name, directory_name, display_name, lastname, firstname, sex)
                                   VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                             $hrid, $type, 0, 'pending', $fullName, $directoryName, $firstname, $lastname, $firstname, $sex);
                $uid = XDB::insertId();
                XDB::execute('INSERT INTO  account_profiles (uid, pid, perms)
                                   VALUES  ({?}, {?}, {?})',
                             $uid, $pid, 'owner');
            }
            $report[] = 'Promo 1920 ajoutée.';
            $next = 'view';
        } elseif ($action == 'view') {
            XDB::execute('CREATE OR REPLACE ALGORITHM=MERGE VIEW  fusionax_xorg_anciens AS
                                                          SELECT  p.pid, p.ax_id, pd.promo, pd.private_name, pd.public_name,
                                                                  pd.sort_name, pd.short_name, pd.directory_name
                                                            FROM  profiles        AS p
                                                      INNER JOIN  profile_display AS pd ON (pd.pid = p.pid)');
            $next = 'clean';
        } elseif ($action == 'clean') {
            // nettoyage du fichier temporaire
            //exec('rm -Rf ' . $spoolpath);
            $report[] = 'Import finit.';
        }
        foreach($report as $t) {
            // affiche les lignes de report
            echo "$('#fusionax').append('" . $t . "<br/>');\n";
        }
        if (isset($next)) {
            // lance le prochain script s'il y en a un
            echo "$.getScript('fusionax/import/" . $next . "');";
        }
        // exit pour ne pas afficher la page template par défaut
        exit;
    }

    function handler_view($page, $action = '')
    {
        $page->changeTpl('fusionax/view.tpl');
        if ($action == 'create') {
            XDB::execute('DROP VIEW IF EXISTS fusionax_deceased');
            XDB::execute("CREATE VIEW  fusionax_deceased AS
                               SELECT  p.pid, a.ax_id, pd.private_name, pd.promo, p.deathdate AS deces_xorg, a.Date_deces AS deces_ax
                                 FROM  profiles         AS p
                           INNER JOIN  profile_display  AS pd ON (p.pid = pd.pid)
                           INNER JOIN  fusionax_anciens AS a ON (a.ax_id = p.ax_id)
                                WHERE  p.deathdate != a.Date_deces OR (p.deathdate IS NULL AND a.Date_deces != '0000-00-00')");
            XDB::execute('DROP VIEW IF EXISTS fusionax_promo');
            XDB::execute("CREATE VIEW  fusionax_promo AS
                               SELECT  p.pid, p.ax_id, pd.private_name, pd.promo, pe.entry_year AS promo_etude_xorg, f.groupe_promo,
                                       f.promotion_etude AS promo_etude_ax, pe.grad_year AS promo_sortie_xorg
                                 FROM  profiles          AS p
                           INNER JOIN  profile_display   AS pd ON (p.pid = pd.pid)
                           INNER JOIN  profile_education AS pe ON (p.pid = pe.pid AND FIND_IN_SET('primary', pe.flags))
                           INNER JOIN  fusionax_anciens  AS f  ON (p.ax_id = f.ax_id)
                                WHERE  (f.groupe_promo = 'X' AND pd.promo != CONCAT('X', f.promotion_etude)
                                       AND !(f.promotion_etude = pe.entry_year + 1 AND pe.grad_year = pe.entry_year + 4)
                                       AND !(f.promotion_etude = pe.entry_year + 2 AND pe.grad_year = pe.entry_year + 5)
                                       AND f.promotion_etude != 0)
                                       OR (f.groupe_promo = 'D' AND f.promotion_etude != pe.grad_year)
                                       OR (f.groupe_promo = 'M' AND f.promotion_etude != pe.entry_year)
                             GROUP BY  p.pid");
            $page->trigSuccess('Les VIEW ont bien été créées.');
        }
    }

    /* Mets à NULL le matricule_ax de ces camarades pour marquer le fait qu'ils ne figurent pas dans l'annuaire de l'AX */
    private static function clear_wrong_in_xorg($pid)
    {
        $res = XDB::execute('UPDATE  fusionax_xorg_anciens
                                SET  ax_id = NULL
                              WHERE  pid = {?}', $pid);
        if (!$res) {
            return 0;
        }
        return XDB::affectedRows() / 2;
    }

    /* Cherche les les anciens présents dans Xorg avec un matricule_ax ne correspondant à rien dans la base de l'AX
     * (mises à part les promo 1921, 1922, 1923, 1924, 1925, 1927, 1928, 1929 qui ne figurent pas dans les données de l'AX)*/
    private static function find_wrong_in_xorg($limit = 10)
    {
        return XDB::iterator('SELECT  u.promo, u.pid, u.private_name, u.ax_id
                                FROM  fusionax_xorg_anciens AS u
                               WHERE  NOT EXISTS (SELECT  *
                                                    FROM  fusionax_anciens AS f
                                                   WHERE  f.ax_id = u.ax_id)
                                      AND u.ax_id IS NOT NULL
                                      AND promo NOT IN (\'X1921\', \'X1922\', \'X1923\', \'X1924\', \'X1925\', \'X1927\', \'X1928\', \'X1929\')');
    }

    /** Lier les identifiants d'un ancien dans les deux annuaires
     * @param user_id identifiant dans l'annuaire X.org
     * @param matricule_ax identifiant dans l'annuaire de l'AX
     * @return 0 si la liaison a échoué, 1 sinon
     */
    private static function link_by_ids($pid, $ax_id)
    {
        $res = XDB::execute('UPDATE  fusionax_import       AS i
                         INNER JOIN  fusionax_xorg_anciens AS u
                                SET  u.ax_id = i.ax_id,
                                     i.pid = u.pid,
                                     i.date_match_id = NOW()
                              WHERE  i.ax_id = {?} AND u.pid = {?}
                                     AND (u.ax_id != {?} OR u.ax_id IS NULL
                                          OR i.pid != {?} OR i.pid IS NULL)',
                            $ax_id, $pid, $ax_id, $pid);
        if (!$res) {
            return 0;
        }
        return XDB::affectedRows() / 2;
    }

    /** Recherche automatique d'anciens à lier entre les deux annuaires
     * @param limit nombre d'anciens à trouver au max
     * @param sure si true, ne trouve que des anciens qui sont quasi sûrs
     * @return un XOrgDBIterator sur les entrées avec display_name, promo,
     *   pid, ax_id et display_name_ax
     */
    private static function find_easy_to_link($limit = 10, $sure = false)
    {
        $easy_to_link = XDB::iterator("
        SELECT  u.private_name, u.promo, u.pid, ax.ax_id,
                CONCAT(ax.prenom, ' ', ax.nom_complet, ' (', ax.groupe_promo, ax.promotion_etude, ')') AS display_name_ax,
                COUNT(*) AS nbMatches
          FROM  fusionax_anciens      AS ax
    INNER JOIN  fusionax_import       AS i ON (i.ax_id = ax.ax_id AND i.pid IS NULL)
     LEFT JOIN  fusionax_xorg_anciens AS u ON (u.ax_id IS NULL
                                               AND u.promo = CONCAT(ax.groupe_promo, ax.promotion_etude)
                                               AND (CONCAT(ax.prenom, ' ', ax.nom_complet) = u.private_name
                                                    OR CONCAT(ax.prenom, ' ', ax.nom_complet) = u.public_name
                                                    OR CONCAT(ax.prenom, ' ', ax.nom_complet) = u.short_name))
      GROUP BY  u.pid
        HAVING  u.pid IS NOT NULL AND nbMatches = 1" . ($limit ? (' LIMIT ' . $limit) : ''));
        if ($easy_to_link->total() > 0 || $sure) {
            return $easy_to_link;
        }
        return XDB::iterator("
        SELECT  u.private_name, u.promo, u.pid, ax.ax_id,
                CONCAT(ax.prenom, ' ', ax.nom_complet, ' (', ax.groupe_promo, ax.promotion_etude, ')') AS display_name_ax,
                COUNT(*) AS nbMatches
          FROM  fusionax_anciens      AS ax
    INNER JOIN  fusionax_import       AS i ON (i.ax_id = ax.ax_id AND i.pid IS NULL)
     LEFT JOIN  fusionax_xorg_anciens AS u ON (u.ax_id IS NULL
                                               AND (CONCAT(ax.prenom, ' ', ax.nom_complet) = u.private_name
                                                    OR CONCAT(ax.prenom, ' ', ax.nom_complet) = u.public_name
                                                    OR CONCAT(ax.prenom, ' ', ax.nom_complet) = u.short_name)
                                               AND u.promo < CONCAT(ax.groupe_promo, ax.promotion_etude + 2)
                                               AND u.promo > CONCAT(ax.groupe_promo, ax.promotion_etude - 2))
      GROUP BY  u.pid
        HAVING  u.pid IS NOT NULL AND nbMatches = 1" . ($limit ? (' LIMIT ' . $limit) : ''));
    }

    /** Module de mise en correspondance les ids */
    function handler_ids($page, $part = 'main', $pid = null, $ax_id = null)
    {
        $nbToLink = 100;
        $page->assign('xorg_title', 'Polytechnique.org - Fusion - Mise en correspondance simple');

        if ($part == 'missingInAX') {
            // locate all persons from this database that are not in AX's
            $page->changeTpl('fusionax/idsMissingInAx.tpl');
            $missingInAX = XDB::iterator('SELECT  promo, pid, private_name
                                            FROM  fusionax_xorg_anciens
                                           WHERE  ax_id IS NULL
                                        ORDER BY  promo');
            $page->assign('missingInAX', $missingInAX);
            return;
        }
        if ($part == 'missingInXorg') {
            // locate all persons from AX's database that are not here
            $page->changeTpl('fusionax/idsMissingInXorg.tpl');
            $missingInXorg = XDB::iterator("SELECT  CONCAT(a.prenom, ' ', a.Nom_usuel) AS private_name,
                                                    CONCAT(a.groupe_promo, a.promotion_etude) AS promo, a.ax_id
                                              FROM  fusionax_import
                                        INNER JOIN  fusionax_anciens AS a USING (ax_id)
                                             WHERE  fusionax_import.pid IS NULL
                                          ORDER BY  promo");
            $page->assign('missingInXorg', $missingInXorg);
            return;
        }
        if ($part == 'wrongInXorg') {
            // locate all persons from Xorg database that have a bad AX id
            $page->changeTpl('fusionax/idswrongInXorg.tpl');
            $wrongInXorg = FusionAxModule::find_wrong_in_xorg($nbToLink);
            $page->assign('wrongInXorg', $wrongInXorg);
            return;
        }
        if ($part == 'cleanwronginxorg') {
            $linksToDo = FusionAxModule::find_wrong_in_xorg($nbToLink);
            while ($l = $linksToDo->next()) {
                FusionAxModule::clear_wrong_in_xorg($l['pid']);
            }
            pl_redirect('fusionax/ids/wrongInXorg');
        }
        if ($part == 'lier') {
            if (Post::has('user_id') && Post::has('matricule_ax')) {
                FusionAxModule::link_by_ids(Post::i('pid'), Post::v('ax_id'));
            }
        }
        if ($part == 'link') {
            FusionAxModule::link_by_ids($pid, $ax_id);
            exit;
        }
        if ($part == 'linknext') {
            $linksToDo = FusionAxModule::find_easy_to_link($nbToLink);
            while ($l = $linksToDo->next()) {
                FusionAxModule::link_by_ids($l['pid'], $l['ax_id']);
            }
            pl_redirect('fusionax/ids#autolink');
        }
        if ($part == 'linkall') {
            $linksToDo = FusionAxModule::find_easy_to_link(0);
            while ($l = $linksToDo->next()) {
                FusionAxModule::link_by_ids($l['pid'], $l['ax_id']);
            }
        }
        {
            $page->changeTpl('fusionax/ids.tpl');
            $missingInAX = XDB::query('SELECT  COUNT(*)
                                         FROM  fusionax_xorg_anciens
                                        WHERE  ax_id IS NULL');
            if ($missingInAX) {
                $page->assign('nbMissingInAX', $missingInAX->fetchOneCell());
            }
            $missingInXorg = XDB::query('SELECT  COUNT(*)
                                           FROM  fusionax_import
                                          WHERE  pid IS NULL');
            if ($missingInXorg) {
                $page->assign('nbMissingInXorg', $missingInXorg->fetchOneCell());
            }
            $wrongInXorg = FusionAxModule::find_wrong_in_xorg($nbToLink);
            if ($wrongInXorg->total() > 0) {
                $page->assign('wrongInXorg', $wrongInXorg->total());
            }
            $easyToLink = FusionAxModule::find_easy_to_link($nbToLink);
            if ($easyToLink->total() > 0) {
                $page->assign('nbMatch', $easyToLink->total());
                $page->assign('easyToLink', $easyToLink);
            }
        }
    }

    function handler_deceased($page, $action = '')
    {
        if ($action == 'updateXorg') {
            XDB::execute('UPDATE  fusionax_deceased
                             SET  deces_xorg = deces_ax
                           WHERE  deces_xorg IS NULL');
        }
        if ($action == 'updateAX') {
            XDB::execute('UPDATE  fusionax_deceased
                             SET  deces_ax = deces_xorg
                           WHERE  deces_ax = "0000-00-00"');
        }
        if ($action == 'update') {
            if (Post::has('pid') && Post::has('date')) {
                XDB::execute('UPDATE  fusionax_deceased
                                 SET  deces_ax = {?}, deces_xorg = {?}
                               WHERE  pid = {?}',
                             Post::v('date'), Post::v('date'), Post::i('pid'));
            }
        }
        $page->changeTpl('fusionax/deceased.tpl');
        // deceased
        $deceasedErrorsSql = XDB::query('SELECT COUNT(*) FROM fusionax_deceased');
        $page->assign('deceasedErrors', $deceasedErrorsSql->fetchOneCell());
        $res = XDB::iterator('SELECT  pid, ax_id, promo, private_name, deces_ax
                                FROM  fusionax_deceased
                               WHERE  deces_xorg IS NULL
                               LIMIT  10');
        $page->assign('nbDeceasedMissingInXorg', $res->total());
        $page->assign('deceasedMissingInXorg', $res);
        $res = XDB::iterator('SELECT  pid, ax_id, promo, private_name, deces_xorg
                                FROM  fusionax_deceased
                               WHERE  deces_ax = "0000-00-00"
                               LIMIT  10');
        $page->assign('nbDeceasedMissingInAX', $res->total());
        $page->assign('deceasedMissingInAX', $res);
        $res = XDB::iterator('SELECT  pid, ax_id, promo, private_name, deces_xorg, deces_ax
                                FROM  fusionax_deceased
                               WHERE  deces_xorg != "0000-00-00" AND deces_ax != "0000-00-00"');
        $page->assign('nbDeceasedDifferent', $res->total());
        $page->assign('deceasedDifferent', $res);
    }

    function handler_promo($page, $action = '')
    {
        $page->changeTpl('fusionax/promo.tpl');
        $res = XDB::iterator("SELECT  pid, ax_id, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  !(promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 3 = promo_sortie_xorg)
                                      AND !(promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 4 = promo_sortie_xorg)
                                      AND !(promo_etude_ax = promo_etude_xorg + 1) AND groupe_promo = 'X'
                            ORDER BY  promo_etude_xorg");
        $nbMissmatchingPromos = $res->total();
        $page->assign('nbMissmatchingPromos', $res->total());
        $page->assign('missmatchingPromos', $res);

        $res = XDB::iterator("SELECT  pid, ax_id, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  promo_etude_ax = promo_etude_xorg + 1 AND groupe_promo = 'X'
                            ORDER BY  promo_etude_xorg");
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromos1', $res->total());
        $page->assign('missmatchingPromos1', $res);

        $res = XDB::iterator("SELECT  pid, ax_id, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 3 = promo_sortie_xorg AND groupe_promo = 'X'
                            ORDER BY  promo_etude_xorg");
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromos2', $res->total());
        $page->assign('missmatchingPromos2', $res);

        $res = XDB::iterator("SELECT  pid, ax_id, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 4 = promo_sortie_xorg AND groupe_promo = 'X'
                            ORDER BY  promo_etude_xorg");
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromos3', $res->total());
        $page->assign('missmatchingPromos3', $res);

        $res = XDB::iterator("SELECT  pid, ax_id, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  groupe_promo = 'M'
                            ORDER BY  promo_etude_xorg");
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromosM', $res->total());
        $page->assign('missmatchingPromosM', $res);


        $res = XDB::iterator("SELECT  pid, ax_id, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  groupe_promo = 'D'
                            ORDER BY  promo_etude_xorg");
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromosD', $res->total());
        $page->assign('missmatchingPromosD', $res);

        $page->assign('nbMissmatchingPromosTotal', $nbMissmatchingPromos);
    }

    private function format($string)
    {
        return preg_replace('/(\s+|\-)/', '', $string);
    }

    private function retrieve_firstnames()
    {
        $res = XDB::rawFetchAllAssoc('SELECT  p.pid, p.ax_id, p.hrpid,
                                              f.prenom, ppn.firstname_initial, ppn.firstname_main, ppn.firstname_ordinary
                                        FROM  fusionax_anciens     AS f
                                  INNER JOIN  profiles             AS p   ON (f.ax_id = p.ax_id)
                                  INNER JOIN  profile_public_names AS ppn ON (p.pid = ppn.pid)
                                       WHERE  f.prenom NOT IN (ppn.firstname_initial, ppn.firstname_main, ppn.firstname_ordinary)');

        $issues = array();
        foreach ($res as $item) {
            if (!($item['firstname_ordinary'] != '' || $item['firstname_main'] != $item['firstname_initial'])) {
                $ax = $this->format(mb_strtolower(replace_accent($item['prenom'])));
                $xorg = $this->format(mb_strtolower(replace_accent($item['firstname_main'])));
                if ($ax != $xorg) {
                    $issues[] = $item;
                }
            }
        }

        return $issues;
    }

    function handler_names($page, $action = '', $csv = false)
    {
        $page->changeTpl('fusionax/names.tpl');

        if ($action == 'first') {
            $res = $this->retrieve_firstnames();
            if ($csv) {
                pl_cached_content_headers('text/x-csv', 'utf-8', 1, 'firstnames.csv');

                $csv = fopen('php://output', 'w');
                fputcsv($csv,  array('pid', 'ax_id', 'hrpid', 'AX', 'initial', 'principal', 'ordinaire'), ';');
                foreach ($res as $item) {
                    fputcsv($csv, $item, ';');
                }
                fclose($csv);
                exit();
            } else {
                $page->assign('firstnameIssues', $res);
            }
        } elseif ($action == 'last' || $action == 'last3' || $action == 'last2' || $action == 'last1') {
            // Define some variables to build queries
            function sql_trim_partic($sqlstring) {
                $sqlstring = 'TRIM(LEADING \'d\\\'\' FROM ' . $sqlstring . ')';
                $sqlstring = 'TRIM(LEADING \'D\\\'\' FROM ' . $sqlstring . ')';
                $sqlstring = 'TRIM(LEADING \'de \' FROM ' . $sqlstring . ')';
                $sqlstring = 'TRIM(LEADING \'De \' FROM ' . $sqlstring . ')';
                $sqlstring = 'TRIM(LEADING \'du \' FROM ' . $sqlstring . ')';
                $sqlstring = 'TRIM(LEADING \'Du \' FROM ' . $sqlstring . ')';
                return $sqlstring;
            }
            //$field_ax_patro = 'IF(f.partic_patro, CONCAT(f.partic_patro, CONCAT(\' \', f.Nom_patronymique)), f.Nom_patronymique)';
            //$field_ax_usuel = 'IF(f.partic_nom, CONCAT(f.partic_nom, CONCAT(\' \', f.Nom_usuel)), f.Nom_usuel)';
            $fields_p_list = '(' . \
                sql_trim_partic('ppn.lastname_initial') . ', ' . \
                sql_trim_partic('ppn.lastname_main') . ', ' . \
                sql_trim_partic('ppn.lastname_marital') . ', ' . \
                sql_trim_partic('ppn.lastname_ordinary') . ')';
            $ax_patro = '(' . sql_trim_partic('f.Nom_patronymique') . ' NOT IN ' . $fields_p_list . ')';
            $ax_ordinary = '(' . sql_trim_partic('f.Nom_usuel') . ' NOT IN ' . $fields_p_list . ')';
            $ax_full = '(' . sql_trim_partic('f.Nom_complet') . ' NOT IN ' . $fields_p_list . ')';

            switch ($action) {
              case 'last':
                $where = $ax_patro . ' OR ' . $ax_ordinary . ' OR ' . $ax_full;
                break;
              case 'last3':
                $where = $ax_patro . ' AND ' . $ax_ordinary . ' AND ' . $ax_full;
                break;
              case 'last2':
                $where = '(' . $ax_patro . ' AND ' . $ax_ordinary . ' AND NOT ' . $ax_full . ') OR ('
                       . $ax_patro . ' AND NOT ' . $ax_ordinary . ' AND ' . $ax_full . ') OR ('
                       . 'NOT ' . $ax_patro . ' AND ' . $ax_ordinary . ' AND ' . $ax_full . ')';
                break;
              case 'last1':
                $where = '(' . $ax_patro . ' AND NOT ' . $ax_ordinary . ' AND NOT ' . $ax_full . ') OR ('
                       . 'NOT ' . $ax_patro . ' AND NOT ' . $ax_ordinary . ' AND ' . $ax_full . ') OR ('
                       . 'NOT ' . $ax_patro . ' AND ' . $ax_ordinary . ' AND NOT ' . $ax_full . ')';
                break;
            }

            $res = XDB::rawFetchAllAssoc('SELECT  p.pid, p.ax_id, p.hrpid,
                                                  f.Nom_patronymique, f.Nom_usuel, f.Nom_complet,
                                                  ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary,
                                                  ' . $ax_patro . ' AS cond_patro,
                                                  ' . $ax_ordinary . ' AS cond_ordinary,
                                                  ' . $ax_full . ' AS cond_full
                                            FROM  fusionax_anciens     AS f
                                      INNER JOIN  profiles             AS p   ON (f.ax_id = p.ax_id)
                                      INNER JOIN  profile_public_names AS ppn ON (p.pid = ppn.pid)
                                           WHERE  ' . $where . '
                                        ORDER BY  p.ax_id');

            if ($csv) {
                function format($string)
                {
                    $string = preg_replace('/\-/', ' ', $string);
                    return preg_replace('/\s+/', ' ', $string);
                }


                pl_cached_content_headers('text/x-csv', 'utf-8', 1, 'lastnames.csv');

                $csv = fopen('php://output', 'w');
                fputcsv($csv,  array(
                    'pid', 'ax_id', 'hrpid',
                    'AX patro', 'AX usuel', 'AX complet',
                    'initial', 'principal', 'marital', 'ordinaire',
                    'pb patro', 'pb usuel', 'pb complet'), ';');
                foreach ($res as $item) {
                    $ax = array(
                        'Nom_patronymique' => format(mb_strtolower(replace_accent($item['Nom_patronymique']))),
                        'Nom_usuel'        => format(mb_strtolower(replace_accent($item['Nom_usuel']))),
                        'Nom_complet'      => format(mb_strtolower(replace_accent($item['Nom_complet'])))
                    );
                    $xorg = array(
                        'lastname_initial'  => format(mb_strtolower(replace_accent($item['lastname_initial']))),
                        'lastname_main'     => format(mb_strtolower(replace_accent($item['lastname_main']))),
                        'lastname_ordinary' => format(mb_strtolower(replace_accent($item['lastname_ordinary'])))
                    );

                    if (!in_array($ax['Nom_patronymique'], $xorg) || !in_array($ax['Nom_usuel'], $xorg) || !in_array($ax['Nom_complet'], $xorg)) {
                        fputcsv($csv, $item, ';');
                    }
                }
                fclose($csv);
                exit();
            } else {
                $page->assign('lastnameIssues', $res);
                $page->assign('total', count($res));
                $page->assign('issuesTypes', array(
                        'last'  => "1, 2 ou 3 noms de l'AX manquant",
                        'last1' => "1 nom de l'AX manquant",
                        'last2' => "2 noms de l'AX manquant",
                        'last3' => "3 noms de l'AX manquant"
                ));
            }
        } else {
            $res = XDB::query('SELECT  COUNT(*)
                                 FROM  fusionax_anciens AS f
                           INNER JOIN  profiles         AS p    ON (f.ax_id = p.ax_id)');
            $page->assign('total', $res->fetchOneCell());

            $res = XDB::rawFetchOneCell("SELECT  COUNT(*)
                                           FROM  fusionax_anciens     AS f
                                     INNER JOIN  profiles             AS p   ON (f.ax_id = p.ax_id)
                                     INNER JOIN  profile_public_names AS ppn ON (p.pid = ppn.pid)
                                          WHERE  IF(f.partic_patro, CONCAT(f.partic_patro, CONCAT(' ', f.Nom_patronymique)), f.Nom_patronymique) NOT IN (ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary)
                                                 OR IF(f.partic_nom, CONCAT(f.partic_nom, CONCAT(' ', f.Nom_usuel)), f.Nom_usuel) NOT IN (ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary)
                                                 OR f.Nom_complet NOT IN (ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary)");
            $page->assign('lastnameIssues', $res);

            $res = XDB::rawFetchOneCell('SELECT  COUNT(*)
                                           FROM  fusionax_anciens     AS f
                                     INNER JOIN  profiles             AS p   ON (f.ax_id = p.ax_id)
                                     INNER JOIN  profile_public_names AS ppn ON (p.pid = ppn.pid)
                                          WHERE  f.prenom NOT IN (ppn.firstname_initial, ppn.firstname_main, ppn.firstname_ordinary)');
            $page->assign('firstnameIssues', count($this->retrieve_firstnames()));
        }
        $page->assign('action', $action);
    }

    function handler_edu($page, $action = '')
    {
        $page->changeTpl('fusionax/education.tpl');

        $missingEducation = XDB::rawIterator("SELECT  DISTINCT(f.Intitule_formation)
                                                FROM  fusionax_formations AS f
                                               WHERE  f.Intitule_formation != '' AND NOT EXISTS (SELECT  *
                                                                                                   FROM  profile_education_enum AS e
                                                                                                  WHERE  f.Intitule_formation = e.name)");
        $missingDegree = XDB::rawIterator("SELECT  DISTINCT(f.Intitule_diplome)
                                             FROM  fusionax_formations AS f
                                            WHERE  f.Intitule_diplome != '' AND NOT EXISTS (SELECT  *
                                                                                              FROM  profile_education_degree_enum AS e
                                                                                             WHERE  f.Intitule_diplome = e.abbreviation)");
        $missingCouple = XDB::rawIterator("SELECT  DISTINCT(f.Intitule_formation) AS edu, f.Intitule_diplome AS degree, ee.id AS eduid, de.id AS degreeid
                                             FROM  fusionax_formations           AS f
                                       INNER JOIN  profile_education_enum        AS ee ON (f.Intitule_formation = ee.name)
                                       INNER JOIN  profile_education_degree_enum AS de ON (f.Intitule_diplome = de.abbreviation)
                                            WHERE  f.Intitule_diplome != '' AND f.Intitule_formation != ''
                                                   AND NOT EXISTS (SELECT  *
                                                                     FROM  profile_education_degree AS d
                                                                    WHERE  ee.id = d.eduid AND de.id = d.degreeid)");

        $page->assign('missingEducation', $missingEducation);
        $page->assign('missingDegree', $missingDegree);
        $page->assign('missingCouple', $missingCouple);
        $page->assign('missingEducationCount', $missingEducation->total());
        $page->assign('missingDegreeCount', $missingDegree->total());
        $page->assign('missingCoupleCount', $missingCouple->total());
    }

    function handler_corps($page)
    {
        $page->changeTpl('fusionax/corps.tpl');

        $missingCorps = XDB::rawIterator('SELECT  DISTINCT(f.corps_sortie) AS name
                                            FROM  fusionax_anciens AS f
                                           WHERE  NOT EXISTS (SELECT  *
                                                                FROM  profile_corps_enum AS c
                                                               WHERE  f.corps_sortie = c.abbreviation)');
        $missingGrade = XDB::rawIterator('SELECT  DISTINCT(f.grade) AS name
                                            FROM  fusionax_anciens AS f
                                           WHERE  NOT EXISTS (SELECT  *
                                                                FROM  profile_corps_rank_enum AS c
                                                               WHERE  f.grade = c.name)');

        $page->assign('missingCorps', $missingCorps);
        $page->assign('missingGrade', $missingGrade);
        $page->assign('missingCorpsCount', $missingCorps->total());
        $page->assign('missingGradeCount', $missingGrade->total());
    }

    function handler_issues_deathdate($page, $action = '')
    {
        $page->changeTpl('fusionax/deathdate_issues.tpl');
        if ($action == 'edit') {
            S::assert_xsrf_token();

            $issues = XDB::rawIterRow('SELECT  p.pid, pd.directory_name, pd.promo, pm.deathdate_ax, p.deathdate
                                         FROM  profile_merge_issues AS pm
                                   INNER JOIN  profiles             AS p  ON (pm.pid = p.pid)
                                   INNER JOIN  profile_display      AS pd ON (pd.pid = p.pid)
                                        WHERE  FIND_IN_SET(\'deathdate\', pm.issues)
                                     ORDER BY  pd.directory_name');
            while (list($pid, $name, $promo, $deathAX, $deathXorg) = $issues->next()) {
                $choiceAX = Post::has('AX_' . $pid);
                $choiceXorg = Post::has('XORG_' . $pid);
                if (!($choiceAX || $choiceXorg)) {
                    continue;
                }

                if ($choiceAX) {
                    XDB::execute('UPDATE  profiles             AS p
                              INNER JOIN  profile_merge_issues AS pm ON (pm.pid = p.pid)
                                     SET  p.deathdate = pm.deathdate_ax, p.deathdate_rec = NOW()
                                   WHERE  p.pid = {?}', $pid);
                }
                XDB::execute("UPDATE  profile_merge_issues
                                 SET  issues = REPLACE(issues, 'deathdate', '')
                               WHERE  pid = {?}", $pid());
                $page->trigSuccess("La date de décès de $name ($promo) a bien été corrigée.");
            }
        }

        $issues = XDB::rawFetchAllAssoc('SELECT  p.pid, p.hrpid, pd.directory_name, pd.promo, pm.deathdate_ax, p.deathdate
                                           FROM  profile_merge_issues AS pm
                                     INNER JOIN  profiles             AS p  ON (pm.pid = p.pid)
                                     INNER JOIN  profile_display      AS pd ON (pd.pid = p.pid)
                                          WHERE  FIND_IN_SET(\'deathdate\', pm.issues)
                                       ORDER BY  pd.directory_name');
        $page->assign('issues', $issues);
        $page->assign('total', count($issues));
    }

    function handler_issues_promo($page, $action = '')
    {
        $page->changeTpl('fusionax/promo_issues.tpl');
        if ($action == 'edit') {
            S::assert_xsrf_token();

            $issues = XDB::rawIterRow('SELECT  p.pid, pd.directory_name, pd.promo, pm.entry_year_ax, pe.entry_year, pe.grad_year
                                         FROM  profile_merge_issues AS pm
                                   INNER JOIN  profiles             AS p  ON (pm.pid = p.pid)
                                   INNER JOIN  profile_display      AS pd ON (pd.pid = p.pid)
                                   INNER JOIN  profile_education    AS pe ON (pe.pid = p.pid AND FIND_IN_SET(\'primary\', pe.flags))
                                        WHERE  FIND_IN_SET(\'promo\', pm.issues)
                                     ORDER BY  pd.directory_name');
            while (list($pid, $name, $promo, $deathAX, $deathXorgEntry, $deathXorgGrad) = $issues->next()) {
                $choiceXorg = Post::has('XORG_' . $pid);
                if (!(Post::has('display_' . $pid) && Post::has('entry_' . $pid) && Post::has('grad_' . $pid))) {
                    continue;
                }

                $display = Post::i('display_' . $pid);
                $entry = Post::i('entry_' . $pid);
                $grad = Post::i('grad_' . $pid);
                if (!(($grad <= $entry + 5 && $grad >= $entry + 3) && ($display >= $entry && $display <= $grad - 3))) {
                    $page->trigError("La promotion de $name n'a pas été corrigée.");
                    continue;
                }
                XDB::execute('UPDATE  profile_display
                                 SET  promo = {?}
                               WHERE  pid = {?}', 'X' . $display, $pid);
                XDB::execute('UPDATE  profile_education
                                 SET  entry_year = {?}, grad_year = {?}
                               WHERE  pid = {?} AND FIND_IN_SET(\'primary\', flags)', $entry, $grad, $pid);
                $page->trigSuccess("La promotion de $name a bien été corrigée.");
            }
        }

        $issues = XDB::rawFetchAllAssoc('SELECT  p.pid, p.hrpid, pd.directory_name, pd.promo, pm.entry_year_ax, pe.entry_year, pe.grad_year
                                           FROM  profile_merge_issues AS pm
                                     INNER JOIN  profiles             AS p  ON (pm.pid = p.pid)
                                     INNER JOIN  profile_display      AS pd ON (pd.pid = p.pid)
                                     INNER JOIN  profile_education    AS pe ON (pe.pid = p.pid AND FIND_IN_SET(\'primary\', pe.flags))
                                          WHERE  FIND_IN_SET(\'promo\', pm.issues)
                                       ORDER BY  pd.directory_name');
        $page->assign('issues', $issues);
        $page->assign('total', count($issues));
    }

    function handler_issues($page, $action = '')
    {
        static $issueList = array(
            'name'      => 'noms',
            'phone'     => 'téléphones',
            'education' => 'formations',
            'address'   => 'adresses',
            'job'       => 'emplois'
        );
        static $typeList = array(
            'name'      => 'general',
            'phone'     => 'general',
            'education' => 'general',
            'address'   => 'adresses',
            'job'       => 'emploi'
        );

        if (!array_key_exists($action, $issueList)) {
            pl_redirect('fusionax');
        } else {
            $total = XDB::fetchOneCell('SELECT  COUNT(*)
                                          FROM  profile_merge_issues
                                         WHERE  FIND_IN_SET({?}, issues)', $action);
            if ($total == 0) {
                pl_redirect('fusionax');
            }

            $issues = XDB::fetchAllAssoc('SELECT  p.hrpid, pd.directory_name, pd.promo
                                            FROM  profile_merge_issues AS pm
                                      INNER JOIN  profiles             AS p  ON (pm.pid = p.pid)
                                      INNER JOIN  profile_display      AS pd ON (pd.pid = p.pid)
                                           WHERE  FIND_IN_SET({?}, pm.issues)
                                        ORDER BY  pd.directory_name
                                           LIMIT  100', $action);

            $page->changeTpl('fusionax/other_issues.tpl');
            $page->assign('issues', $issues);
            $page->assign('issue', $issueList[$action]);
            $page->assign('type', $typeList[$action]);
            $page->assign('total', $total);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
