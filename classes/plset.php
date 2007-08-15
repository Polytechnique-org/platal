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

    public function args()
    {
        $get = $_GET;
        unset($get['n']);
        return $get;
    }

    protected function encodeArgs(array $args, $encode = false)
    {
        $qs = '?';
        $sep = '&';
        foreach ($args as $k=>$v) {
            if (!$encode) {
                $k = urlencode($k);
                $v = urlencode($v);
            }
            $qs .= "$k=$v$sep";
        }
        return $encode ? urlencode($qs) : $qs;
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
            reset($this->mods);
            $view = $this->default ? $this->default : key($this->mods);
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
        $args = $view->args();
        if (!isset($args['rechercher'])) {
            $args['rechercher'] = 'Chercher';
        }
        $page->changeTpl('core/plset.tpl');
        $page->assign('plset_base', $baseurl);
        $page->assign('plset_mods', $this->mods);
        $page->assign('plset_mod', $this->mod);
        $page->assign('plset_search', $this->encodeArgs($args));
        $page->assign('plset_search_enc', $this->encodeArgs($args, true));
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
    public function args();
}

abstract class MultipageView implements PlView
{
    protected $set;

    public $pages  = 1;
    public $page   = 1;
    public $offset = 0;

    protected $entriesPerPage = 20;
    protected $params = array();

    protected $sortkeys = array();
    protected $defaultkey = null;

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

    protected function addSortKey($name, array $keys, $desc, $default = false)
    {
        $this->sortkeys[$name] = array('keys' => $keys, 'desc' => $desc);
        if (!$this->defaultkey || $default) {
            $this->defaultkey = $name;
        }
    }

    public function order()
    {
        $order = Env::v('order', $this->defaultkey);
        $invert = ($order{0} == '-');
        if ($invert) {
            $order = substr($order, 1);
        }
        $list = array();
        foreach ($this->sortkeys[$order]['keys'] as $item) {
            $desc = ($item{0} == '-');
            if ($desc) {
                $item = substr($item, 1);
            }
            if ($desc xor $invert) {
                $item .= ' DESC';
            }
            $list[] = $item;
        }
        return implode(', ', $list);
    }

    abstract public function templateName();

    public function apply(PlatalPage &$page)
    {
        $page->assign('order', Env::v('order', $this->defaultkey));
        $page->assign('orders', $this->sortkeys);
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

    public function args()
    {
        $list = $this->set->args();
        unset($list['page']);
        unset($list['order']);
        return $list;
    } 
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
