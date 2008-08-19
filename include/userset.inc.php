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

require_once('user.func.inc.php');

global $globals;

@$globals->search->result_where_statement = '
    LEFT JOIN  profile_education       AS edu ON (u.user_id = edu.uid)
    LEFT JOIN  profile_education_enum  AS ede ON (ede.id = edu.eduid)
    LEFT JOIN  entreprises             AS e   ON (e.entrid = 0 AND e.uid = u.user_id)
    LEFT JOIN  emploi_secteur          AS es  ON (e.secteur = es.id)
    LEFT JOIN  fonctions_def           AS ef  ON (e.fonction = ef.id)
    LEFT JOIN  geoloc_pays             AS n1  ON (u.nationalite = n1.a2)
    LEFT JOIN  geoloc_pays             AS n2  ON (u.nationalite2 = n2.a2)
    LEFT JOIN  geoloc_pays             AS n3  ON (u.nationalite2 = n3.a2)
    LEFT JOIN  adresses                AS adr ON (u.user_id = adr.uid AND FIND_IN_SET(\'active\',adr.statut))
    LEFT JOIN  geoloc_pays             AS gp  ON (adr.country = gp.a2)
    LEFT JOIN  geoloc_region           AS gr  ON (adr.country = gr.a2 AND adr.region = gr.region)
    LEFT JOIN  emails                  AS em  ON (em.uid = u.user_id AND em.flags = \'active\')';

class UserSet extends PlSet
{
    public function __construct($joins = '', $where = '')
    {
        global $globals;
        parent::__construct('auth_user_md5 AS u',
                            (!empty($GLOBALS['IS_XNET_SITE']) ?
                                'INNER JOIN groupex.membres AS gxm ON (u.user_id = gxm.uid
                                                                       AND gxm.asso_id = ' . $globals->asso('id') . ') ' : '')
                           . 'LEFT JOIN auth_user_quick AS q USING (user_id)
                              LEFT JOIN aliases         AS a ON (a.id = u.user_id AND a.type = \'a_vie\')
                              ' . $joins,
                            $where,
                            'u.user_id');
    }
}

class SearchSet extends UserSet
{
    public  $advanced = false;
    private $score = null;
    private $order = null;
    private $quick = false;

    public function __construct($quick = false, $no_search = false, $join = '', $where = '')
    {
        require_once dirname(__FILE__).'/../modules/search/search.inc.php';

        if ($no_search) {
            return;
        }

        $this->quick = $quick;
        if ($quick) {
            $this->getQuick($join, $where);
        } else {
            $this->getAdvanced($join, $where);
        }
    }

    private function getQuick($join, $where)
    {
        require_once dirname(__FILE__).'/../modules/search/search.inc.php';
        global $globals;
        if (!S::logged()) {
            Env::kill('with_soundex');
        }
        $qSearch = new QuickSearch('quick');
        $fields  = new SFieldGroup(true, array($qSearch));
        if ($qSearch->isEmpty()) {
            new ThrowError('Aucun critère de recherche n\'est spécifié.');
        }
        $this->score = $qSearch->get_score_statement();
        $pwhere = $fields->get_where_statement();
        if (trim($pwhere)) {
            if (trim($where)) {
                $where .= ' AND ';
            }
            $where .= $pwhere;
        }
        if (S::logged() && Env::has('nonins')) {
            if (trim($where)) {
                $where .= ' AND ';
            }
            $where .= 'u.perms="pending" AND u.deces=0';
        }
        parent::__construct($join . ' ' . $fields->get_select_statement(), $where);

        $this->order = implode(',',array_filter(array($fields->get_order_statement(),
                                                      'u.promo DESC, NomSortKey, prenom')));
    }

    private function getAdvanced($join, $where)
    {
        global $globals;
        $this->advanced = true;
        $fields = new SFieldGroup(true, advancedSearchFromInput());
        if ($fields->too_large()) {
            new ThrowError('Recherche trop générale.');
        }
        parent::__construct(@$join . ' ' . $fields->get_select_statement(),
                            @$where . ' ' . $fields->get_where_statement());
        $this->order = implode(',',array_filter(array($fields->get_order_statement(),
                                                      'promo DESC, NomSortKey, prenom')));
    }

    public function &get($fields, $joins, $where, $groupby, $order, $limitcount = null, $limitfrom = null)
    {
        if ($this->score) {
            $fields .= ', ' . $this->score;
        }
        return parent::get($fields, $joins, $where, $groupby, $order, $limitcount, $limitfrom);
    }
}

class ArraySet extends UserSet
{
    public function __construct(array $users)
    {
        $where = $this->getUids($users);
        if ($where) {
            $where = "a.alias IN ($where)";
        } else {
            $where = " 0 ";
        }
        parent::__construct('', $where);
    }

    private function getUids(array $users)
    {
        $users = get_users_forlife_list($users, true, '_silent_user_callback');
        if (is_null($users)) {
            return '';
        }
        return '\'' . implode('\', \'', $users) . '\'';
    }
}

class MinificheView extends MultipageView
{
    public function __construct(PlSet &$set, $data, array $params)
    {
        require_once 'applis.func.inc.php';
        global $globals;
        $this->entriesPerPage = $globals->search->per_page;
        if (@$params['with_score']) {
            $this->addSortKey('score', array('-score', '-date', '-promo', 'name_sort'), 'pertinence');
        }
        $this->addSortKey('name', array('name_sort'), 'nom');
        $this->addSortKey('promo', array('-promo', 'name_sort'), 'promotion');
        $this->addSortKey('date_mod', array('-date', '-promo', 'name_sort'), 'dernière modification');
        parent::__construct($set, $data, $params);
    }

    public function fields()
    {
        return "u.user_id AS id,
                u.*, a.alias AS forlife,
                u.perms != 'pending' AS inscrit,
                u.perms != 'pending' AS wasinscrit,
                u.deces != 0 AS dcd, u.deces, u.matricule_ax,
                FIND_IN_SET('femme', u.flags) AS sexe,
                e.entreprise, es.label AS secteur, ef.fonction_fr AS fonction,
                IF(n1.nat='',n1.pays,n1.nat) AS nat1, n1.a2 AS iso3166_1,
                IF(n2.nat='',n2.pays,n2.nat) AS nat2, n2.a2 AS iso3166_2,
                IF(n3.nat='',n3.pays,n3.nat) AS nat3, n3.a2 AS iso3166_3,
                ede0.name AS eduname0, ede0.url AS eduurl0, edd0.degree AS edudegree0, edu0.grad_year AS edugrad_year0, f0.field AS edufield0,
                ede1.name AS eduname1, ede1.url AS eduurl1, edd1.degree AS edudegree1, edu1.grad_year AS edugrad_year1, f1.field AS edufield1,
                ede2.name AS eduname2, ede2.url AS eduurl2, edd2.degree AS edudegree2, edu2.grad_year AS edugrad_year2, f2.field AS edufield2,
                ede3.name AS eduname3, ede3.url AS eduurl3, edd3.degree AS edudegree3, edu3.grad_year AS edugrad_year3, f3.field AS edufield3,
                adr.city, gp.a2, gp.pays AS countrytxt, gr.name AS region,
                (COUNT(em.email) > 0 OR FIND_IN_SET('googleapps', u.mail_storage) > 0) AS actif,
                nd.display AS name_display, nd.tooltip AS name_tooltip, nd.sort AS name_sort" .
                (S::logged() ? ", c.contact AS contact" : '');
    }

    public function joins()
    {
        return  "LEFT JOIN  entreprises                   AS e    ON (e.entrid = 0 AND e.uid = u.user_id".(S::logged() ? "" : " AND e.pub = 'public'").")
                 LEFT JOIN  emploi_secteur                AS es   ON (e.secteur = es.id)
                 LEFT JOIN  fonctions_def                 AS ef   ON (e.fonction = ef.id)
                 LEFT JOIN  geoloc_pays                   AS n1   ON (u.nationalite = n1.a2)
                 LEFT JOIN  geoloc_pays                   AS n2   ON (u.nationalite2 = n2.a2)
                 LEFT JOIN  geoloc_pays                   AS n3   ON (u.nationalite3 = n3.a2)
                 LEFT JOIN  profile_education             AS edu0 ON (u.user_id = edu0.uid AND edu0.id = 0)
                 LEFT JOIN  profile_education_enum        AS ede0 ON (ede0.id = edu0.eduid)
                 LEFT JOIN  profile_education_degree_enum AS edd0 ON (edd0.id = edu0.degreeid)
                 LEFT JOIN  profile_education_field_enum  AS f0   ON (f0.id = edu0.fieldid)
                 LEFT JOIN  profile_education             AS edu1 ON (u.user_id = edu1.uid AND edu1.id = 1)
                 LEFT JOIN  profile_education_enum        AS ede1 ON (ede1.id = edu1.eduid)
                 LEFT JOIN  profile_education_degree_enum AS edd1 ON (edd1.id = edu1.degreeid)
                 LEFT JOIN  profile_education_field_enum  AS f1   ON (f1.id = edu1.fieldid)
                 LEFT JOIN  profile_education             AS edu2 ON (u.user_id = edu2.uid AND edu2.id = 2)
                 LEFT JOIN  profile_education_enum        AS ede2 ON (ede2.id = edu2.eduid)
                 LEFT JOIN  profile_education_degree_enum AS edd2 ON (edd2.id = edu2.degreeid)
                 LEFT JOIN  profile_education_field_enum  AS f2   ON (f2.id = edu2.fieldid)
                 LEFT JOIN  profile_education             AS edu3 ON (u.user_id = edu3.uid AND edu3.id = 3)
                 LEFT JOIN  profile_education_enum        AS ede3 ON (ede3.id = edu3.eduid)
                 LEFT JOIN  profile_education_degree_enum AS edd3 ON (edd3.id = edu3.degreeid)
                 LEFT JOIN  profile_education_field_enum  AS f3   ON (f3.id = edu3.fieldid)
                 LEFT JOIN  adresses                      AS adr  ON (u.user_id = adr.uid
                                                                      AND FIND_IN_SET('active', adr.statut)".(S::logged() ? "" : "
                                                                      AND adr.pub = 'public'").")
                 LEFT JOIN  geoloc_pays                   AS gp   ON (adr.country = gp.a2)
                 LEFT JOIN  geoloc_region                 AS gr   ON (adr.country = gr.a2 AND adr.region = gr.region)
                 LEFT JOIN  emails                        AS em   ON (em.uid = u.user_id AND em.flags = 'active')
                INNER JOIN  profile_names_display         AS nd   ON (nd.user_id = u.user_id)" .
                (S::logged() ?
                "LEFT JOIN  contacts                      AS c   ON (c.contact = u.user_id AND c.uid = " . S::v('uid') . ")"
                 : "");
    }

    public function bounds()
    {
        $order = Env::v('order', $this->defaultkey);
        $show_bounds = 0;
        if (($order == "name") || ($order == "-name")) {
            $this->bound_field = "nom";
            $show_bounds = 1;
        } elseif (($order == "promo") || ($order == "-promo")) {
            $this->bound_field = "promo";
            $show_bounds = -1;
        }
        if ($order{0} == '-') {
            $show_bounds = -$show_bounds;
        }
        return $show_bounds;
    }

    public function templateName()
    {
        return 'include/plview.minifiche.tpl';
    }
}

class MentorView extends MultipageView
{
    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->entriesPerPage = 10;
        $this->addSortKey('rand', array('RAND(' . S::i('uid') . ')'), 'aléatoirement');
        $this->addSortKey('name', array('name_sort'), 'nom');
        $this->addSortKey('promo', array('-promo', 'name_sort'), 'promotion');
        $this->addSortKey('date_mod', array('-date', '-promo', 'name_sort'), 'dernière modification');
        parent::__construct($set, $data, $params);
    }

    public function fields()
    {
        return "m.uid, u.promo,
                a.alias AS bestalias, m.expertise, mp.pid,
                ms.secteur, ms.ss_secteur,
                nd.display AS name_display, nd.tooltip AS name_tooltip, nd.sort AS name_sort";
    }

    public function joins()
    {
        return "INNER JOIN  profile_names_display AS nd ON (nd.user_id = u.user_id)";
    }

    public function bounds()
    {
        $order = Env::v('order', $this->defaultkey);
        $show_bounds = 0;
        if (($order == "name") || ($order == "-name")) {
            $this->bound_field = "nom";
            $show_bounds = 1;
        } elseif (($order == "promo") || ($order == "-promo")) {
            $this->bound_field = "promo";
            $show_bounds = -1;
        }
        if ($order{0} == '-') {
            $show_bounds = -$show_bounds;
        }
        return $show_bounds;
    }

    public function templateName()
    {
        return 'include/plview.referent.tpl';
    }
}

class TrombiView extends MultipageView
{
    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->entriesPerPage = 24;
        $this->order = explode(',', Env::v('order', 'name_sort'));
        if (@$params['with_score']) {
            $this->addSortKey('score', array('-score', '-watch_last', '-promo', 'name_sort'), 'pertinence');
        }
        $this->addSortKey('name', array('name_sort'), 'nom');
        $this->addSortKey('promo', array('-promo', 'name_sort'), 'promotion');
        parent::__construct($set, $data, $params);
    }

    public function fields()
    {
        return "u.user_id, nd.display AS name_display, nd.tooltip AS name_tooltip, nd.sort AS name_sort, u.promo, a.alias AS forlife ";
    }

    public function joins()
    {
        return "INNER JOIN  photo AS p ON (p.uid = u.user_id)
                INNER JOIN  profile_names_display AS nd ON (nd.user_id = u.user_id)";
    }

    public function bounds()
    {
        $order = Env::v('order', $this->defaultkey);
        $show_bounds = 0;
        if (($order == "name") || ($order == "-name")) {
            $this->bound_field = "nom";
            $show_bounds = 1;
        } elseif (($order == "promo") || ($order == "-promo")) {
            $this->bound_field = "promo";
            $show_bounds = -1;
        }
        if ($order{0} == '-') {
            $show_bounds = -$show_bounds;
        }
        return $show_bounds;
    }

    public function templateName()
    {
        return 'include/plview.trombi.tpl';
    }

    public function apply(PlPage &$page)
    {
        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            global $globals;
            $page->assign('mainsiteurl', 'https://' . $globals->core->secure_domain . '/');
        }
        return parent::apply($page);
    }
}

class GeolocView implements PlView
{
    private $set;
    private $type;
    private $params;

    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->params = $params;
        $this->set   =& $set;
        $this->type   = $data;
    }

    private function use_map()
    {
        return is_file(dirname(__FILE__) . '/../modules/geoloc/dynamap.swf') &&
               is_file(dirname(__FILE__) . '/../modules/geoloc/icon.swf');
    }

    public function args()
    {
        $args = $this->set->args();
        unset($args['initfile']);
        unset($args['mapid']);
        return $args;
    }

    public function apply(PlPage &$page)
    {
        require_once 'geoloc.inc.php';
        require_once '../modules/search/search.inc.php';

        switch ($this->type) {
          case 'icon.swf':
            header("Content-type: application/x-shockwave-flash");
            header("Pragma:");
            readfile(dirname(__FILE__).'/../modules/geoloc/icon.swf');
            exit;

          case 'dynamap.swf':
            header("Content-type: application/x-shockwave-flash");
            header("Pragma:");
            readfile(dirname(__FILE__).'/../modules/geoloc/dynamap.swf');
            exit;

          case 'init':
            $page->changeTpl('geoloc/init.tpl', NO_SKIN);
            header('Content-Type: text/xml');
            header('Pragma:');
            if (!empty($GLOBALS['IS_XNET_SITE'])) {
                $page->assign('background', 0xF2E9D0);
            }
            break;

          case 'city':
            $page->changeTpl('geoloc/city.tpl', NO_SKIN);
            header('Content-Type: text/xml');
            header('Pragma:');
            $only_current = Env::v('only_current', false)? ' AND FIND_IN_SET(\'active\', adrf.statut)' : '';
            $it =& $this->set->get('u.user_id AS id, u.prenom, u.nom, u.promo, al.alias',
                                   "INNER JOIN  adresses AS adrf  ON (adrf.uid = u.user_id $only_current)
                                     LEFT JOIN  aliases  AS al   ON (u.user_id = al.id
                                                                   AND FIND_IN_SET('bestalias', al.flags))
                                    INNER JOIN  adresses AS avg ON (" . getadr_join('avg') . ")",
                                   'adrf.cityid = ' . Env::i('cityid'), null, null, 11);
            $page->assign('users', $it);
            break;

          case 'country':
            if (Env::has('debug')) {
                $page->changeTpl('geoloc/country.tpl', SIMPLE);
            } else {
                $page->changeTpl('geoloc/country.tpl', NO_SKIN);
                header('Content-Type: text/xml');
                header('Pragma:');
            }
            $mapid = Env::has('mapid') ? Env::i('mapid', -2) : false;
            list($countries, $cities) = geoloc_getData_subcountries($mapid, $this->set, 10);
            $page->assign('countries', $countries);
            $page->assign('cities', $cities);
            break;

          default:
            global $globals;
            if (!$this->use_map()) {
                $page->assign('request_geodesix', true);
            }
            $page->assign('annu', @$this->params['with_annu']);
            $page->assign('protocole', @$_SERVER['HTTPS'] ? 'https' : 'http');
            $this->set->get('u.user_id', null, "u.perms != 'pending' AND u.deces = 0", "u.user_id", null);
            return 'include/plview.geoloc.tpl';
        }
    }
}

class GadgetView implements PlView
{
    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->set =& $set;
    }

    public function fields()
    {
        return "u.user_id AS id,
                u.*, a.alias AS forlife," .
               "u.perms != 'pending' AS inscrit,
                u.perms != 'pending' AS wasinscrit,
                u.deces != 0 AS dcd, u.deces,
                FIND_IN_SET('femme', u.flags) AS sexe,
                adr.city, gp.a2, gp.pays AS countrytxt, gr.name AS region" .
                (S::logged() ? ", c.contact AS contact" : '');
    }

    public function joins()
    {
        return  "LEFT JOIN  adresses      AS adr ON (u.user_id = adr.uid AND FIND_IN_SET('active', adr.statut)".(S::logged() ? "" : "
                                                                         AND adr.pub = 'public'").")
                 LEFT JOIN  geoloc_pays   AS gp  ON (adr.country = gp.a2)
                 LEFT JOIN  geoloc_region AS gr  ON (adr.country = gr.a2 AND adr.region = gr.region)" .
                (S::logged() ?
                "LEFT JOIN  contacts      AS c   ON (c.contact = u.user_id AND c.uid = " . S::v('uid') . ")"
                 : "");
    }

    public function apply(PlPage &$page)
    {
        $page->assign_by_ref('set',
          $this->set->get($this->fields(), $this->joins(), null, null, null, 5, 0));
    }

    public function args()
    {
        return null;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
