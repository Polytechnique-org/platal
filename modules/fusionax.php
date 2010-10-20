<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
                'fusionax'                  => $this->make_hook('index',    AUTH_MDP, 'admin'),
                'fusionax/import'           => $this->make_hook('import',   AUTH_MDP, 'admin'),
                'fusionax/view'             => $this->make_hook('view',     AUTH_MDP, 'admin'),
                'fusionax/ids'              => $this->make_hook('ids',      AUTH_MDP, 'admin'),
                'fusionax/deceased'         => $this->make_hook('deceased', AUTH_MDP, 'admin'),
                'fusionax/promo'            => $this->make_hook('promo',    AUTH_MDP, 'admin'),
                'fusionax/names'            => $this->make_hook('names',    AUTH_MDP, 'admin')
            );
        } elseif (Platal::globals()->merge->state == 'done') {
            return array(
                'fusionax'                  => $this->make_hook('index',            AUTH_MDP, 'admin,edit_directory'),
                'fusionax/issues'           => $this->make_hook('issues',           AUTH_MDP, 'admin,edit_directory'),
                'fusionax/issues/deathdate' => $this->make_hook('issues_deathdate', AUTH_MDP, 'admin,edit_directory'),
                'fusionax/issues/promo'     => $this->make_hook('issues_promo',     AUTH_MDP, 'admin,edit_directory'),
            );
        }
    }


    function handler_index(&$page)
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
    function handler_import(&$page, $action = 'index', $file = '')
    {
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
                // Removes master and doctorate students
                exec('grep -v "^[A-Z]\{2\}.[0-9]\{4\}[MD][0-9]\{3\}" ' . $file . ' > ' . $file . '.tmp');
                exec('mv -f ' . $file . '.tmp ' . $file);
                // Split export into specialised files
                exec('grep "^AD" ' . $file . ' > ' . $spoolpath . 'Adresses.txt');
                exec('grep "^AN" ' . $file . ' > ' . $spoolpath . 'Anciens.txt');
                exec('grep "^FO" ' . $file . ' > ' . $spoolpath . 'Formations.txt');
                exec('grep "^AC" ' . $file . ' > ' . $spoolpath . 'Activites.txt');
                exec('grep "^EN" ' . $file . ' > ' . $spoolpath . 'Entreprises.txt');
                exec($modulepath . 'formation.pl');
                exec('mv -f ' . $spoolpath . 'Formations_out.txt ' . $spoolpath . 'Formations.txt');
                $report[] = 'Fichier parsé.';
                $report[] = 'Import dans la base en cours...';
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
                4 => 'Entreprises.sql'
            );
            if ($file != '') {
                // récupère le contenu du fichier sql
                $queries = explode(';', file_get_contents($modulepath . $filesSQL[$file]));
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
                        XDB::execute(str_replace('{?}', $spoolpath, $q));
                    }
                }
                // trouve le prochain fichier à exécuter
                $nextfile = $file + 1;
            } else {
                $nextfile = 0;
            }
            if ($nextfile > 4) {
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

            $nameTypes = DirEnum::getOptions(DirEnum::NAMETYPES);
            $nameTypes = array_flip($nameTypes);
            $eduSchools = DirEnum::getOptions(DirEnum::EDUSCHOOLS);
            $eduSchools = array_flip($eduSchools);
            $eduDegrees = DirEnum::getOptions(DirEnum::EDUDEGREES);
            $eduDegrees = array_flip($eduDegrees);
            $degreeid = $eduDegrees[Profile::DEGREE_X];
            $entry_year = 1920;
            $grad_year = 1923;
            $promo = 'X1920';
            $sex = PlUser::GENDER_MALE;
            $xorgId = 19200000;
            $type = 'x';

            while (list($firstname, $lastname, $ax_id) = $res->next()) {
                $hrid = self::getHrid($firstname, $lastname, $promo);
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

                XDB::execute('REPLACE INTO  profiles (hrpid, xorg_id, ax_id, sex)
                                    VALUES  ({?}, {?}, {?}, {?})',
                             $hrid, $xorgId, $ax_id, $sex);
                $pid = XDB::insertId();
                XDB::execute('REPLACE INTO  profile_name (pid, name, typeid)
                                    VALUES  ({?}, {?}, {?})',
                             $pid, $lastname, $nameTypes['name_ini']);
                XDB::execute('REPLACE INTO  profile_name (pid, name, typeid)
                                    VALUES  ({?}, {?}, {?})',
                             $pid, $firstname, $nameTypes['firstname_ini']);
                XDB::execute('REPLACE INTO  profile_display (pid, yourself, public_name, private_name,
                                                             directory_name, short_name, sort_name, promo)
                                    VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                             $pid, $firstname, $fullName, $fullName, $directoryName, $fullName, $directoryName, $promo);
                XDB::execute('REPLACE INTO  profile_education (pid, eduid, degreeid, entry_year, grad_year, flags)
                                    VALUES  ({?}, {?}, {?}, {?}, {?}, {?})',
                             $pid, $eduSchools[Profile::EDU_X], $degreeid, $entry_year, $grad_year, 'primary');
                XDB::execute('REPLACE INTO  accounts (hruid, type, is_admin, state, full_name, directory_name, display_name, sex)
                                    VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})',
                             $hrid, $type, 0, 'active', $fullName, $directoryName, $lastname, $sex);
                $uid = XDB::insertId();
                XDB::execute('REPLACE INTO  account_profiles (uid, pid, perms)
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
                                                      INNER JOIN  profile_display AS pd USING(pid)');
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

    function handler_view(&$page, $action = '')
    {
        $page->changeTpl('fusionax/view.tpl');
        if ($action == 'create') {
            XDB::execute('DROP VIEW IF EXISTS fusionax_deceased');
            XDB::execute('CREATE VIEW  fusionax_deceased AS
                               SELECT  p.pid, a.ax_id, pd.private_name, pd.promo, p.deathdate AS deces_xorg, a.Date_deces AS deces_ax
                                 FROM  profiles         AS p
                           INNER JOIN  profile_display  AS pd ON (p.pid = pd.pid)
                           INNER JOIN  fusionax_anciens AS a ON (a.ax_id = p.ax_id)
                                WHERE  p.deathdate != a.Date_deces');
            XDB::execute('DROP VIEW IF EXISTS fusionax_promo');
            XDB::execute('CREATE VIEW  fusionax_promo AS
                               SELECT  p.pid, p.ax_id, pd.private_name, pd.promo, pe.entry_year AS promo_etude_xorg,
                                       f.promotion_etude AS promo_etude_ax, pe.grad_year AS promo_sortie_xorg
                                 FROM  profiles          AS p
                           INNER JOIN  profile_display   AS pd ON (p.pid = pd.pid)
                           INNER JOIN  profile_education AS pe ON (p.pid = pe.pid)
                           INNER JOIN  fusionax_anciens  AS f  ON (p.ax_id = f.ax_id)
                                WHERE  pd.promo != CONCAT(\'X\', f.promotion_etude)
                                       AND !(f.promotion_etude = pe.entry_year + 1 AND pe.grad_year = pe.entry_year + 4)');
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
     * (mises à part les promo 1921 et 1923 qui ne figurent pas dans les données de l'AX)*/
    private static function find_wrong_in_xorg($limit = 10)
    {
        return XDB::iterator('SELECT  u.promo, u.pid, u.private_name
                                FROM  fusionax_xorg_anciens AS u
                               WHERE  NOT EXISTS (SELECT  *
                                                    FROM  fusionax_anciens AS f
                                                   WHERE  f.ax_id = u.ax_id)
                                      AND u.ax_id IS NOT NULL AND promo != \'X1921\' AND promo != \'X1923\'');
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
                CONCAT(ax.prenom, ' ', ax.nom_complet, ' (X', ax.promotion_etude, ')') AS display_name_ax,
                COUNT(*) AS nbMatches
          FROM  fusionax_anciens      AS ax
    INNER JOIN  fusionax_import       AS i ON (i.ax_id = ax.ax_id AND i.pid IS NULL)
     LEFT JOIN  fusionax_xorg_anciens AS u ON (u.ax_id IS NULL
                                               AND u.promo = CONCAT('X', ax.promotion_etude)
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
                CONCAT(ax.prenom, ' ', ax.nom_complet, ' (X', ax.promotion_etude, ')') AS display_name_ax,
                COUNT(*) AS nbMatches
          FROM  fusionax_anciens      AS ax
    INNER JOIN  fusionax_import       AS i ON (i.ax_id = ax.ax_id AND i.pid IS NULL)
     LEFT JOIN  fusionax_xorg_anciens AS u ON (u.ax_id IS NULL
                                               AND (CONCAT(ax.prenom, ' ', ax.nom_complet) = u.private_name
                                                    OR CONCAT(ax.prenom, ' ', ax.nom_complet) = u.public_name
                                                    OR CONCAT(ax.prenom, ' ', ax.nom_complet) = u.short_name)
                                               AND u.promo < CONCAT('X', ax.promotion_etude + 2)
                                               AND u.promo > CONCAT('X', ax.promotion_etude - 2))
      GROUP BY  u.pid
        HAVING  u.pid IS NOT NULL AND nbMatches = 1" . ($limit ? (' LIMIT ' . $limit) : ''));
    }

    /** Module de mise en correspondance les ids */
    function handler_ids(&$page, $part = 'main', $pid = null, $ax_id = null)
    {
        $nbToLink = 100;
        $page->assign('xorg_title', 'Polytechnique.org - Fusion - Mise en correspondance simple');

        if ($part == 'missingInAX') {
            // locate all persons from this database that are not in AX's
            $page->changeTpl('fusionax/idsMissingInAx.tpl');
            $missingInAX = XDB::iterator('SELECT  promo, pid, private_name
                                            FROM  fusionax_xorg_anciens
                                           WHERE  ax_id IS NULL');
            $page->assign('missingInAX', $missingInAX);
            return;
        }
        if ($part == 'missingInXorg') {
            // locate all persons from AX's database that are not here
            $page->changeTpl('fusionax/idsMissingInXorg.tpl');
            $missingInXorg = XDB::iterator("SELECT  CONCAT(a.prenom, ' ', a.Nom_usuel) AS private_name,
                                                    a.promotion_etude AS promo, a.ax_id
                                              FROM  fusionax_import
                                        INNER JOIN  fusionax_anciens AS a USING (ax_id)
                                             WHERE  fusionax_import.pid IS NULL");
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

    function handler_deceased(&$page, $action = '')
    {
        if ($action == 'updateXorg') {
            XDB::execute('UPDATE  fusionax_deceased
                             SET  deces_xorg = deces_ax
                           WHERE  deces_xorg = "0000-00-00"');
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
                               WHERE  deces_xorg = "0000-00-00"
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

    function handler_promo(&$page, $action = '')
    {
        $page->changeTpl('fusionax/promo.tpl');
        $res = XDB::iterator('SELECT  pid, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  !(promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 3 = promo_sortie_xorg)
                                      AND !(promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 4 = promo_sortie_xorg)
                                      AND !(promo_etude_ax = promo_etude_xorg + 1)
                            ORDER BY  promo_etude_xorg');
        $nbMissmatchingPromos = $res->total();
        $page->assign('nbMissmatchingPromos', $res->total());
        $page->assign('missmatchingPromos', $res);

        $res = XDB::iterator('SELECT  pid, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  promo_etude_ax = promo_etude_xorg + 1
                            ORDER BY  promo_etude_xorg');
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromos1', $res->total());
        $page->assign('missmatchingPromos1', $res);

        $res = XDB::iterator('SELECT  pid, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 3 = promo_sortie_xorg
                            ORDER BY  promo_etude_xorg');
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromos2', $res->total());
        $page->assign('missmatchingPromos2', $res);

        $res = XDB::iterator('SELECT  pid, private_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax, promo
                                FROM  fusionax_promo
                               WHERE  promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 4 = promo_sortie_xorg
                            ORDER BY  promo_etude_xorg');
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromos3', $res->total());
        $page->assign('missmatchingPromos3', $res);

        $page->assign('nbMissmatchingPromosTotal', $nbMissmatchingPromos);
    }

    function handler_names(&$page, $action = '')
    {
        $page->changeTpl('fusionax/names.tpl');

        $res = XDB::query('SELECT  COUNT(*)
                             FROM  fusionax_anciens AS f
                       INNER JOIN  profiles         AS p    ON (f.ax_id = p.ax_id)');
        $page->assign('total', $res->fetchOneCell());

        // To be checked:
        // | lastname           |  1 |
        // | lastname_marital   |  2 |
        // | lastname_ordinary  |  3 |
        // | firstname          |  4 |
        // | firstname_ordinary |  7 |
        // | firstname_other    |  8 |
        // | name_other         |  9 |
        // | name_ini           | 10 |
        // | firstname_ini      | 11 |
        $res = XDB::query("SELECT  COUNT(*)
                             FROM  fusionax_anciens AS f
                       INNER JOIN  profiles         AS p   ON (f.ax_id = p.ax_id)
                        LEFT JOIN  profile_name     AS pnp ON (p.pid = pnp.pid AND pnp.typeid = 1)
                        LEFT JOIN  profile_name     AS pnm ON (p.pid = pnm.pid AND pnm.typeid = 2)
                        LEFT JOIN  profile_name     AS pno ON (p.pid = pno.pid AND pno.typeid = 3)
                        LEFT JOIN  profile_name     AS pne ON (p.pid = pne.pid AND pne.typeid = 9)
                        LEFT JOIN  profile_name     AS pni ON (p.pid = pni.pid AND pni.typeid = 10)
                            WHERE  IF(f.partic_patro, CONCAT(f.partic_patro, CONCAT(' ', f.Nom_patronymique)), f.Nom_patronymique) NOT IN (pnp.name, pno.name, pnm.name, pne.name, pni.name)
                                   OR IF(f.partic_nom, CONCAT(f.partic_nom, CONCAT(' ', f.Nom_usuel)), f.Nom_usuel) NOT IN (pnp.name, pno.name, pnm.name, pne.name, pni.name)
                                   OR f.Nom_complet NOT IN (pnp.name, pno.name, pnm.name, pne.name, pni.name)");
        $page->assign('lastnameIssues', $res->fetchOneCell());

        $res = XDB::query('SELECT  COUNT(*)
                             FROM  fusionax_anciens AS f
                       INNER JOIN  profiles         AS p   ON (f.ax_id = p.ax_id)
                        LEFT JOIN  profile_name     AS pnf ON (p.pid = pnf.pid AND pnf.typeid = 4)
                        LEFT JOIN  profile_name     AS pno ON (p.pid = pno.pid AND pno.typeid = 7)
                        LEFT JOIN  profile_name     AS pne ON (p.pid = pne.pid AND pne.typeid = 8)
                        LEFT JOIN  profile_name     AS pni ON (p.pid = pni.pid AND pni.typeid = 11)
                            WHERE  f.prenom NOT IN (pnf.name, pno.name, pne.name, pni.name)');
        $page->assign('firstnameIssues', $res->fetchOneCell());

    }

    function handler_issues_deathdate(&$page, $action = '')
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

    function handler_issues_promo(&$page, $action = '')
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

    function handler_issues(&$page, $action = '')
    {
        static $issueList = array(
            'name'      => 'noms',
            'phone'     => 'téléphones',
            'education' => 'formations',
            'address'   => 'adresses',
            'job'       => 'emplois'
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
            $page->assign('total', $total);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
