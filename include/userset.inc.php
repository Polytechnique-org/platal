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

require_once('xorg.misc.inc.php');
require_once('user.func.inc.php');

global $globals;

@$globals->search->result_where_statement = '
    LEFT JOIN  applis_ins     AS ai0 ON (u.user_id = ai0.uid AND ai0.ordre = 0)
    LEFT JOIN  applis_def     AS ad0 ON (ad0.id = ai0.aid)
    LEFT JOIN  applis_ins     AS ai1 ON (u.user_id = ai1.uid AND ai1.ordre = 1)
    LEFT JOIN  applis_def     AS ad1 ON (ad1.id = ai1.aid)
    LEFT JOIN  entreprises    AS e   ON (e.entrid = 0 AND e.uid = u.user_id)
    LEFT JOIN  emploi_secteur AS es  ON (e.secteur = es.id)
    LEFT JOIN  fonctions_def  AS ef  ON (e.fonction = ef.id)
    LEFT JOIN  geoloc_pays    AS n   ON (u.nationalite = n.a2)
    LEFT JOIN  adresses       AS adr ON (u.user_id = adr.uid AND FIND_IN_SET(\'active\',adr.statut))
    LEFT JOIN  geoloc_pays    AS gp  ON (adr.country = gp.a2)
    LEFT JOIN  geoloc_region  AS gr  ON (adr.country = gr.a2 AND adr.region = gr.region)
    LEFT JOIN  emails         AS em  ON (em.uid = u.user_id AND em.flags = \'active\')';

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
            $this->addSortKey('score', array('-score', '-date', '-promo', 'nom', 'prenom'), 'pertinence');
        }
        $this->addSortKey('name', array('nom', 'prenom'), 'nom');
        $this->addSortKey('promo', array('-promo', 'nom', 'prenom'), 'promotion');
        $this->addSortKey('date_mod', array('-date', '-promo', 'nom', 'prenom'), 'dernière modification');
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
                IF(n.nat='',n.pays,n.nat) AS nat, n.a2 AS iso3166,
                ad0.text AS app0text, ad0.url AS app0url, ai0.type AS app0type,
                ad1.text AS app1text, ad1.url AS app1url, ai1.type AS app1type,
                adr.city, gp.a2, gp.pays AS countrytxt, gr.name AS region,
                IF(u.nom_usage<>'',u.nom_usage,u.nom) AS sortkey,
                (COUNT(em.email) > 0 OR FIND_IN_SET('googleapps', u.mail_storage) > 0) AS actif" .
                (S::logged() ? ", c.contact AS contact" : '');
    }

    public function joins()
    {
        return  "LEFT JOIN  entreprises    AS e   ON (e.entrid = 0 AND e.uid = u.user_id".(S::logged() ? "" : " AND e.pub = 'public'").")
                 LEFT JOIN  emploi_secteur AS es  ON (e.secteur = es.id)
                 LEFT JOIN  fonctions_def  AS ef  ON (e.fonction = ef.id)
                 LEFT JOIN  geoloc_pays    AS n   ON (u.nationalite = n.a2)
                 LEFT JOIN  applis_ins     AS ai0 ON (u.user_id = ai0.uid AND ai0.ordre = 0)
                 LEFT JOIN  applis_def     AS ad0 ON (ad0.id = ai0.aid)
                 LEFT JOIN  applis_ins     AS ai1 ON (u.user_id = ai1.uid AND ai1.ordre = 1)
                 LEFT JOIN  applis_def     AS ad1 ON (ad1.id = ai1.aid)
                 LEFT JOIN  adresses       AS adr ON (u.user_id = adr.uid
                                                      AND FIND_IN_SET('active', adr.statut)".(S::logged() ? "" : " AND adr.pub = 'public'").")
                 LEFT JOIN  geoloc_pays    AS gp  ON (adr.country = gp.a2)
                 LEFT JOIN  geoloc_region  AS gr  ON (adr.country = gr.a2 AND adr.region = gr.region)
                 LEFT JOIN  emails         AS em  ON (em.uid = u.user_id AND em.flags = 'active')" .
                (S::logged() ?
                 "LEFT JOIN  contacts       AS c   On (c.contact = u.user_id AND c.uid = " . S::v('uid') . ")"
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
        $this->addSortKey('name', array('nom', 'prenom'), 'nom');
        $this->addSortKey('promo', array('-promo', 'nom', 'prenom'), 'promotion');
        $this->addSortKey('date_mod', array('-date', '-promo', 'nom', 'prenom'), 'dernière modification');
        parent::__construct($set, $data, $params);
    }

    public function fields()
    {
        return "m.uid, u.prenom, u.nom, u.promo,
                a.alias AS forlife, m.expertise, mp.pid,
                ms.secteur, ms.ss_secteur";
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
        $this->order = explode(',', Env::v('order', 'nom,prenom,promo'));
        if (@$params['with_score']) {
            $this->addSortKey('score', array('-score', '-watch_last', '-promo', 'nom', 'prenom'), 'pertinence');
        }
        $this->addSortKey('name', array('nom', 'prenom'), 'nom');
        $this->addSortKey('promo', array('-promo', 'nom', 'prenom'), 'promotion');
        parent::__construct($set, $data, $params);
    }

    public function fields()
    {
        return "u.user_id, IF(u.nom_usage != '', u.nom_usage, u.nom) AS nom, u.prenom, u.promo, a.alias AS forlife ";
    }

    public function joins()
    {
        return "INNER JOIN  photo AS p ON (p.uid = u.user_id) ";
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

    public function apply(PlatalPage &$page)
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

    public function apply(PlatalPage &$page)
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
                (S::logged() ? "q.profile_mobile AS mobile, " : "IF(q.profile_mobile_pub = 'public', q.profile_mobile, NULL) as mobile, ") .
               "u.perms != 'pending' AS inscrit,
                u.perms != 'pending' AS wasinscrit,
                u.deces != 0 AS dcd, u.deces,
                FIND_IN_SET('femme', u.flags) AS sexe,
                adr.city, gp.a2, gp.pays AS countrytxt, gr.name AS region" .
                (S::logged() ? ", c.contact AS contact" : '');
    }

    public function joins()
    {
        return  "LEFT JOIN  adresses       AS adr ON (u.user_id = adr.uid AND FIND_IN_SET('active', adr.statut)".(S::logged() ? "" : " AND adr.pub = 'public'").")
                 LEFT JOIN  geoloc_pays    AS gp  ON (adr.country = gp.a2)
                 LEFT JOIN  geoloc_region  AS gr  ON (adr.country = gr.a2 AND adr.region = gr.region)" .
                (S::logged() ?
                 "LEFT JOIN  contacts       AS c   On (c.contact = u.user_id AND c.uid = " . S::v('uid') . ")"
                 : "");
    }

    public function apply(PlatalPage &$page)
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
