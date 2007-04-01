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

class SearchModule extends PLModule
{
    function handlers()
    {
        return array(
            'search'     => $this->make_hook('quick', AUTH_PUBLIC),
            'search/adv' => $this->make_hook('advanced', AUTH_COOKIE),
            'search/ajax/region'  => $this->make_hook('region', AUTH_COOKIE, 'user', NO_AUTH),
            'search/ajax/grade'   => $this->make_hook('grade',  AUTH_COOKIE, 'user', NO_AUTH),
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

    function get_quick($offset, $limit, $order)
    {
        global $globals;
        if (!S::logged()) {
            Env::kill('with_soundex');
        }
        $qSearch = new QuickSearch('quick');
        $fields  = new SFieldGroup(true, array($qSearch));

        if ($qSearch->isempty()) {
            new ThrowError('Recherche trop générale.');
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS
            UPPER(IF(u.nom!="",u.nom,u.nom_ini)) AS nom,
            IF(u.prenom!="",u.prenom,u.prenom_ini) AS prenom,
            '.$globals->search->result_fields.'
            c.uid AS contact, w.ni_id AS watch,
            '.$qSearch->get_score_statement().'
                FROM  auth_user_md5  AS u
                '.$fields->get_select_statement().'
                LEFT JOIN  auth_user_quick AS q  ON (u.user_id = q.user_id)
                LEFT JOIN  aliases         AS a  ON (u.user_id = a.id AND a.type="a_vie")
                LEFT JOIN  contacts        AS c  ON (c.uid='.S::i('uid', -1).'
                                                     AND c.contact=u.user_id)
                LEFT JOIN  watch_nonins    AS w  ON (w.ni_id=u.user_id
                                                     AND w.uid='.S::i('uid', -1).')
                '.$globals->search->result_where_statement.'
                    WHERE  '.$fields->get_where_statement()
                    .(S::logged() && Env::has('nonins') ? ' AND u.perms="pending" AND u.deces=0' : '')
                .'
                 GROUP BY  u.user_id
                 ORDER BY  '.($order?($order.', '):'')
                .implode(',',array_filter(array($fields->get_order_statement(),
                                                'u.promo DESC, NomSortKey, prenom'))).'
                    LIMIT  '.$offset * $globals->search->per_page.','
                .$globals->search->per_page;
        $list    = XDB::iterator($sql);
        $res     = XDB::query("SELECT  FOUND_ROWS()");
        $nb_tot  = $res->fetchOneCell();
        return array($list, $nb_tot);
    }

    function form_prepare()
    {
        global $page;

        $page->assign('formulaire',1);
        $page->assign('choix_schools',
                      XDB::iterator('SELECT id,text FROM applis_def ORDER BY text'));
        $this->get_diplomas();
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
        global $page;
        $page->assign('choix_diplomas', explode(',',$types));
    }

    function get_advanced($offset, $limit, $order)
    {
        $fields = new SFieldGroup(true, advancedSearchFromInput());
        if ($fields->too_large()) {
            $this->form_prepare();
            new ThrowError('Recherche trop générale.');
        }
        global $globals, $page;

  			$page->assign('search_vars', $fields->get_url());

        $where = $fields->get_where_statement();
        if ($where) {
            $where = "WHERE  $where";
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT
                           u.nom, u.prenom,
                           '.$globals->search->result_fields.'
                           c.uid AS contact,
                           w.ni_id AS watch
                     FROM  auth_user_md5   AS u
               LEFT JOIN  auth_user_quick AS q USING(user_id)
                '.$fields->get_select_statement().'
                '.(Env::has('only_referent') ? ' INNER JOIN mentor AS m ON (m.uid = u.user_id)' : '').'
                LEFT JOIN  aliases        AS a ON (u.user_id = a.id AND a.type="a_vie")
                LEFT JOIN  contacts       AS c ON (c.uid='.S::v('uid').'
                                                   AND c.contact=u.user_id)
                LEFT JOIN  watch_nonins   AS w ON (w.ni_id=u.user_id
                                                   AND w.uid='.S::v('uid').')
                '.$globals->search->result_where_statement."
                    $where
                 GROUP BY  u.user_id
                 ORDER BY  ".($order?($order.', '):'')
                .implode(',',array_filter(array($fields->get_order_statement(),
                                                'promo DESC, NomSortKey, prenom'))).'
                    LIMIT  '.($offset * $limit).','.$limit;
        $liste   = XDB::iterator($sql);
        $res     = XDB::query("SELECT  FOUND_ROWS()");
        $nb_tot  = $res->fetchOneCell();
        return Array($liste, $nb_tot);
    }

    function handler_quick(&$page, $action = null, $subaction = null)
    {
        global $globals;

        if (Env::has('quick') || $action == 'geoloc') {
            $page->assign('formulaire', 0);

            require_once 'userset.inc.php';
            $view = new SearchSet(true, $action == 'geoloc' && substr($subaction, -3) == 'swf');
            $view->addMod('minifiche', 'Minifiches', true);
            $view->addMod('trombi', 'Trombinoscope');
            $view->addMod('geoloc', 'Planishpère');
            $view->apply('search', $page, $action, $subaction);

            $nb_tot = $view->count();
            if ($subaction) {
                return;
            }
            if (!S::logged() && $nb_tot > $globals->search->public_max) {
                new ThrowError('Votre recherche a généré trop de résultats pour un affichage public.');
            } elseif ($nb_tot > $globals->search->private_max) {
                new ThrowError('Recherche trop générale');
            } elseif (empty($nb_tot)) {
                new ThrowError('il n\'existe personne correspondant à ces critères dans la base !');
            }
        } else {
            $res = XDB::query("SELECT  MIN(diminutif), MAX(diminutif)
                                 FROM  groupex.asso
                                WHERE  cat = 'Promotions'");
            list($min, $max) = $res->fetchOneRow();
            $page->assign('promo_min', $min);
            $page->assign('promo_max', $max); 
            $page->assign('formulaire',1);
            $page->addJsLink('ajax.js');
        }

        $page->changeTpl('search/index.tpl');            
        $page->assign('xorg_title','Polytechnique.org - Annuaire');
        $page->assign('baseurl', $globals->baseurl);
    }

    function handler_advanced(&$page, $mode = null)
    {
        global $globals;
        if (!Env::has('rechercher')) {
            $this->form_prepare();
        } else {
            require_once 'userset.inc.php';
            $view = new SearchSet(false, $action == 'geoloc' && substr($subaction, -3) == 'swf');
            $view->addMod('minifiche', 'Minifiches', true);
            $view->addMod('trombi', 'Trombinoscope');
            $view->addMod('geoloc', 'Planishpère');
            $view->apply('search', $page, $action, $subaction);
            
            if ($subaction) {
                return;
            }
            $nb_tot = $view->count();
            if ($nb_tot > $globals->search->private_max) {
                $this->form_prepare();
                new ThrowError('Recherche trop générale');
            }
        }

        $page->changeTpl('search/index.tpl', $mode == 'mini' ? SIMPLE : SKINNED);
        $page->addJsLink('ajax.js');
        $page->assign('advanced',1);
        $page->assign('public_directory',0);
    }

    function handler_region(&$page, $country = null)
    {
        header('Content-Type: text/html; charset="UTF-8"');
        require_once("geoloc.inc.php");
        $page->ChangeTpl('search/adv.region.form.tpl', NO_SKIN);
        $page->assign('region', "");
        $page->assign('country', $country);
    }

    function handler_grade(&$page, $school = null)
    {
        header('Content-Type: text/html; charset="UTF-8"');
        $page->ChangeTpl('search/adv.grade.form.tpl', NO_SKIN);
        $page->assign('grade', '');
        $this->get_diplomas($school);
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
        $q = $_REQUEST['q'];
        if (!$q) exit();
        // default search
        $unique = 'user_id';
        $db = 'auth_user_md5';
        $realid = false;
        $contains = false;
        
        switch ($type) {
        case 'binetTxt':
						$db = 'binets_def INNER JOIN binets_ins ON(binets_def.id = binets_ins.binet_id)';
						$field='binets_def.text';
						if (strlen($q) > 2)
								$contains = true;
						$realid = 'binets_def.id';
						break;
        case 'city': $db = 'geoloc_city INNER JOIN adresses ON(geoloc_city.id = adresses.cityid)'; $unique='uid'; $field='geoloc_city.name'; break;
        case 'entreprise': $db = 'entreprises'; $field = 'entreprise'; $unique='uid'; break;
        case 'firstname': $field = 'prenom'; break;
        case 'fonctionTxt':
        		$db = 'fonctions_def INNER JOIN entreprises ON(entreprises.fonction = fonctions_def.id)';
        		$field = 'fonction_fr';
        		$unique = 'uid';
        		$realid = 'fonctions_def.id';
        		break;
        case 'groupexTxt':
						$db = 'groupesx_def INNER JOIN groupesx_ins ON(groupesx_def.id = groupesx_ins.gid)';
						$field='groupesx_def.text';
						if (strlen($q) > 2)
								$contains = true;
						$realid = 'groupesx_def.id';
						$unique = 'guid';
						break;
        case 'name': $field = 'nom'; break;
    		case 'nationaliteTxt':
    				$db = 'geoloc_pays INNER JOIN auth_user_md5 ON(geoloc_pays.a2 = auth_user_md5.nationalite)';
    				$field = 'IF(geoloc_pays.nat=\'\', geoloc_pays.pays, geoloc_pays.nat)';
    				$realid = 'geoloc_pays.a2';
    				break;
        case 'nickname': $field = 'profile_nick'; $db = 'auth_user_quick'; break;
        case 'poste': $db = 'entreprises'; $field = 'poste'; $unique='uid'; break;
    		case 'secteurTxt':
    				$db = 'emploi_secteur INNER JOIN entreprises ON(entreprises.secteur = emploi_secteur.id)';
    				$field = 'emploi_secteur.label';
    				$realid = 'emploi_secteur.id';
    				$unique = 'uid';
    				break;
    		case 'sectionTxt':
    				$db = 'sections INNER JOIN auth_user_md5 ON(auth_user_md5.section = sections.id)';
    				$field = 'sections.text';
    				$realid = 'sections.id';
    				break;
        default: exit();
        }

        $list = XDB::iterator('
						SELECT
								'.$field.' AS field,
								COUNT(DISTINCT '.$unique.') AS nb
								'.($realid?(', '.$realid.' AS id'):'').'
						FROM '.$db.'
						WHERE '.$field.' LIKE {?}
						GROUP BY '.$field.'
						ORDER BY nb DESC
						LIMIT 11',
						($contains?'%':'').str_replace('*','%',$q).'%');
        $nbResults = 0;
        while ($result = $list->next()) {
            $nbResults++;
            if ($nbResults == 11) {
                echo '...|1'."\n";
            } else {
                echo $result['field'].'|'.$result['nb'].(isset($result['id'])?('|'.$result['id']):'')."\n";
            }
        }

        exit();
    }
    
    function handler_list(&$page, $type = null, $idVal = null)
    {
    		// Give the list of all values possible of type and builds a select input for it
				$field = 'text';
				$id = 'id';
    		switch ($type) {
    		case 'binet':
    				$db = 'binets_def';
    				break;
    		case 'fonction':
    				$db = 'fonctions_def';
    				$field = 'fonction_fr';
    				break;
    		case 'groupex':
    				$db = 'groupesx_def';
    				break;
    		case 'nationalite':
    				$db = 'geoloc_pays';
    				$field = 'IF(nat=\'\', pays, nat)';
    				$id = 'a2';
    				break;
    		case 'section':
    				$db = 'sections';
    				break;
    		case 'secteur':
    				$db = 'emploi_secteur';
    				$field = 'label';
    				break;
    		default: exit();
    		}
    		if (isset($idVal)) {
       			header('Content-Type: text/plain; charset="UTF-8"');
    				$result = XDB::query('SELECT '.$field.' AS field FROM '.$db.' WHERE '.$id.' = {?} LIMIT 1',$idVal);
    				echo $result->fetchOneCell();
    		} else {
		        header('Content-Type: text/xml; charset="UTF-8"');
		    		$list = XDB::iterator('
		    				SELECT
		    						'.$field.' AS field,
		    						'.$id.' AS id
		    				FROM '.$db.'
		    				ORDER BY '.$field);
		    		echo '<select name="'.$type.'">';
		    		while ($result = $list->next()) {
		    				echo '<option value="'.$result['id'].'">'.htmlspecialchars($result['field']).'</option>';
		    		}
		    		echo '</select>';
				}
				    		
    		exit();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
