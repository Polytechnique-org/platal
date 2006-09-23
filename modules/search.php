<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
            'search.php' => $this->make_hook('quick', AUTH_PUBLIC),
            'search/adv' => $this->make_hook('advanced', AUTH_COOKIE),
            'advanced_search.php' => $this->make_hook('redir_advanced', AUTH_PUBLIC),
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
        $page->assign('choix_nats',
                      XDB::iterator('SELECT a2 AS id,IF(nat=\'\',pays,nat) AS text
                                                 FROM geoloc_pays ORDER BY text'));
        $page->assign('choix_postes',
                      XDB::iterator('SELECT id,fonction_fr FROM fonctions_def
                                             ORDER BY fonction_fr'));
        $page->assign('choix_binets',
                      XDB::iterator('SELECT id,text FROM binets_def ORDER BY text'));
        $page->assign('choix_groupesx',
                      XDB::iterator('SELECT id,text FROM groupesx_def ORDER BY text'));
        $page->assign('choix_sections',
                      XDB::iterator('SELECT id,text FROM sections ORDER BY text'));
        $page->assign('choix_schools',
                      XDB::iterator('SELECT id,text FROM applis_def ORDER BY text'));
        $page->assign('choix_secteurs',
                      XDB::iterator('SELECT id,label FROM emploi_secteur ORDER BY label'));

        if (Env::has('school')) {
            $sql = 'SELECT type FROM applis_def WHERE id='.Env::i('school');
        } else {
            $sql = 'DESCRIBE applis_def type';
        }

        $res = XDB::query($sql);
        $row = $res->fetchOneRow();
        if (Env::has('school')) {
            $types = $row[0];
        } else {
            $types = explode('(',$row[1]);
            $types = str_replace("'","",substr($types[1],0,-1));
        }
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

    function handler_quick(&$page)
    {
        global $globals;

        require_once 'search.inc.php';

        $page->changeTpl('search/index.tpl');

        $page->assign('xorg_title','Polytechnique.org - Annuaire');
        require_once("applis.func.inc.php");
        require_once("geoloc.inc.php");

        $page->assign('baseurl', $globals->baseurl);

        if (Env::has('quick')) {
            $page->assign('formulaire', 0);

            $search = new XOrgSearch(array($this, 'get_quick'));
            $search->setNbLines($globals->search->per_page);
            $search->addOrder('score', 'score', false, 'pertinence', AUTH_PUBLIC, true);

            $nb_tot = $search->show();

            if (!S::logged() && $nb_tot > $globals->search->public_max) {
                new ThrowError('Votre recherche a généré trop de résultats pour un affichage public.');
            } elseif ($nb_tot > $globals->search->private_max) {
                new ThrowError('Recherche trop générale');
            } elseif (empty($nb_tot)) {
                new ThrowError('il n\'existe personne correspondant à ces critères dans la base !');
            }
        } else {
            $page->assign('formulaire',1);
        }

        $page->register_modifier('display_lines', 'display_lines');
    }

    function handler_advanced(&$page, $mode = null)
    {
        global $globals;

        require_once 'search.inc.php' ;
        require_once 'applis.func.inc.php';
        require_once 'geoloc.inc.php';


        $page->changeTpl('search/index.tpl', $mode == 'mini' ? SIMPLE : SKINNED);

        $page->assign('advanced',1);
        $page->assign('public_directory',0);

        if (!Env::has('rechercher')) {
            $this->form_prepare();
        } else {

            $search = new XOrgSearch(array($this, 'get_advanced'));
            $search->setNbLines($globals->search->per_page);

            $page->assign('url_search_form', $search->make_url(Array('rechercher'=>0)));
            if (Env::has('with_soundex')) {
                $page->assign('with_soundex', $search->make_url(Array())."&with_soundex=1");
            }

            $nb_tot = $search->show();

            if ($nb_tot > $globals->search->private_max) {
                $this->form_prepare();
                new ThrowError('Recherche trop générale');
            }

        }

        $page->register_modifier('display_lines', 'display_lines');
    }
}

?>
