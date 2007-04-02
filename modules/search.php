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

    function handler_quick(&$page, $action = null, $subaction = null)
    {
        global $globals;

        if (Env::has('quick') || $action == 'geoloc') {
            $page->assign('formulaire', 0);

            require_once 'userset.inc.php';
            $view = new SearchSet(true, $action == 'geoloc' && substr($subaction, -3) == 'swf');
            $view->addMod('minifiche', 'Minifiches', true, array('with_score' => true));
            if (S::logged() && !Env::i('nonins')) {
                $view->addMod('trombi', 'Trombinoscope', false, array('with_promo' => true, 'with_score' => true));
                $view->addMod('geoloc', 'Planisphère');
            }
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

        require_once dirname(__FILE__) . '/search/search.inc.php';
        $page->changeTpl('search/index.tpl');            
        $page->assign('xorg_title','Polytechnique.org - Annuaire');
        $page->assign('baseurl', $globals->baseurl);
        $page->register_modifier('display_lines', 'display_lines');
    }

    function handler_advanced(&$page, $action = null, $subaction = null)
    {
        global $globals;
        if (!Env::has('rechercher') && $action != 'geoloc') {
            $this->form_prepare();
        } else {
            require_once 'userset.inc.php';
            $view = new SearchSet(false, $action == 'geoloc' && substr($subaction, -3) == 'swf');
            $view->addMod('minifiche', 'Minifiches', true);
            $view->addMod('trombi', 'Trombinoscope', false, array('with_promo' => true));
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

        require_once 'geoloc.inc.php';
        require_once dirname(__FILE__) . '/search/search.inc.php';
        $page->changeTpl('search/index.tpl', $action == 'mini' ? SIMPLE : SKINNED);
        $page->addJsLink('ajax.js');
        $page->assign('advanced',1);
        $page->assign('public_directory',0);
        $page->register_modifier('display_lines', 'display_lines');
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
        $q = preg_replace('/\*+$/','',$_REQUEST['q']);
        if (!$q) exit();

				// try to look in cached results        
        $cache = XDB::query('SELECT result FROM search_autocomplete WHERE name = {?} AND query = {?} AND generated > NOW() - INTERVAL 1 DAY',
        		$type, $q);
        if ($res = $cache->fetchOneCell()) {
        		echo $res;
        		die();
        }
        
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
        $res = "";
        while ($result = $list->next()) {
            $nbResults++;
            if ($nbResults == 11) {
                $res .= '...|1'."\n";
            } else {
                $res .= $result['field'].'|'.$result['nb'].(isset($result['id'])?('|'.$result['id']):'')."\n";
            }
        }
        XDB::query('REPLACE INTO search_autocomplete VALUES ({?}, {?}, {?}, NOW())',
        		$type, $q, $res);
        echo $res;
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
