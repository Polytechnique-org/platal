<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

class SearchModule extends PLModule
{
    function handlers()
    {
        return array(
            'search'                    => $this->make_hook('quick',              AUTH_PUBLIC),
            'search/adv'                => $this->make_hook('advanced',           AUTH_COOKIE, 'directory_ax'),
            'advanced_search.php'       => $this->make_hook('redir_advanced',     AUTH_PUBLIC),
            'search/autocomplete'       => $this->make_hook('autocomplete',       AUTH_COOKIE, 'directory_ax', NO_AUTH),
            'search/list'               => $this->make_hook('list',               AUTH_COOKIE, 'directory_ax', NO_AUTH),
            'search/list/count'         => $this->make_hook('list_count',         AUTH_COOKIE, 'directory_ax', NO_AUTH),
            'jobs'                      => $this->make_hook('referent',           AUTH_COOKIE),
            'emploi'                    => $this->make_hook('referent',           AUTH_COOKIE),
            'referent/search'           => $this->make_hook('referent',           AUTH_COOKIE),
            'search/referent/countries' => $this->make_hook('referent_countries', AUTH_COOKIE),
        );
    }

    function handler_redir_advanced($page, $mode = null)
    {
        pl_redirect('search/adv');
        exit;
    }

    function form_prepare()
    {
        Platal::page()->assign('formulaire',1);
    }

    /** 
     * $model: The way of presenting the results: minifiche, trombi, geoloc.
     * $byletter: Show only names beginning with this letter
     */
    function handler_quick($page, $model = null, $byletter = null)
    {
        global $globals;

        if (Env::has('quick') || $model == 'geoloc') {
            $quick = Env::t('quick');
            if (S::logged() && !Env::has('page')) {
                S::logger()->log('search', 'quick=' . $quick);
            }

            if ($quick == '') {
                $page->trigWarning('Aucun critère de recherche n\'est spécifié.');
                $page->changeTpl('search/index.tpl');
                $page->setTitle('Annuaire');
                $page->assign('formulaire', 1);
                return;
            }

            $list = 'profile|prf|fiche|fic|referent|ref|mentor';
            if (S::admin()) {
                $list .= '|admin|adm|ax';
            }
            $suffixes = array_keys(DirEnum::getOptions(DirEnum::ACCOUNTTYPES));
            $suffixes = implode('|', $suffixes);
            if (preg_match('/^(' . $list . '):([-a-z]+(\.[-a-z]+(\.(?:[md]?\d{2,4}|' . $suffixes . '))?)?)$/', replace_accent($quick), $matches)) {
                $login = $matches[2];
                switch($matches[1]) {
                  case 'admin': case 'adm':
                    $base = 'admin/user/';
                    break;
                  case 'ax':
                    $base = 'profile/ax/';
                    break;
                  case 'profile': case 'prf': case 'fiche': case 'fic':
                    $base = 'profile/';
                    break;
                  case 'referent': case 'ref': case 'mentor':
                    $base = 'referent/';
                    break;
                }

                $user = User::getSilent($login);
                if ($user) {
                    pl_redirect($base . $user->login());
                }
                Get::set('quick', $login);
            } elseif (strpos($quick, 'doc:') === 0) {
                $url = 'Docs/Recherche?';
                $url .= 'action=search&q=' . urlencode(substr($quick, 4));
                $url .= '&group=' . urlencode('-Equipe,-Main,-PmWiki,-Site,-Review');
                pl_redirect($url);
            } elseif (strpos($quick, 'trombi:') === 0) {
                $promo = substr($quick, 7);
                $res = XDB::query("SELECT  diminutif
                                     FROM  groups
                                    WHERE  cat = 'Promotions' AND diminutif = {?}",
                                  $promo);
                if ($res->numRows() == 0) {
                    $page->trigWarning("La promotion demandée n'est pas valide: $promo");
                } else {
                    http_redirect('http://www.polytechnique.net/login/' . $promo . '/annuaire/trombi');
                }
            }

            $page->assign('formulaire', 0);

            require_once 'userset.inc.php';
            $view = new QuickSearchSet();
            $view->addMod('minifiche', 'Mini-fiches', true, array('with_score' => true, 'starts_with' => $byletter));
            if (S::logged() && !Env::i('nonins')) {
                $view->addMod('trombi', 'Trombinoscope', false, array('with_promo' => true, 'with_score' => true));
                $view->addMod('map', 'Planisphère');
            }
            $view->apply('search', $page, $model);

            $nb_tot = $view->count();
            $page->assign('search_results_nb', $nb_tot);
            if (!S::logged() && $nb_tot > $globals->search->public_max) {
                $page->trigError('Votre recherche a généré trop de résultats pour un affichage public.');
            } elseif ($nb_tot > $globals->search->private_max) {
                $page->trigError('Recherche trop générale. Une <a href="search/adv">recherche avancée</a> permet de préciser la recherche.');
            } elseif (empty($nb_tot)) {
                $page->trigError('Il n\'existe personne correspondant à ces critères dans la base !');
            }
        } else {
            $page->assign('formulaire',1);
        }

        $page->changeTpl('search/index.tpl');
        $page->setTitle('Annuaire');
    }

    /** $model is the way of presenting the results: minifiche, trombi, geoloc.
     */
    function handler_advanced($page, $model = null, $byletter = null)
    {
        global $globals;
        $page->assign('advanced',1);

        $networks = DirEnum::getOptions(DirEnum::NETWORKS);
        $networks[-1] = 'Tous types';
        $networks[0] = '-';
        ksort($networks);
        $page->assign('networking_types', $networks);
        $origin_corps_list = DirEnum::getOptions(DirEnum::CURRENTCORPS);
        $current_corps_list = DirEnum::getOptions(DirEnum::ORIGINCORPS);
        $corps_rank_list = DirEnum::getOptions(DirEnum::CORPSRANKS);
        $origin_corps_list[0] = '-';
        $current_corps_list[0] = '-';
        $corps_rank_list[0] = '-';
        ksort($origin_corps_list);
        ksort($current_corps_list);
        ksort($corps_rank_list);
        $page->assign('origin_corps_list', $origin_corps_list);
        $page->assign('current_corps_list', $current_corps_list);
        $page->assign('corps_rank_list', $corps_rank_list);

        if (!Env::has('rechercher') && $model != 'geoloc') {
            $this->form_prepare();
        } else {
            if (!Env::has('page')) {
                S::logger()->log('search', 'adv=' . var_export($_GET, true));
            }

            require_once 'userset.inc.php';
            // Enable X.org fields for X.org admins, and AX fields for AX secretaries.
            $view = new AdvancedSearchSet(S::admin(),
                                          S::user()->checkPerms(User::PERM_EDIT_DIRECTORY));

            if (!$view->isValid()) {
                $this->form_prepare();
                $page->trigError('Recherche invalide.');
            } else {
                $view->addMod('minifiche', 'Mini-fiches', true, array('starts_with' => $byletter));
                $view->addMod('trombi', 'Trombinoscope', false, array('with_promo' => true));
                $view->addMod('map', 'Planisphère');
                if (S::user()->checkPerms(User::PERM_EDIT_DIRECTORY) || S::admin()) {
                    $view->addMod('addresses', 'Adresses postales', false);
                }
                $view->apply('search/adv', $page, $model);

                $nb_tot = $view->count();
                if ($nb_tot > $globals->search->private_max) {
                    $this->form_prepare();
                    if ($model != 'addresses' && (S::user()->checkPerms(User::PERM_EDIT_DIRECTORY) || S::admin())) {
                        $page->assign('suggestAddresses', true);
                    }
                    $page->trigError('Recherche trop générale.');
                } else if ($nb_tot == 0) {
                    $this->form_prepare();
                    $page->trigError('Il n\'existe personne correspondant à ces critères dans la base !');
                }
            }
        }

        $page->changeTpl('search/index.tpl', $model == 'mini' ? SIMPLE : SKINNED);
        $page->assign('public_directory',0);
    }

    function handler_autocomplete($page, $type = null)
    {
        // Autocompletion : according to type required, return
        // a list of results matching with the number of matches.
        // The output format is :
        //   result1|nb1
        //   result2|nb2
        //   ...
        pl_content_headers("text/plain");
        $q = preg_replace(array('/\*+$/', // always look for $q*
                                '/([\^\$\[\]])/', // escape special regexp char
                                '/\*/'), // replace joker by regexp joker
                           array('',
                                 '\\\\\1',
                                 '.*'),
                           Env::s('q'));
        if (!$q) exit();

        // try to look in cached results
        $cache = XDB::query('SELECT  result
                               FROM  search_autocomplete
                              WHERE  name = {?} AND
                                     query = {?} AND
                                     generated > NOW() - INTERVAL 1 DAY',
                             $type, $q);
        if ($res = $cache->fetchOneCell()) {
            echo $res;
            die();
        }

        $enums = array(
            'binetTxt'           => DirEnum::BINETS,
            'groupexTxt'         => DirEnum::GROUPESX,
            'sectionTxt'         => DirEnum::SECTIONS,
            'networking_typeTxt' => DirEnum::NETWORKS,
            'localityTxt'        => DirEnum::LOCALITIES,
            'countryTxt'         => DirEnum::COUNTRIES,
            'entreprise'         => DirEnum::COMPANIES,
            'jobtermTxt'         => DirEnum::JOBTERMS,
            'description'        => DirEnum::JOBDESCRIPTION,
            'nationaliteTxt'     => DirEnum::NATIONALITIES,
            'schoolTxt'          => DirEnum::EDUSCHOOLS,
        );
        if (!array_key_exists($type, $enums)) {
            exit();
        }

        $enum = $enums[$type];

        $list = DirEnum::getAutoComplete($enum, $q);
        $nbResults = 0;
        $res = "";
        while ($result = $list->next()) {
            $nbResults++;
            if ($nbResults == 11) {
                $res .= $q."|-1\n";
            } else {
                $res .= $result['field'].'|';
                if (isset($result['nb'])) {
                    $res .= $result['nb'];
                }
                if (isset($result['id'])) {
                    $res  .= '|'.$result['id'];
                }
                $res .= "\n";
            }
        }
        if ($nbResults == 0) {
            $res = $q."|-2\n";
        }
        XDB::query('INSERT INTO  search_autocomplete (name, query, result, generated)
                         VALUES  ({?}, {?}, {?}, NOW())
        ON DUPLICATE KEY UPDATE  result = VALUES(result), generated = VALUES(generated)',
                   $type, $q, $res);
        echo $res;
        exit();
    }

    function handler_list($page, $type = null, $idVal = null)
    {
        $page->assign('name', $type);
        $page->assign('with_text_value', true);
        $page->assign('onchange', "document.forms.recherche.{$type}Txt.value = this.options[this.selectedIndex].text");

        // Give the list of all values possible of type and builds a select input for it
        $ids = null;

        switch ($type) {
        case 'binet':
            $ids = DirEnum::getOptionsIter(DirEnum::BINETS);
            break;
          case 'networking_type':
            $ids = DirEnum::getOptionsIter(DirEnum::NETWORKS);
            break;
          case 'country':
            $ids = DirEnum::getOptionsIter(DirEnum::COUNTRIES);
            $page->assign('onchange', 'changeAddressComponents(\'' . $type . '\', this.value)');
            break;
          case 'administrative_area_level_1':
          case 'administrative_area_level_2':
          case 'administrative_area_level_3':
          case 'locality':
            $page->assign('onchange', 'changeAddressComponents(\'' . $type . '\', this.value)');
          case 'sublocality':
            $ids = XDB::iterator("SELECT  pace1.id, pace1.long_name AS field
                                    FROM  profile_addresses_components_enum AS pace1
                              INNER JOIN  profile_addresses_components      AS pac1  ON (pac1.component_id = pace1.id)
                              INNER JOIN  profile_addresses_components      AS pac2  ON (pac1.pid = pac2.pid AND pac1.jobid = pac2.jobid AND pac1.id = pac2.id
                                                                                         AND pac1.groupid = pac2.groupid AND pac1.type = pac2.type)
                              INNER JOIN  profile_addresses_components_enum AS pace2 ON (pac2.component_id = pace2.id AND FIND_IN_SET({?}, pace2.types))
                                   WHERE  pace2.id = {?} AND FIND_IN_SET({?}, pace1.types) AND pac1.type = 'home'
                                GROUP BY  pace1.long_name",
                                 Env::v('previous'), Env::v('value'), $type);
            break;
          case 'diploma':
            if (Env::has('school') && Env::i('school') != 0) {
              $ids = DirEnum::getOptionsIter(DirEnum::EDUDEGREES, Env::i('school'));
            } else {
              $ids = DirEnum::getOptionsIter(DirEnum::EDUDEGREES);
            }
            break;
          case 'groupex':
            $ids = DirEnum::getOptionsIter(DirEnum::GROUPESX);
            break;
          case 'nationalite':
            $ids = DirEnum::getOptionsIter(DirEnum::NATIONALITIES);
            break;
          case 'school':
            $ids = DirEnum::getOptionsIter(DirEnum::EDUSCHOOLS);
            $page->assign('onchange', 'changeSchool(this.value)');
            break;
          case 'section':
            $ids = DirEnum::getOptionsIter(DirEnum::SECTIONS);
            break;
          case 'jobterm':
            if (Env::has('jtid')) {
                JobTerms::ajaxGetBranch($page, JobTerms::ONLY_JOBS);
                return;
            } else {
                pl_content_headers('text/xml');
                echo '<div>'; // global container so that response is valid xml
                echo '<input name="jobtermTxt" type="text" style="display:none" size="32" />';
                echo '<input name="jobterm" type="hidden"/>';
                echo '<div class="term_tree"></div>'; // container where to create the tree
                echo '<script type="text/javascript" src="javascript/jquery.jstree.js"></script>';
                echo '<script type="text/javascript" src="javascript/jobtermstree.js"></script>';
                echo '<script type="text/javascript">createJobTermsTree(".term_tree", "search/list/jobterm", "search", "searchForJobTerm");</script>';
                echo '</div>';
                exit();
            }
          default: exit();
        }
        if (isset($idVal)) {
            pl_content_headers("text/plain");
            echo $ids[$idVal];
            exit();
        }
        pl_content_headers("text/xml");
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('list', $ids);
    }

    function handler_referent($page, $action = null, $subaction = null)
    {
        global $globals;

        $wp = new PlWikiPage('Docs.Emploi');
        $wp->buildCache();

        $page->setTitle('Emploi et Carrières');

        // Count mentors
        $res = XDB::query("SELECT count(distinct pid) FROM profile_mentor_term");
        $page->assign('mentors_number', $res->fetchOneCell());

        // Search for mentors matching filters
        require_once 'ufbuilder.inc.php';
        $ufb = new UFB_MentorSearch();
        if (!$ufb->isEmpty()) {
            require_once 'userset.inc.php';
            $ufc = $ufb->getUFC();
            $set = new ProfileSet($ufc);
            $set->addMod('mentor', 'Référents');
            $set->apply('referent/search', $page, $action, $subaction);
            $nb_tot = $set->count();
            if ($nb_tot > $globals->search->private_max) {
                $this->form_prepare();
                $page->trigError('Recherche trop générale.');
                $page->assign('plset_count', 0);
            } else if ($nb_tot == 0) {
                $this->form_prepare();
                $page->trigError('Il n\'existe personne correspondant à ces critères dans la base.');
            }
        }

        $page->changeTpl('search/referent.tpl');
    }

    /**
     * Builds a select field to choose among countries that referents
     * know about. Only referents linked to term (jtid) are displayed.
     * @param $jtid id of job term to restrict referents
     */
    function handler_referent_countries($page, $jtid = null)
    {
        pl_content_headers("text/xml");
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('name', 'country');
        $it = XDB::iterator("SELECT  gc.iso_3166_1_a2 AS id, gc.country AS field
                               FROM  geoloc_countries       AS gc
                         INNER JOIN  profile_mentor_country AS mp ON (mp.country = gc.iso_3166_1_a2)
                         INNER JOIN  profile_mentor_term    AS mt ON (mt.pid = mp.pid)
                         INNER JOIN  profile_job_term_relation AS jtr ON (jtr.jtid_2 = mt.jtid)
                              WHERE  jtr.jtid_1 = {?}
                           GROUP BY  gc.iso_3166_1_a2
                           ORDER BY  gc.country", $jtid);
        $page->assign('list', $it);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
