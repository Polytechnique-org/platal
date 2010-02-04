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


/** UserSet is a light-weight Model/View tool for displaying a set of items
 */
abstract class PlSet
{
    const DEFAULT_MAX_RES = 20;

    private $conds   = null;
    private $orders  = null;
    private $limit   = null;

    protected $count   = null;

    private $mods      = array();
    private $modParams = array();
    private $mod       = null;
    private $default   = null;

    public function __construct(PlFilterCondition &$cond, $orders)
    {
        if ($cond instanceof PFC_And) {
            $this->conds = $cond;
        } else {
            $this->conds = new PFC_And($cond);
        }

        if ($orders instanceof PlFilterOrder) {
            $this->orders[] = $order;
        } else {
            foreach ($orders as $order) {
                $this->orders[] = $order;
            }
        }
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

    public function addSort(PlFilterOrder &$order)
    {
        $this->orders[] = $order;
    }

    public function addCond(PlFilterCondition &$cond)
    {
        $this->conds->addChild($cond);
    }

    /** This function builds the right kind of PlFilter from given data
     * @param $cond The PlFilterCondition for the filter
     * @param $orders An array of PlFilterOrder for the filter
     */
    abstract protected function buildFilter(PlFilterCondition &$cond, $orders);

    public function &get(PlLimit $limit = null)
    {
        $pf = $this->buildFilter($this->conds, $this->orders);

        if (is_null($limit)) {
            $limit = new PlLimit(self::DEFAULT_MAX_RES, 0);
        }
        $it          = $pf->get($limit);
        $this->count = $pf->getTotalCount();
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

    public function apply($baseurl, PlPage &$page, $view = null, $data = null)
    {
        $view =& $this->buildView($view, $data);
        if (is_null($view)) {
            return false;
        }
        $args = $view->args();
        if (!isset($args['rechercher'])) {
            $args['rechercher'] = 'Chercher';
        }
        $page->coreTpl('plset.tpl');
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
    public function apply(PlPage &$page);
    public function args();
}

/** This class describes an Order as used in a PlView :
 * - It is based on a PlFilterOrder
 * - It has a short identifier
 * - It has a full name, to display on the page
 */
class PlViewOrder
{
    public $pfo         = null;
    public $name        = null;
    public $displaytext = null;

    /** Build a PlViewOrder
     * @param $name Name of the order (key)
     * @param $displaytext Text to display
     * @param $pfo PlFilterOrder for the order
     */
    public function __construct($name, PlFilterOrder &$pfo, $displaytext = null)
    {
        $this->name = $name;
        if (is_null($displaytext)) {
            $this->displaytext = ucfirst($name);
        } else {
            $this->displaytext = $displaytext;
        }
        $this->pfo = $pfo;
    }
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

    protected $bound_field = null;

    /** Builds a MultipageView
     * @param $set The associated PlSet
     * @param $data Data for the PlSet
     * @param $params Parameters of the view
     */
    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->set   =& $set;
        $this->page   = Env::i('page', 1);
        $this->offset = $this->entriesPerPage * ($this->page - 1);
        $this->params = $params;
    }

    /** Add an order to the view
     */
    protected function addSort(PlViewOrder &$pvo, $default = false)
    {
        $this->sortkeys[$pvo->name] = $pvo;
        if (!$this->defaultkey || $default) {
            $this->defaultkey = $pvo->name;
        }
    }

    /** Returns a list of PFO objects, the "default" one first
     */
    public function order()
    {
        $order = Env::v('order', $this->defaultkey);
        $invert = ($order{0} == '-');
        if ($invert) {
            $order = substr($order, 1);
        }
        $list = array();
        if (count($this->sortkeys)) {
            $list[0] = null;
        }
        foreach ($this->sortkeys as $name => $sort) {
            $desc = $sort->pfo->isDescending();;
            if ($invert) {
                $sort->pfo->toggleDesc();
            }
            if ($name == $order) {
                $list[0] = $sort->pfo;
            } else {
                $list[] = $sort->pfo;
            }
        }
        return $list;
    }

    /** Returns information on the order of bounds
     * @return * 1  if normal bounds
     *         * -1 if inversed bounds
     *         * 0  if bounds shouldn't be displayed
     */
    public function bounds()
    {
        return null;
    }

    public function limit()
    {
        return null;
    }

    /** Name of the template to use for displaying items of the view
     */
    abstract public function templateName();

    /** Returns the value of a boundary of the current view (in order
     * to show "from C to F")
     * @param $obj The boundary result whose value must be shown to the user
     */
    abstract protected function getBoundValue($obj);

    /** Applies the view to a page
     * @param $page Page to which the view will be applied
     */
    public function apply(PlPage &$page)
    {
        foreach ($this->order() as $order) {
            $this->set->addSort($order->pfo);
        }
        $res = $this->set->get($this->limit());

        $show_bounds = $this->bounds();
        $end         = end($res);
        if ($show_bounds) {
            if ($show_bounds == 1) {
                $first = $this->getBoundValue($res[0]);
                $last  = $this->getBoundValue($end);
            } elseif ($show_bounds == -1) {
                $first = $this->getBoundValue($end);
                $last  = $this->getBoundValue($res[0]);
            }
            $page->assign('first', $first);
            $page->assign('last', $last);
        }

        $page->assign('show_bounds', $show_bounds);
        $page->assign('order', Env::v('order', $this->defaultkey));
        $page->assign('orders', $this->sortkeys);
        $page->assign_by_ref('plview', $this);
        $page->assign_by_ref('set', $res);
        $count = $this->set->count();
        $this->pages = intval(ceil($count / $this->entriesPerPage));
        return PlPage::getCoreTpl('plview.multipage.tpl');
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
