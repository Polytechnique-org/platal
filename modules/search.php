<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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
            'search'              => $this->make_hook('quick',          AUTH_PUBLIC),
            'search/adv'          => $this->make_hook('advanced',       AUTH_COOKIE),
            'advanced_search.php' => $this->make_hook('redir_advanced', AUTH_PUBLIC),
            'search/autocomplete' => $this->make_hook('autocomplete',   AUTH_COOKIE, 'user', NO_AUTH),
            'search/list'         => $this->make_hook('list',           AUTH_COOKIE, 'user', NO_AUTH),
        );
    }

    function handler_redir_advanced(&$page, $mode = null)
    {
        pl_redirect('search/adv');
        exit;
    }

    function form_prepare()
    {
        Platal::page()->assign('formulaire',1);
    }

    function get_diplomas($school = null)
    {
        if (is_null($school) && Env::has('school')) {
            $school = Env::i('school');
        }

        if ((!is_null($school)) && ($school != '')) {
            $sql = 'SELECT  degreeid
                      FROM  profile_education_degree
                     WHERE  eduid=' . $school;
        } else {
            $sql = 'SELECT  id
                      FROM  profile_education_degree_enum
                  ORDER BY  id';
        }

        $res = XDB::query($sql);
        Platal::page()->assign('choix_diplomas', $res->fetchColumn());

        $sql = 'SELECT  degree
                  FROM  profile_education_degree_enum
              ORDER BY  id';
        $res = XDB::query($sql);
        Platal::page()->assign('name_diplomas', $res->fetchColumn());
    }

    function handler_quick(&$page, $action = null, $subaction = null)
    {
        global $globals;

        $res = XDB::query("SELECT  MIN(diminutif), MAX(diminutif)
                             FROM  #groupex#.asso
                            WHERE  cat = 'Promotions'");
        list($min, $max) = $res->fetchOneRow();
        $page->assign('promo_min', $min);
        $page->assign('promo_max', $max);

        if (Env::has('quick') || $action == 'geoloc') {
            $quick = trim(Env::v('quick'));
            if (S::logged() && !Env::has('page')) {
                S::logger()->log('search', 'quick=' . $quick);
            }
            $list = 'profile|prf|fiche|fic|referent|ref|mentor';
            if (S::admin()) {
                $list .= '|admin|adm|ax';
            }
            if (preg_match('/^(' . $list . '):([-a-z]+(\.[-a-z]+(\.\d{2,4})?)?)$/', replace_accent($quick), $matches)) {
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
                $_REQUEST['quick'] = $login;
                $_GET['quick'] = $login;
            } elseif (strpos($quick, 'doc:') === 0) {
                $url = 'Docs/Recherche?';
                $url .= 'action=search&q=' . urlencode(substr($quick, 4));
                $url .= '&group=' . urlencode('-Equipe,-Main,-PmWiki,-Site,-Review');
                pl_redirect($url);
            }

            $page->assign('formulaire', 0);

            require_once 'userset.inc.php';
            $view = new SearchSet(true, $action == 'geoloc' && substr($subaction, -3) == 'swf');
            $view->addMod('minifiche', 'Mini-fiches', true, array('with_score' => true));
            if (S::logged() && !Env::i('nonins')) {
                $view->addMod('trombi', 'Trombinoscope', false, array('with_promo' => true, 'with_score' => true));
                $view->addMod('geoloc', 'Planisphère', false, array('with_annu' => 'search/adv'));
            }
            $view->apply('search', $page, $action, $subaction);

            $nb_tot = $view->count();
            $page->assign('search_results_nb', $nb_tot);
            if ($subaction) {
                return;
            }
            if (!S::logged() && $nb_tot > $globals->search->public_max) {
                new ThrowError('Votre recherche a généré trop de résultats pour un affichage public.');
            } elseif ($nb_tot > $globals->search->private_max) {
                new ThrowError('Recherche trop générale. Une <a href="search/adv">recherche avancée</a> permet de préciser la recherche.');
            } elseif (empty($nb_tot)) {
                new ThrowError('Il n\'existe personne correspondant à ces critères dans la base !');
            }
        } else {
            $page->assign('formulaire',1);
            $page->addJsLink('ajax.js');
        }

        $this->load('search.inc.php');
        $page->changeTpl('search/index.tpl');
        $page->setTitle('Annuaire');
    }

    function handler_advanced(&$page, $action = null, $subaction = null)
    {
        global $globals;
        require_once 'geoloc.inc.php';
        $this->load('search.inc.php');
        $page->assign('advanced',1);
        $page->addJsLink('jquery.autocomplete.js');

        if (!Env::has('rechercher') && $action != 'geoloc') {
            $this->form_prepare();
        } else {
            $textFields = array(
                'country'         => array('field' => 'iso_3166_1_a2', 'table' => 'geoloc_countries', 'text' => 'countryFR',
                                           'exact' => false),
                'fonction'        => array('field' => 'id', 'table' => 'fonctions_def', 'text' => 'fonction_fr', 'exact' => true),
                'secteur'         => array('field' => 'id', 'table' => 'profile_job_sector_enum', 'text' => 'name', 'exact' => false),
                'nationalite'     => array('field' => 'iso_3166_1_a2', 'table' => 'geoloc_countries', 
                                           'text' => 'nationalityFR', 'exact' => 'false'),
                'binet'           => array('field' => 'id', 'table' => 'binets_def', 'text' => 'text', 'exact' => false),
                'networking_type' => array('field' => 'network_type', 'table' => 'profile_networking_enum',
                                           'text' => 'name', 'exact' => false),
                'groupex'         => array('field' => 'id', 'table' => '#groupex#.asso',
                                           'text' => "(cat = 'GroupesX' OR cat = 'Institutions') AND pub = 'public' AND nom",
                                           'exact' => false),
                'section'         => array('field' => 'id', 'table' => 'sections', 'text' => 'text', 'exact' => false),
                'school'          => array('field' => 'id', 'table' => 'profile_education_enum', 'text' => 'name', 'exact' => false),
                'city'            => array('table' => 'geoloc_localities', 'text' => 'name', 'exact' => false)
            );
            if (!Env::has('page')) {
                S::logger()->log('search', 'adv=' . var_export($_GET, true));
            }
            foreach ($textFields as $field=>&$query) {
                if (!Env::v($field) && Env::v($field . 'Txt')) {
                    $res = XDB::query("SELECT  {$query['field']}
                                         FROM  {$query['table']}
                                        WHERE  {$query['text']} " . ($query['exact'] ? " = {?}" :
                                                                    " LIKE CONCAT('%', {?}, '%')"),
                                      Env::v($field . 'Txt'));
                    $_REQUEST[$field] = $res->fetchOneCell();
                }
            }

            require_once 'userset.inc.php';
            $view = new SearchSet(false, $action == 'geoloc' && substr($subaction, -3) == 'swf');
            $view->addMod('minifiche', 'Mini-fiches', true);
            $view->addMod('trombi', 'Trombinoscope', false, array('with_promo' => true));
            //$view->addMod('geoloc', 'Planisphère', false, array('with_annu' => 'search/adv'));
            $view->apply('search/adv', $page, $action, $subaction);

            if ($subaction) {
                return;
            }
            $nb_tot = $view->count();
            if ($nb_tot > $globals->search->private_max) {
                $this->form_prepare();
                new ThrowError('Recherche trop générale.');
            }
        }

        $page->changeTpl('search/index.tpl', $action == 'mini' ? SIMPLE : SKINNED);
        $page->addJsLink('ajax.js');
        $page->assign('public_directory',0);
    }

    function handler_autocomplete(&$page, $type = null)
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
                           $_REQUEST['q']);
        if (!$q) exit();

        // try to look in cached results
        $cache = XDB::query('SELECT  `result`
                               FROM  `search_autocomplete`
                              WHERE  `name` = {?} AND
                                     `query` = {?} AND
                                     `generated` > NOW() - INTERVAL 1 DAY',
                             $type, $q);
        if ($res = $cache->fetchOneCell()) {
            echo $res;
            die();
        }

        // default search
        $unique = '`user_id`';
        $db = '`auth_user_md5`';
        $realid = false;
        $beginwith = true;
        $field2 = false;
        $qsearch = str_replace(array('%', '_'), '', $q);
        $distinct = true;

        switch ($type) {
          case 'binetTxt':
            $db = '`binets_def` INNER JOIN
                   `binets_ins` ON(`binets_def`.`id` = `binets_ins`.`binet_id`)';
            $field = '`binets_def`.`text`';
            if (strlen($q) > 2)
                $beginwith = false;
            $realid = '`binets_def`.`id`';
            break;
          case 'networking_typeTxt':
            $db = '`profile_networking_enum` INNER JOIN
                   `profile_networking` ON(`profile_networking`.`network_type` = `profile_networking_enum`.`network_type`)';
            $field = '`profile_networking_enum`.`name`';
            $unique = 'uid';
            $realid = '`profile_networking_enum`.`network_type`';
            break;
          case 'city':
            $db     = 'geoloc_localities INNER JOIN
                       profile_addresses ON (geoloc_localities.id = profile_addresses.localityId)';
            $unique = 'uid';
            $field  ='geoloc_localities.name';
            break;
          case 'countryTxt':
            $db     = 'geoloc_countries INNER JOIN
                       profile_addresses ON (geoloc_countries.iso_3166_1_a2 = profile_addresses.countryId)';
            $unique = 'pid';
            $field  = 'geoloc_countries.countryFR';
            $realid = 'geoloc_countries.iso_3166_1_a2';
            break;
          case 'entreprise':
            $db     = 'profile_job_enum INNER JOIN
                       profile_job ON (profile_job.jobid = profile_job_enum.id)';
            $field  = 'profile_job_enum.name';
            $unique = 'profile_job.uid';
            break;
          case 'fonctionTxt':
            $db        = 'fonctions_def INNER JOIN
                          profile_job ON (profile_job.fonctionid = fonctions_def.id)';
            $field     = 'fonction_fr';
            $unique    = 'uid';
            $realid    = 'fonctions_def.id';
            $beginwith = false;
            break;
          case 'groupexTxt':
            $db = "#groupex#.asso AS a INNER JOIN
                   #groupex#.membres AS m ON(a.id = m.asso_id
                                           AND (a.cat = 'GroupesX' OR a.cat = 'Institutions')
                                           AND a.pub = 'public')";
            $field='a.nom';
            $field2 = 'a.diminutif';
            if (strlen($q) > 2)
                $beginwith = false;
            $realid = 'a.id';
            $unique = 'm.uid';
            break;
          case 'nationaliteTxt':
            $db     = 'geoloc_countries INNER JOIN
                       auth_user_md5 ON (geoloc_countries.a2 = auth_user_md5.nationalite
                                         OR geoloc_countries.a2 = auth_user_md5.nationalite2
                                         OR geoloc_countries.a2 = auth_user_md5.nationalite3)';
            $field  = 'geoloc_countries.nationalityFR';
            $realid = 'geoloc_countries.iso_3166_1_a2';
            break;
          case 'description':
            $db     = 'profile_job';
            $field  = 'description';
            $unique = 'uid';
            break;
          case 'schoolTxt':
            $db = 'profile_education_enum INNER JOIN
                   profile_education ON (profile_education_enum.id = profile_education.eduid)';
            $field = 'profile_education_enum.name';
            $unique = 'uid';
            $realid = 'profile_education_enum.id';
            if (strlen($q) > 2)
                $beginwith = false;
            break;
          case 'secteurTxt':
            $db        = 'profile_job_sector_enum INNER JOIN
                          profile_job ON (profile_job.sectorid = profile_job_sector_enum.id)';
            $field     = 'profile_job_sector_enum.name';
            $realid    = 'profile_job_sector_enum.id';
            $unique    = 'uid';
            $beginwith = false;
            break;
          case 'subSubSector':
            $db        = 'profile_job_subsubsector_enum';
            $field     = 'name';
            $beginwith = false;
            $unique    = 'name';
            $distinct  = false;
            break;
          case 'sectionTxt':
            $db = '`sections` INNER JOIN
                   `auth_user_md5` ON(`auth_user_md5`.`section` = `sections`.`id`)';
            $field = '`sections`.`text`';
            $realid = '`sections`.`id`';
            $beginwith = false;
            break;
          default: exit();
        }

        function make_field_test($fields, $beginwith) {
            $tests = array();
            $tests[] = $fields . ' LIKE CONCAT({?}, \'%\')';
            if (!$beginwith) {
                $tests[] = $fields . ' LIKE CONCAT(\'% \', {?}, \'%\')';
                $tests[] = $fields . ' LIKE CONCAT(\'%-\', {?}, \'%\')';
            }
            return '(' . implode(' OR ', $tests) . ')';
        }
        $field_select = $field;
        $field_t = make_field_test($field, $beginwith);
        if ($field2) {
            $field2_t = make_field_test($field2, $beginwith);
            $field_select = 'IF(' . $field_t . ', ' . $field . ', ' . $field2. ')';
        }
        $list = XDB::iterator('SELECT  ' . $field_select . ' AS field'
                                       . ($distinct ? (', COUNT(DISTINCT ' . $unique . ') AS nb') : '')
                                       . ($realid ? (', ' . $realid . ' AS id') : '') . '
                                 FROM  ' . $db . '
                                WHERE  ' . $field_t .
                                        ($field2 ? (' OR ' . $field2_t) : '') . '
                             GROUP BY  ' . $field_select . '
                             ORDER BY  ' . ($distinct ? 'nb DESC' : $field_select) . '
                                LIMIT  11',
                               $qsearch, $qsearch, $qsearch, $qsearch, $qsearch, $qsearch, $qsearch, $qsearch,
                               $qsearch, $qsearch, $qsearch, $qsearch, $qsearch, $qsearch, $qsearch, $qsearch);

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
        XDB::query('REPLACE INTO  `search_autocomplete`
                          VALUES  ({?}, {?}, {?}, NOW())',
                    $type, $q, $res);
        echo $res;
        exit();
    }

    function handler_list(&$page, $type = null, $idVal = null)
    {
        // Give the list of all values possible of type and builds a select input for it
        $field = '`text`';
        $id = '`id`';
        $where = '';

        switch ($type) {
          case 'binet':
            $db = '`binets_def`';
            break;
          case 'networking_type':
            $db = '`profile_networking_enum`';
            $field = '`name`';
            $id = '`network_type`';
            break;
          case 'country':
            $db    = 'geoloc_countries';
            $field = 'countryFR';
            $id    = 'iso_3166_1_a2';
            $page->assign('onchange', 'changeCountry(this.value)');
            break;
          case 'fonction':
            $db = '`fonctions_def`';
            $field = '`fonction_fr`';
            break;
          case 'diploma':
            pl_content_headers("text/xml");
            $this->get_diplomas();
            $page->changeTpl('search/adv.grade.form.tpl', NO_SKIN);
            return;
          case 'groupex':
            $db = '#groupex#.asso';
            $where = " WHERE (cat = 'GroupesX' OR cat = 'Institutions') AND pub = 'public'";
            $field = 'nom';
            break;
          case 'nationalite':
            $db    = 'geoloc_countries INNER JOIN
                      auth_user_md5 ON (geoloc_countries.iso_3166_1_a2 = auth_user_md5.nationalite
                                        OR geoloc_countries.iso_3166_1_a2 = auth_user_md5.nationalite2
                                        OR geoloc_countries.iso_3166_1_a2 = auth_user_md5.nationalite3)';
            $field = 'nationalityFR';
            $id    = 'iso_3166_1_a2';
            break;
          case 'region':
            $db    = 'geoloc_administrativeareas';
            $field = 'name';
            $id    = 'id';
            if (isset($_REQUEST['country'])) {
                $where .= ' WHERE country = "' . $_REQUEST['country'] . '"';
            }
            break;
          case 'school':
            $db = 'profile_education_enum';
            $field = 'name';
            $id = 'id';
            $page->assign('onchange', 'changeSchool(this.value)');
            break;
          case 'section':
            $db = '`sections`';
            break;
          case 'secteur':
            $db    = 'profile_job_sector_enum INNER JOIN
                      profile_job ON (profile_job.sectorid = profile_job_sector_enum.id)';
            $field = 'profile_job_sector_enum.name';
            $id    = 'profile_job_sector_enum.id';
            break;
          default: exit();
        }
        if (isset($idVal)) {
            pl_content_headers("text/plain");
            $result = XDB::query('SELECT '.$field.' AS field FROM '.$db.' WHERE '.$id.' = {?} LIMIT 1',$idVal);
            echo $result->fetchOneCell();
            exit();
        }
        pl_content_headers("text/xml");
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('name', $type);
        $page->assign('list', XDB::iterator('SELECT  '.$field.' AS field,
                                                     '.$id.' AS id
                                               FROM  '.$db.$where.'
                                           GROUP BY  '.$field.'
                                           ORDER BY  '.$field));
        $page->assign('with_text_value', true);
        $page->assign('onchange', "document.forms.recherche.{$type}Txt.value = this.options[this.selectedIndex].text");
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
