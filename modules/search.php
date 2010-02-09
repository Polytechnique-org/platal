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
                // TODO: Reactivate when the new map is completed.
                // $view->addMod('geoloc', 'Planisphère', false, array('with_annu' => 'search/adv'));
            }
            $view->apply('search', $page, $action, $subaction);

            $nb_tot = $view->count();
            $page->assign('search_results_nb', $nb_tot);
            if ($subaction) {
                return;
            }
            if (!S::logged() && $nb_tot > $globals->search->public_max) {
                $page->trigError('Votre recherche a généré trop de résultats pour un affichage public.');
            } elseif ($nb_tot > $globals->search->private_max) {
                $page->trigError('Recherche trop générale. Une <a href="search/adv">recherche avancée</a> permet de préciser la recherche.');
            } elseif (empty($nb_tot)) {
                $page->trigError('Il n\'existe personne correspondant à ces critères dans la base !');
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
        require_once 'geocoding.inc.php';
        $this->load('search.inc.php');
        $page->assign('advanced',1);
        $page->addJsLink('jquery.autocomplete.js');

        if (!Env::has('rechercher') && $action != 'geoloc') {
            $this->form_prepare();
        } else {
            if (!Env::has('page')) {
                S::logger()->log('search', 'adv=' . var_export($_GET, true));
            }

            require_once 'userset.inc.php';
            $view = new SearchSet(false, $action == 'geoloc' && substr($subaction, -3) == 'swf');
            $view->addMod('minifiche', 'Mini-fiches', true);
            $view->addMod('trombi', 'Trombinoscope', false, array('with_promo' => true));
            // TODO: Reactivate when the new map is completed.
            // $view->addMod('geoloc', 'Planisphère', false, array('with_annu' => 'search/adv'));
            $view->apply('search/adv', $page, $action, $subaction);

            if ($subaction) {
                return;
            }
            $nb_tot = $view->count();
            if ($nb_tot > $globals->search->private_max) {
                $this->form_prepare();
                $page->trigError('Recherche trop générale.');
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

        require_once 'directory.enums.inc.php';
        $enums = array(
            'binetTxt'           => DirEnum::BINETS,
            'groupexTxt'         => DirEnum::GROUPESX,
            'sectionTxt'         => DirEnum::SECTIONS,
            'networking_typeTxt' => DirEnum::NETWORKS,
            'city'               => DirEnum::LOCALITIES,
            'countryTxt'         => DirEnum::COUNTRIES,
            'entreprise'         => DirEnum::COMPANIES,
            'secteurTxt'         => DirEnum::SECTORS,
            'description'        => DirEnum::JOBDESCRIPTION,
            'nationaliteTxt'     => DirEnum::NATIONALITIES,
            'schoolTxt'          => DirEnum::EDUSCHOOLS,
        );
        if (!array_key_exists($enums, $type)) {
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
        XDB::query('REPLACE INTO  search_autocomplete
                          VALUES  ({?}, {?}, {?}, NOW())',
                    $type, $q, $res);
        echo $res;
        exit();
    }

    function handler_list(&$page, $type = null, $idVal = null)
    {
        // Give the list of all values possible of type and builds a select input for it
        $ids = null;
        require_once 'directory.enums.inc.php';

        switch ($type) {
        case 'binet':
            $ids = DirEnum::getOptions(DirEnum::BINETS);
            break;
          case 'networking_type':
            $ids = DirEnum::getOptions(DirEnum::NETWORKS);
            break;
          case 'country':
            $ids = DirEnum::getOptions(DirEnum::COUNTRIES);
            $page->assign('onchange', 'changeCountry(this.value)');
            break;
          case 'diploma':
            if (Env::has('school') && Env::i('school') != 0) {
              $ids = DirEnum::getOptions(DirEnum::EDUDEGREES, Env::i('school'));
            } else {
              $ids = DirEnum::getOptions(DirEnum::EDUDEGREES);
            }
            break;
          case 'groupex':
            $ids = DirEnum::getOptions(DirEnum::GROUPESX);
            break;
          case 'nationalite':
            $ids = DirEnum::getOptions(DirEnum::NATIONALITIES);
            break;
        case 'region':
            if ($isset($_REQUEST['country'])) {
                $ids = DirEnum::getOptions(DirEnum::ADMINAREAS, $_REQUEST['country']);
            } else {
                $ids = DirEnum::getOptions(DirEnum::ADMINAREAS);
            }
            break;
          case 'school':
            $ids = DirEnum::getOptions(DirEnum::EDUSCHOOLS);
            $page->assign('onchange', 'changeSchool(this.value)');
            break;
          case 'section':
            $ids = DirEnum::getOptions(DirEnum::SECTIONS);
            break;
          case 'secteur':
            $ids = DirEnum::getOptions(DirEnum::SECTORS);
            break;
          default: exit();
        }
        if (isset($idVal)) {
            pl_content_headers("text/plain");
            echo $ids[$idVal];
            exit();
        }
        pl_content_headers("text/xml");
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('name', $type);
        $page->assign('list', $ids);
        $page->assign('with_text_value', true);
        $page->assign('onchange', "document.forms.recherche.{$type}Txt.value = this.options[this.selectedIndex].text");
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
