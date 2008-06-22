<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
            'search'     => $this->make_hook('quick', AUTH_PUBLIC),
            'search/adv' => $this->make_hook('advanced', AUTH_COOKIE),
            'advanced_search.php' => $this->make_hook('redir_advanced', AUTH_PUBLIC),
            'search/autocomplete' => $this->make_hook('autocomplete', AUTH_COOKIE, 'user', NO_AUTH),
            'search/list' => $this->make_hook('list', AUTH_COOKIE, 'user', NO_AUTH),
        );
    }

    function handler_redir_advanced(&$page, $mode = null)
    {
        pl_redirect('search/adv');
        exit;
    }

    function on_subscribe($forlife, $uid, $promo, $pass)
    {
        require_once 'user.func.inc.php';
        user_reindex($uid);
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

        if (!is_null($school)) {
            $sql = 'SELECT type FROM applis_def WHERE id=' . $school;
        } else {
            $sql = 'DESCRIBE applis_def type';
        }

        $res = XDB::query($sql);
        $row = $res->fetchOneRow();
        if (!is_null($school)) {
            $types = $row[0];
        } else {
            $types = explode('(',$row[1]);
            $types = str_replace("'","",substr($types[1],0,-1));
        }
        Platal::page()->assign('choix_diplomas', explode(',',$types));
    }

    function handler_quick(&$page, $action = null, $subaction = null)
    {
        global $globals;

        $res = XDB::query("SELECT  MIN(`diminutif`), MAX(`diminutif`)
                             FROM  `groupex`.`asso`
                            WHERE  `cat` = 'Promotions'");
        list($min, $max) = $res->fetchOneRow();
        $page->assign('promo_min', $min);
        $page->assign('promo_max', $max);

        if (Env::has('quick') || $action == 'geoloc') {
            $quick = trim(Env::v('quick'));
            if (S::logged() && !Env::has('page')) {
                S::logger()->log('search', 'quick=' . $quick);
            }
            $list = 'profile|prf|fiche|fic|referent|ref|mentor';
            if (S::has_perms()) {
                $list .= '|admin|adm|ax';
            }
            if (preg_match('/^(' . $list . '):([-a-z]+(\.[-a-z]+(\.\d{2,4})?)?)$/', replace_accent($quick), $matches)) {
                $forlife = $matches[2];
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

                require_once 'user.func.inc.php';
                $login = get_user_forlife($forlife, '_silent_user_callback');
                if ($login) {
                    pl_redirect($base . $login);
                }
                $_REQUEST['quick'] = $forlife;
                $_GET['quick'] = $forlife;
            } elseif (strpos($quick, 'doc:') === 0) {
                $url = 'Docs/Recherche?';
                $url .= 'action=search&q=' . urlencode(substr($quick, 4));
                $url .= '&group=' . urlencode('-Equipe,-Main,-PmWiki,-Site,-Review');
                pl_redirect($url);
            }

            $page->assign('formulaire', 0);

            require_once 'userset.inc.php';
            $view = new SearchSet(true, $action == 'geoloc' && substr($subaction, -3) == 'swf');
            $view->addMod('minifiche', 'Minifiches', true, array('with_score' => true));
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

        require_once dirname(__FILE__) . '/search/search.inc.php';
        $page->changeTpl('search/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Annuaire');
    }

    function handler_advanced(&$page, $action = null, $subaction = null)
    {
        global $globals;
        require_once 'geoloc.inc.php';
        require_once dirname(__FILE__) . '/search/search.inc.php';
        $page->assign('advanced',1);
        $page->addJsLink('jquery.autocomplete.js');

        if (!Env::has('rechercher') && $action != 'geoloc') {
            $this->form_prepare();
        } else {
            $textFields = array(
                'country' => array('field' => 'a2', 'table' => 'geoloc_pays', 'text' => 'pays', 'exact' => false),
                'fonction' => array('field' => 'id', 'table' => 'fonctions_def', 'text' => 'fonction_fr', 'exact' => true),
                'secteur' => array('field' => 'id', 'table' => 'emploi_secteur', 'text' => 'label', 'exact' => false),
                'nationalite' => array('field' => 'a2', 'table' => 'geoloc_pays', 'text' => 'nat', 'exact' => 'false'),
                'binet' => array('field' => 'id', 'table' => 'binets_def', 'text' => 'text', 'exact' => false),
                'groupex' => array('field' => 'id', 'table' => 'groupex.asso',
                                   'text' => "(a.cat = 'GroupesX' OR a.cat = 'Institutions') AND pub = 'public' AND nom",
                                   'exact' => false),
                'section' => array('field' => 'id', 'table' => 'sections', 'text' => 'text', 'exact' => false),
                'school' => array('field' => 'id', 'table' => 'applis_def', 'text' => 'text', 'exact' => false),
                'city' => array('table' => 'geoloc_city', 'text' => 'name', 'exact' => false)
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
            $view->addMod('minifiche', 'Minifiches', true);
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
        header('Content-Type: text/plain; charset="UTF-8"');
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

        switch ($type) {
          case 'binetTxt':
            $db = '`binets_def` INNER JOIN
                   `binets_ins` ON(`binets_def`.`id` = `binets_ins`.`binet_id`)';
            $field='`binets_def`.`text`';
            if (strlen($q) > 2)
                $beginwith = false;
            $realid = '`binets_def`.`id`';
            break;
          case 'city':
            $db = '`geoloc_city` INNER JOIN
                   `adresses` ON(`geoloc_city`.`id` = `adresses`.`cityid`)';
            $unique='`uid`';
            $field='`geoloc_city`.`name`';
            break;
          case 'countryTxt':
            $db = '`geoloc_pays` INNER JOIN
                   `adresses` ON(`geoloc_pays`.`a2` = `adresses`.`country`)';
            $unique='`uid`';
            $field = '`geoloc_pays`.`pays`';
            $field2 = '`geoloc_pays`.`country`';
            $realid='`geoloc_pays`.`a2`';
            break;
          case 'entreprise':
            $db = '`entreprises`';
            $field = '`entreprise`';
            $unique='`uid`';
            break;
          case 'firstname':
            $field = '`prenom`';
            $beginwith = false;
            break;
          case 'fonctionTxt':
            $db = '`fonctions_def` INNER JOIN
                   `entreprises` ON(`entreprises`.`fonction` = `fonctions_def`.`id`)';
            $field = '`fonction_fr`';
            $unique = '`uid`';
            $realid = '`fonctions_def`.`id`';
            $beginwith = false;
            break;
          case 'groupexTxt':
            $db = "groupex.asso AS a INNER JOIN
                   groupex.membres AS m ON(a.id = m.asso_id
                                           AND (a.cat = 'GroupesX' OR a.cat = 'Institutions')
                                           AND a.pub = 'public')";
            $field='a.nom';
            $field2 = 'a.diminutif';
            if (strlen($q) > 2)
                $beginwith = false;
            $realid = 'a.id';
            $unique = 'm.uid';
            break;
          case 'name':
            $field = '`nom`';
            $field2 = '`nom_usage`';
            $beginwith = false;
            break;
          case 'nationaliteTxt':
            $db = '`geoloc_pays` INNER JOIN
                   `auth_user_md5` ON(`geoloc_pays`.`a2` = `auth_user_md5`.`nationalite`)';
            $field = 'IF(`geoloc_pays`.`nat`=\'\',
                                       `geoloc_pays`.`pays`,
                                       `geoloc_pays`.`nat`)';
            $realid = '`geoloc_pays`.`a2`';
            break;
          case 'nickname':
            $field = '`profile_nick`';
            $db = '`auth_user_quick`';
            $beginwith = false;
            break;
          case 'poste':
            $db = '`entreprises`';
            $field = '`poste`';
            $unique='`uid`';
            break;
          case 'schoolTxt':
            $db = '`applis_def` INNER JOIN
                   `applis_ins` ON(`applis_def`.`id` = `applis_ins`.`aid`)';
            $field='`applis_def`.`text`';
            $unique = '`uid`';
            $realid = '`applis_def`.`id`';
            if (strlen($q) > 2)
                $beginwith = false;
            break;
          case 'secteurTxt':
            $db = '`emploi_secteur` INNER JOIN
                   `entreprises` ON(`entreprises`.`secteur` = `emploi_secteur`.`id`)';
            $field = '`emploi_secteur`.`label`';
            $realid = '`emploi_secteur`.`id`';
            $unique = '`uid`';
            $beginwith = false;
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
        $list = XDB::iterator('SELECT  ' . $field_select . ' AS field,
                                       COUNT(DISTINCT ' . $unique . ') AS nb
                                       ' . ($realid ? (', ' . $realid . ' AS id') : '') . '
                                 FROM  ' . $db . '
                                WHERE  ' . $field_t .
                                        ($field2 ? (' OR ' . $field2_t) : '') . '
                             GROUP BY  ' . $field_select . '
                             ORDER BY  nb DESC
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
                $res .= $result['nb'];
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
          case 'country':
            $db = '`geoloc_pays`';
            $field = '`pays`';
            $id = '`a2`';
            $page->assign('onchange', 'changeCountry(this.value)');
            break;
          case 'fonction':
            $db = '`fonctions_def`';
            $field = '`fonction_fr`';
            break;
          case 'diploma':
            header('Content-Type: text/xml; charset="UTF-8"');
            $this->get_diplomas();
            $page->changeTpl('search/adv.grade.form.tpl', NO_SKIN);
            return;
          case 'groupex':
            $db = 'groupex.asso';
            $where = " WHERE (cat = 'GroupesX' OR cat = 'Institutions') AND pub = 'public'";
            $field = 'nom';
            break;
          case 'nationalite':
            $db = '`geoloc_pays` INNER JOIN
                   `auth_user_md5` ON (`geoloc_pays`.`a2` = `auth_user_md5`.`nationalite`)';
            $field = 'IF(`nat`=\'\', `pays`, `nat`)';
            $id = '`a2`';
            break;
          case 'region':
            $db = '`geoloc_region`';
            $field = '`name`';
            $id = '`region`';
            if (isset($_REQUEST['country'])) {
                $where .= ' WHERE `a2` = "'.$_REQUEST['country'].'"';
            }
            break;
          case 'school':
            $db = '`applis_def`';
            $page->assign('onchange', 'changeSchool(this.value)');
            break;
          case 'section':
            $db = '`sections`';
            break;
          case 'secteur':
            $db = '`emploi_secteur`';
            $field = '`label`';
            break;
          default: exit();
        }
        if (isset($idVal)) {
            header('Content-Type: text/plain; charset="UTF-8"');
            $result = XDB::query('SELECT '.$field.' AS field FROM '.$db.' WHERE '.$id.' = {?} LIMIT 1',$idVal);
            echo $result->fetchOneCell();
            exit();
        }
        header('Content-Type: text/xml; charset="UTF-8"');
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
