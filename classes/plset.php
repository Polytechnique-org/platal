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

/** UserSet is a light-weight Model/View tool for displaying a set of items
 */
class PlSet
{
    private $from    = null;
    private $groupby = null;
    private $joins   = null;
    private $where   = null;

    private $count   = null;

    private $mods      = array();
    private $modParams = array();
    private $mod       = null;
    private $default   = null;

    public function __construct($from, $joins = '', $where = '', $groupby = '')
    {
        $this->from    = $from;
        $this->joins   = $joins;
        $this->where   = $where;
        $this->groupby = $groupby;
    }

    public function addMod($name, $description, $default = false, array $params = array())
    {
        $name = strtolower($name);
        $this->mods[$name]      = $description;
        $this->modParams[$name] = $params;
        if ($default) {
            $this->default = $name;
        }
    }

    public function rmMod($name)
    {
        $name = strtolower($name);
        unset($this->mods[$name]);
    }

    private function &query($fields, $from, $joins, $where, $groupby, $order, $limit)
    {
        if (trim($order)) {
            $order = "ORDER BY $order";
        }
        if (trim($where)) {
            $where = "WHERE $where";
        }
        if (trim($groupby)) {
            $groupby = "GROUP BY $groupby";
        }
        $query = "SELECT  SQL_CALC_FOUND_ROWS
                          $fields
                    FROM  $from
                          $joins
                          $where
                          $groupby
                          $order
                          $limit";
//        echo $query;
//        print_r($this);
        $it    = XDB::iterator($query);
        $count = XDB::query('SELECT FOUND_ROWS()');
        $this->count = intval($count->fetchOneCell());
        return $it;
    }

    public function &get($fields, $joins, $where, $groupby, $order, $limitcount = null, $limitfrom = null)
    {
        if (!is_null($limitcount)) {
            if (!is_null($limitfrom)) {
                $limitcount = "$limitfrom,$limitcount";
            }
            $limitcount = "LIMIT $limitcount";
        }
        $joins  = $this->joins . ' ' . $joins;
        $where  = $where;
        if (trim($this->where)) {
            if (trim($where)) {
                $where .= ' AND ';
            }
            $where .= $this->where;
        }
        if (!$groupby) {
            $groupby = $this->groupby;
        }
        return $this->query($fields, $this->from, $joins, $where, $groupby, $order, $limitcount);
    }

    public function count()
    {
        return $this->count;
    }

    private function &buildView($view, $data)
    {
        $view = strtolower($view);
        if (!$view || !class_exists($view . 'View') || !isset($this->mods[$view])) {
            $view = $this->default ? $this->default : $this->mods[0];
        }
        $this->mod = $view;
        $class = $view . 'View';
        if (!class_exists($class)) {
            $view = null;
        } else {
            $view = new $class($this, $data, $this->modParams[$this->mod]);
            if (!$view instanceof PlView) {
                $view = null;
            }
        }
        return $view;
    }

    public function apply($baseurl, PlatalPage &$page, $view = null, $data = null)
    {
        $view =& $this->buildView($view, $data);
        if (is_null($view)) {
            return false;
        }
        if (empty($GLOBALS['IS_XNET_SITE'])) {
            $page->changeTpl('core/plset.tpl');
        } else {
            new_group_open_page('core/plset.tpl');
        }
        $page->assign('plset_base', $baseurl);
        $page->assign('plset_mods', $this->mods);
        $page->assign('plset_mod', $this->mod);
        foreach ($this->modParams[$this->mod] as $param=>$value) {
            $page->assign($this->mod . '_' . $param, $value);
        }
        $page->assign('plset_content', $view->apply($page));
        $page->assign('plset_count', $this->count);
        return true;
    }
}

interface PlView
{
    public function __construct(PlSet &$set, $data, array $params);
    public function apply(PlatalPage &$page);
}

abstract class MultipageView implements PlView
{
    protected $set;

    public $pages  = 1;
    public $page   = 1;
    public $offset = 0;

    protected $order  = array();
    protected $entriesPerPage = 20;
    protected $params = array();

    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->set   =& $set;
        $this->page   = Env::i('page', 1);
        $this->offset = $this->entriesPerPage * ($this->page - 1);
        $this->params = $params;
    }

    public function joins()
    {
        return null;
    }

    public function where()
    {
        return null;
    }

    public function groupBy()
    {
        return null;
    }

    public function order()
    {
        foreach ($this->order as &$item) {
            if ($item{0} == '-') {
                $item = substr($item, 1) . ' DESC';
            }
        }
        return implode(', ', $this->order);
    }

    abstract public function templateName();

    public function apply(PlatalPage &$page)
    {
        $page->assign_by_ref('plview', $this);
        $page->assign_by_ref('set',
                             $this->set->get($this->fields(),
                                             $this->joins(),
                                             $this->where(),
                                             $this->groupBy(),
                                             $this->order(),
                                             $this->entriesPerPage,
                                             $this->offset));
        $count = $this->set->count();
        $this->pages = intval(ceil($count / $this->entriesPerPage));

        return 'include/plview.multipage.tpl';
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
