<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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
 * @brief Module to merge data from AX database
 *
 * Module to import data from another database of alumni that had
 * different schemas. The organization that used this db is called AX
 * hence the name of this module.
 *
 * Datas are stored in an external server and you need a private key
 * to connect to their server.
 */
class FusionAxModule extends PLModule
{
    function __construct()
    {
    }

    function handlers()
    {
        return array(
            'fusionax'          => $this->make_hook('index',    AUTH_MDP, 'admin'),
            'fusionax/import'   => $this->make_hook('import',   AUTH_MDP, 'admin'),
            'fusionax/view'     => $this->make_hook('view',     AUTH_MDP, 'admin'),
            'fusionax/ids'      => $this->make_hook('ids',      AUTH_MDP, 'admin'),
            'fusionax/deceased' => $this->make_hook('deceased', AUTH_MDP, 'admin'),
            'fusionax/promo'    => $this->make_hook('promo',    AUTH_MDP, 'admin'),
        );
    }


    function handler_index(&$page)
    {
        $globals = Platal::globals();

        $page->changeTpl('fusionax/index.tpl');
        $page->assign('xorg_title', 'Polytechnique.org - Fusion des annuaires');
        if (isset($globals->fusionax) && isset($globals->fusionax->LastUpdate)) {
            $page->assign('lastimport', date("d-m-Y", $globals->fusionax->LastUpdate));
        }
    }

    /** Import de l'annuaire de l'AX depuis l'export situé dans le home de jacou */
    function handler_import(&$page, $action = 'index', $fileSQL = '')
    {
        $globals = Platal::globals();

        if ($action == 'index') {
            $page->changeTpl('fusionax/import.tpl');
            if (isset($globals->fusionax) && isset($globals->fusionax->LastUpdate)) {
                $page->assign(
                    'lastimport',
                    "le " . date("d/m/Y à H:i", $globals->fusionax->LastUpdate));
            }
            return;
        }

        // toutes les actions sont faites en ajax en utilisant jquery
        header("Content-type: text/javascript; charset=utf-8");

        // log des actions
        $report = array();

        // création d'un fichier temporaire si nécessaire
        if (Env::has('tmpdir')) {
            $tmpdir = Env::v('tmpdir');
        } else {
            $tmpdir = tempnam('/tmp', 'fusionax');
            unlink($tmpdir);
            mkdir($tmpdir);
            chmod($tmpdir, 0700);
        }

        $modulepath = realpath(dirname(__FILE__) . '/fusionax/') . '/';
        $olddir = getcwd();
        chdir($tmpdir);

        if ($action == 'launch') {
            // séparation de l'archive en fichiers par tables
            exec($modulepath . 'import-ax.sh', $report);
            $report[] = 'Fichier parsé.';
            $report[] = 'Import dans la base en cours...';
            $next = 'integrateSQL';
        } elseif ($action == 'integrateSQL') {
            // intégration des données dans la base MySQL
            // liste des fichiers sql à exécuter
            $filesSQL = array(
                'Activites.sql',
                'Adresses.sql',
                'Anciens.sql',
                'Formations.sql',
                'Entreprises.sql');
            if ($fileSQL != '') {
                // récupère le contenu du fichier sql
                $queries = explode(';', file_get_contents($modulepath . $fileSQL));
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
                        XDB::execute($q);
                    }
                }
                // trouve le prochain fichier à exécuter
                $trans = array_flip($filesSQL);
                $nextfile = $trans[$fileSQL] + 1;
            } else {
                $nextfile = 0;
            }
            if (!isset($filesSQL[$nextfile])) {
                // tous les fichiers ont été exécutés, on passe à l'étape
                // suivante
                $next = 'clean';
            } else {
                // on passe au fichier suivant
                $next = 'integrateSQL/' . $filesSQL[$nextfile];
            }
        } elseif ($action == 'clean') {
            // nettoyage du fichier temporaire
            chdir($olddir);
            exec("rm -Rf $tmpdir", $report);
            $report[] = "Fin de l\'import";
            // met à jour la date de dernier import
            //$globals->change_dynamic_config(array('LastUpdate' => time()), 'FusionAx');
        }
        foreach($report as $t) {
            // affiche les lignes de report
            echo "$('#fusionax_import').append('" . $t . "<br/>');\n";
        }
        if (isset($next)) {
            $tmpdir = getcwd();
            chdir($olddir);
            // lance le prochain script s'il y en a un
            echo "$.getScript('fusionax/import/" . $next . "?tmpdir=" . urlencode($tmpdir) . "');";
        }
        // exit pour ne pas afficher la page template par défaut
        exit;
    }

    /** Import de l'annuaire de l'AX depuis l'export situé dans le home de jacou */
    function handler_view(&$page, $action = '')
    {
        $globals = Platal::globals();

        $page->changeTpl('fusionax/view.tpl');
        if ($action == 'create') {
            XDB::execute('DROP VIEW IF EXISTS fusionax_deceased');
            XDB::execute('CREATE VIEW  fusionax_deceased AS
                               SELECT  u.user_id, a.id_ancien, u.nom, u.prenom, u.promo, u.deces AS deces_xorg, a.Date_deces AS deces_ax
                                 FROM  auth_user_md5    AS u
                           INNER JOIN  fusionax_anciens AS a ON (a.id_ancien = u.matricule_ax)
                                WHERE  u.deces != a.Date_deces');
            XDB::execute('DROP VIEW IF EXISTS fusionax_promo');
            XDB::execute('CREATE VIEW  fusionax_promo AS
                               SELECT  u.user_id, u.matricule_ax, CONCAT(u.nom, " ", u.prenom) AS display_name, u.promo AS promo_etude_xorg,
                                       f.promotion_etude AS promo_etude_ax, u.promo_sortie AS promo_sortie_xorg
                                 FROM  auth_user_md5    AS u
                           INNER JOIN  fusionax_anciens AS f ON (u.matricule_ax = f.id_ancien)
                                WHERE  u.promo != f.promotion_etude AND !(f.promotion_etude = u.promo + 1 AND u.promo_sortie = u.promo + 4)');
        }
    }

    /* Mets à NULL le matricule_ax de ces camarades pour marquer le fait qu'ils ne figurent pas dans l'annuaire de l'AX */
    private static function clear_wrong_in_xorg($user_id)
    {
        $res = XDB::execute("UPDATE  fusionax_xorg_anciens
                                SET  matricule_ax = NULL
                              WHERE  user_id = {?}", $user_id);
        if (!$res) {
            return 0;
        }
        return XDB::affectedRows() / 2;
    }

    /* Cherche les les anciens présents dans Xorg avec un matricule_ax ne correspondant à rien dans la base de l'AX 
     * (mises à part les promo 1921 et 1923 qui ne figurent pas dans les données de l'AX)*/
    private static function find_wrong_in_xorg($limit = 10)
    {
        return XDB::iterator("SELECT  u.promo, u.user_id, u.display_name
                                FROM  fusionax_xorg_anciens AS u
                               WHERE  NOT EXISTS (SELECT  *
                                                    FROM  fusionax_anciens AS f
                                                   WHERE  f.id_ancien = u.matricule_ax)
                                      AND u.matricule_ax IS NOT NULL AND promo != 1921 AND promo != 1923");
    }

    /** Lier les identifiants d'un ancien dans les deux annuaires
     * @param user_id identifiant dans l'annuaire X.org
     * @param matricule_ax identifiant dans l'annuaire de l'AX
     * @return 0 si la liaison a échoué, 1 sinon
     */
    private static function link_by_ids($user_id, $matricule_ax)
    {
        $res = XDB::execute("UPDATE  fusionax_import       AS i
                         INNER JOIN  fusionax_xorg_anciens AS u
                                SET  u.matricule_ax = i.id_ancien,
                                     i.user_id = u.user_id,
                                     i.date_match_id = NOW()
                              WHERE  i.id_ancien = {?} AND u.user_id = {?}
                                     AND (u.matricule_ax != {?} OR u.matricule_ax IS NULL
                                          OR i.user_id != {?} OR i.user_id IS NULL)",
                            $matricule_ax, $user_id, $matricule_ax, $user_id);
        if (!$res) {
            return 0;
        }
        return XDB::affectedRows() / 2;
    }

    /** Recherche automatique d'anciens à lier entre les deux annuaires
     * @param limit nombre d'anciens à trouver au max
     * @param sure si true, ne trouve que des anciens qui sont quasi sûrs
     * @return un XOrgDBIterator sur les entrées avec display_name, promo,
     * user_id, id_ancien et display_name_ax
     */
    private static function find_easy_to_link($limit = 10, $sure = false)
    {
        $easy_to_link = XDB::iterator("
        SELECT  u.display_name, u.promo, u.user_id, ax.id_ancien,
                CONCAT(ax.prenom, ' ', ax.nom_complet, ' (X ', ax.promotion_etude, ')') AS display_name_ax,
                COUNT(*) AS nbMatches
          FROM  fusionax_anciens      AS ax
    INNER JOIN  fusionax_import       AS i ON (i.id_ancien = ax.id_ancien AND i.user_id IS NULL)
     LEFT JOIN  fusionax_xorg_anciens AS u ON (u.matricule_ax IS NULL
                                               AND ax.Nom_patronymique = u.nom
                                               AND ax.prenom = u.prenom
                                               AND u.promo = ax.promotion_etude)
      GROUP BY  u.user_id
        HAVING  u.user_id IS NOT NULL AND nbMatches = 1 " . ($limit ? ('LIMIT ' . $limit) : ''));
        if ($easy_to_link->total() > 0 || $sure) {
            return $easy_to_link;
        }
        return XDB::iterator("
        SELECT  u.display_name, u.promo, u.user_id, ax.id_ancien,
                CONCAT(ax.prenom, ' ', ax.nom_complet, ' (X ', ax.promotion_etude, ')') AS display_name_ax,
                COUNT(*) AS nbMatches
          FROM  fusionax_anciens      AS ax
    INNER JOIN  fusionax_import       AS i ON (i.id_ancien = ax.id_ancien AND i.user_id IS NULL)
     LEFT JOIN  fusionax_xorg_anciens AS u ON (u.matricule_ax IS NULL
                                               AND (ax.Nom_patronymique = u.nom
                                                    OR ax.Nom_patronymique LIKE CONCAT(u.nom, ' %')
                                                    OR ax.Nom_patronymique LIKE CONCAT(u.nom, '-%')
                                                    OR ax.Nom_usuel = u.nom
                                                    OR u.nom LIKE CONCAT('% ', ax.Nom_patronymique))
                                               AND u.promo < ax.promotion_etude + 2
                                               AND u.promo > ax.promotion_etude - 2)
      GROUP BY  u.user_id
        HAVING  u.user_id IS NOT NULL AND nbMatches = 1 " . ($limit ? ('LIMIT ' . $limit) : ''));
    }

    /** Module de mise en correspondance les ids */
    function handler_ids(&$page, $part = 'main', $user_id = null, $matricule_ax = null)
    {
        $globals = Platal::globals();
        $nbToLink = 100;

        $page->assign('xorg_title', 'Polytechnique.org - Fusion - Mise en correspondance simple');
        if ($part == 'missingInAX') {
            // locate all persons from this database that are not in AX's
            $page->changeTpl('fusionax/idsMissingInAx.tpl');
            $missingInAX = XDB::iterator("SELECT  promo, user_id, display_name
                                            FROM  fusionax_xorg_anciens
                                           WHERE  matricule_ax IS NULL");
            $page->assign('missingInAX', $missingInAX);
            return;
        }
        if ($part == 'missingInXorg') {
            // locate all persons from AX's database that are not here
            $page->changeTpl('fusionax/idsMissingInXorg.tpl');
            $missingInXorg = XDB::iterator("SELECT  a.promotion_etude AS promo,
                                                    CONCAT(a.prenom, ' ', a.Nom_usuel) AS display_name,
                                                    a.id_ancien
                                              FROM  fusionax_import
                                        INNER JOIN  fusionax_anciens AS a USING (id_ancien)
                                             WHERE  fusionax_import.user_id IS NULL");
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
                FusionAxModule::clear_wrong_in_xorg($l['user_id']);
            }
            pl_redirect('fusionax/ids/wrongInXorg');
        }
        if ($part == 'lier') {
            if (Post::has('user_id') && Post::has('matricule_ax')) {
                FusionAxModule::link_by_ids(Post::i('user_id'), Post::v('matricule_ax'));
            }
        }
        if ($part == 'link') {
            FusionAxModule::link_by_ids($user_id, $matricule_ax);
            exit;
        }
        if ($part == 'linknext') {
            $linksToDo = FusionAxModule::find_easy_to_link($nbToLink);
            while ($l = $linksToDo->next()) {
                FusionAxModule::link_by_ids($l['user_id'], $l['id_ancien']);
            }
            pl_redirect('fusionax/ids#autolink');
        }
        if ($part == 'linkall') {
            $linksToDo = FusionAxModule::find_easy_to_link(0);
            while ($l = $linksToDo->next()) {
                FusionAxModule::link_by_ids($l['user_id'], $l['id_ancien']);
            }
        }
        {
            $page->changeTpl('fusionax/ids.tpl');
            $missingInAX = XDB::query('SELECT  COUNT(*)
                                         FROM  fusionax_xorg_anciens AS u
                                        WHERE  u.matricule_ax IS NULL');
            if ($missingInAX) {
                $page->assign('nbMissingInAX', $missingInAX->fetchOneCell());
            }
            $missingInXorg = XDB::query('SELECT  COUNT(*)
                                           FROM  fusionax_import AS i
                                          WHERE  i.user_id IS NULL');
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
            if (Post::has('user_id') && Post::has('date')) {
                XDB::execute('UPDATE  fusionax_deceased
                                 SET  deces_ax = {?}, deces_xorg = {?}
                               WHERE  user_id = {?}',
                             Post::v('date'), Post::v('date'), Post::i('user_id'));
            }
        }
        $page->changeTpl('fusionax/deceased.tpl');
        // deceased
        $deceasedErrorsSql = XDB::query('SELECT COUNT(*) FROM fusionax_deceased');
        $page->assign('deceasedErrors', $deceasedErrorsSql->fetchOneCell());
        $res = XDB::iterator('SELECT  d.user_id, d.id_ancien, d.nom, d.prenom, d.promo, d.deces_ax,
                                      CONCAT(d.prenom, " ", d.nom) AS display_name
                                FROM  fusionax_deceased AS d
                               WHERE  d.deces_xorg = "0000-00-00"
                               LIMIT  10');
        $page->assign('nbDeceasedMissingInXorg', $res->total());
        $page->assign('deceasedMissingInXorg', $res);
        $res = XDB::iterator('SELECT  d.user_id, d.id_ancien, d.nom, d.prenom, d.promo, d.deces_xorg,
                                      CONCAT(d.prenom, " ", d.nom) AS display_name
                                FROM  fusionax_deceased AS d
                               WHERE  d.deces_ax = "0000-00-00"
                               LIMIT  10');
        $page->assign('nbDeceasedMissingInAX', $res->total());
        $page->assign('deceasedMissingInAX', $res);
        $res = XDB::iterator('SELECT  d.user_id, d.id_ancien, d.nom, d.prenom, d.promo,
                                      d.deces_ax, d.deces_xorg,
                                      CONCAT(d.prenom, " ", d.nom, " ", d.user_id) AS display_name
                                FROM  fusionax_deceased AS d
                               WHERE  d.deces_xorg != "0000-00-00" AND d.deces_ax != "0000-00-00"');
        $page->assign('nbDeceasedDifferent', $res->total());
        $page->assign('deceasedDifferent', $res);
    }

    function handler_promo(&$page, $action = '')
    {
        $page->changeTpl('fusionax/promo.tpl');
        $res = XDB::iterator('SELECT  user_id, display_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax
                                FROM  fusionax_promo
                               WHERE  !(promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 3 = promo_sortie_xorg)');
        $nbMissmatchingPromos = $res->total();
        $page->assign('nbMissmatchingPromos1', $res->total());
        $page->assign('missmatchingPromos1', $res);
        $res = XDB::iterator('SELECT  user_id, display_name, promo_etude_xorg, promo_sortie_xorg, promo_etude_ax
                                FROM  fusionax_promo
                               WHERE  promo_etude_ax + 1 = promo_etude_xorg AND promo_etude_xorg + 3 = promo_sortie_xorg');
        $nbMissmatchingPromos += $res->total();
        $page->assign('nbMissmatchingPromos2', $res->total());
        $page->assign('missmatchingPromos2', $res);
        $page->assign('nbMissmatchingPromos', $nbMissmatchingPromos);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:?>
