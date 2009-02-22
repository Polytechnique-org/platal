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

class UserSet extends PlSet
{
    private $cond;

    public function __construct($cond = null)
    {
        $this->cond = new UFC_And();
        if (!is_null($cond)) {
            $this->cond->addChild($cond);
        }
    }

    public function &get($fields, $joins, $where, $groupby, $order, $limitcount = null, $limitfrom = null)
    {
        $uf = new UserFilter($this->cond);
        $users = $uf->getUsers($limitcount, $limitfrom);
        $this->count = $uf->getTotalCount();
        return $users;
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
        Platal::load('search', 'search.inc.php');
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
        Platal::load('search', 'search.inc.php');
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
            $where = "u.hruid IN ($where)";
        } else {
            $where = " 0 ";
        }
        parent::__construct('', $where);
    }

    private function getUids(array $users)
    {
        $users = User::getBulkHruid($users, array('User', '_silent_user_callback'));
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
        require_once 'education.func.inc.php';
        global $globals;
        $this->entriesPerPage = $globals->search->per_page;
        if (@$params['with_score']) {
            $this->addSortKey('score', array('-score', '-date', '-d.promo', 'sort_name'), 'pertinence');
        }
        $this->addSortKey('name', array('sort_name'), 'nom');
        $this->addSortKey('promo', array('-d.promo', 'sort_name'), 'promotion');
        $this->addSortKey('date_mod', array('-date', '-d.promo', 'sort_name'), 'dernière modification');
        parent::__construct($set, $data, $params);
    }

    public function bounds()
    {
        return null;
    }

    public function fields()
    {
        return null;
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
        $this->addSortKey('name', array('sort_name'), 'nom');
        $this->addSortKey('promo', array('-d.promo', 'sort_name'), 'promotion');
        $this->addSortKey('date_mod', array('-date', '-d.promo', 'sort_name'), 'dernière modification');
        parent::__construct($set, $data, $params);
    }

    public function fields()
    {
        return "m.uid, d.promo, u.hruid,
                m.expertise, mp.country, ms.sectorid, ms.subsectorid,
                d.directory_name, d.sort_name";
    }

    public function joins()
    {
        return "INNER JOIN  profile_display AS d ON (d.pid = u.user_id)";
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
        $this->order = explode(',', Env::v('order', 'sort_name'));
        if (@$params['with_score']) {
            $this->addSortKey('score', array('-score', '-watch_last', '-d.promo', 'sort_name'), 'pertinence');
        }
        $this->addSortKey('name', array('sort_name'), 'nom');
        $this->addSortKey('promo', array('-d.promo', 'sort_name'), 'promotion');
        parent::__construct($set, $data, $params);
    }

    public function fields()
    {
        return "u.user_id, d.directory_name, d.sort_name, u.promo, d.promo, u.hruid ";
    }

    public function joins()
    {
        return "INNER JOIN  photo           AS p  ON (p.uid = u.user_id)
                INNER JOIN  profile_display AS d  ON (d.pid = u.user_id)";
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

class GadgetView implements PlView
{
    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->set =& $set;
    }

    public function fields()
    {
        return "u.user_id AS id, u.*," .
               "u.perms != 'pending' AS inscrit,
                u.perms != 'pending' AS wasinscrit,
                u.deces != 0 AS dcd, u.deces,
                FIND_IN_SET('femme', u.flags) AS sexe,
                " // adr.city, gr.name AS region
              . "gc.iso_3166_1_a2, gc.countryFR AS countrytxt" .
                (S::logged() ? ", c.contact AS contact" : '');
    }

    public function joins()
    {
        return "LEFT JOIN  profile_addresses          AS adr ON (u.user_id = adr.pid AND
                                                                 FIND_IN_SET('current', adr.flags)"
                                                                . (S::logged() ? "" : "AND adr.pub = 'public'") . ")
                LEFT JOIN  geoloc_countries           AS gc  ON (adr.countryId = gc.iso_3166_1_a2)
                LEFT JOIN  geoloc_administrativeareas AS gr  ON (adr.countryId = gr.country
                                                                 AND adr.administrativeAreaId = gr.id)
               " . (S::logged() ?
               "LEFT JOIN  contacts                   AS c   ON (c.contact = u.user_id
                                                                 AND c.uid = " . S::v('uid') . ")" : "");
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
