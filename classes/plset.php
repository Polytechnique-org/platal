<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

    protected $conds   = null;
    protected $orders  = array();
    protected $limit   = null;

    protected $count   = null;

    // A list of available views
    private $mods      = array();
    // An array of $view_name => array($parameters)
    private $modParams = array();
    // The current view name
    private $mod       = null;
    // The default view name
    private $default   = null;

    public function __construct(PlFilterCondition &$cond, $orders = null)
    {
        if ($cond instanceof PFC_And) {
            $this->conds = $cond;
        } else {
            $this->conds = new PFC_And($cond);
        }

        if (!is_null($orders) && $orders instanceof PlFilterOrder) {
            $this->addSort($orders);
        } else if (is_array($orders)){
            foreach ($orders as $order) {
                $this->addSort($order);
            }
        }
    }

    /** Adds a new view (minifiche, trombi, map)
     * @param $name The name of the view (cf buildView)
     * @param $description A user-friendly name for the view
     * @param $default Whether this is the default view
     * @param $params Parameters used to tune the view (display promo, order by
     *                  score...)
     */
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

    /** Adds a new sort (on the PlFilter)
     */
    public function addSort(PlFilterOrder &$order)
    {
        $this->orders[] = $order;
    }

    /** Adds a new condition to the PlFilter
     */
    public function addCond(PlFilterCondition &$cond)
    {
        $this->conds->addChild($cond);
    }

    /** This function builds the right kind of PlFilter from given data
     * @param $cond The PlFilterCondition for the filter
     * @param $orders An array of PlFilterOrder for the filter
     */
    abstract protected function buildFilter(PlFilterCondition &$cond, $orders);

    /** This function returns the results of the given filter
     * wihtin $limit; can be use to replace the default $pf->get call.
     * @param &$pf The filter
     * @param $limit The PlLimit
     * @return The results of the filter
     */
    protected function &getFilterResults(PlFilter &$pf, PlLimit $limit)
    {
        $res = $pf->get($limit);
        return $res;
    }

    /** This function returns the values of the set, and sets $count with the
     * total number of results.
     * @param $limit A PlLimit for selecting users
     * @param $orders An optional array of PFO to use before the "default" ones
     * @return The result of $pf->get();
     */
    public function &get(PlLimit $limit = null, $orders = array())
    {
        if (!is_array($orders)) {
            $orders = array($orders);
        }

        $orders = array_merge($orders, $this->orders);

        $pf = $this->buildFilter($this->conds, $orders);

        if (is_null($limit)) {
            $limit = new PlLimit(self::DEFAULT_MAX_RES, 0);
        }
        $it          = $this->getFilterResults($pf, $limit);
        $this->count = $pf->getTotalCount();
        return $it;
    }

    /** XXX ??
     */
    public function args()
    {
        $get = $_GET;
        unset($get['n']);
        return $get;
    }

    /** XXX?
     */
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

    /** Builds the view class from the given parameters
     * @param $view A string ('profile' for 'ProfileView'); if null,
     *          the default view is used.
     * @return A new PlView instance.
     */
    private function &buildView($view)
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
            $view = new $class($this, $this->modParams[$this->mod]);
            if (!$view instanceof PlView) {
                $view = null;
            }
        }
        return $view;
    }

    /** Creates the view: sets the page template, assigns Smarty vars.
     * @param $baseurl The base URL for this (for instance, "search/")
     * @param $page The page in which the view should be loaded
     * @param $view The name of the view; if null, the default one will be used.
     */
    public function apply($baseurl, PlPage &$page, $view = null)
    {
        $view =& $this->buildView($view);
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
    /** Constructs a new PlView
     * @param $set The set
     * @param $params Parameters to tune the view (sort by score, include promo...)
     */
    public function __construct(PlSet &$set, array $params);

    /** Applies the view to a page
     * The content of the set is fetched here.
     * @param $page Page to which the view will be applied
     * @return The name of the global view template (for displaying the view,
     *              not the items of the set)
     */
    public function apply(PlPage &$page);

    /** XXX?
     */
    public function args();
}

/** This class describes an Order as used in a PlView :
 * - It is based on a PlFilterOrder
 * - It has a short identifier
 * - It has a full name, to display on the page
 */
class PlViewOrder
{
    public $pfos        = null;
    public $name        = null;
    public $displaytext = null;

    /** Build a PlViewOrder
     * @param $name Name of the order (key)
     * @param $displaytext Text to display
     * @param $pfos Array of PlFilterOrder for the order
     */
    public function __construct($name, $pfos, $displaytext = null)
    {
        $this->name = $name;
        if (is_null($displaytext)) {
            $this->displaytext = ucfirst($name);
        } else {
            $this->displaytext = $displaytext;
        }
        $this->pfos = $pfos;
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
     * @param $params Parameters of the view
     */
    public function __construct(PlSet &$set, array $params)
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

    /** Returns a list of PFO objects in accordance with the user's choice
     */
    public function order()
    {
        $order = Env::v('order', $this->defaultkey);
        $invert = ($order{0} == '-');
        if ($invert) {
            $order = substr($order, 1);
        }

        $ordering = $this->sortkeys[$order];
        if ($invert) {
            foreach ($ordering->pfos as $pfo) {
                $pfo->toggleDesc();
            }
        }
        return $ordering->pfos;
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
        return new PlLimit($this->entriesPerPage, $this->offset);
    }

    /** Name of the template to use for displaying items of the view
     * e.g plview.minifiche.tpl, plview.trombi.pl, ...
     */
    abstract public function templateName();

    /** Returns the value of a boundary of the current view (in order
     * to show "from C to F")
     * @param $obj The boundary result whose value must be shown to the user
     *              (e.g a Profile, ...)
     * @return The bound
     */
    abstract protected function getBoundValue($obj);

    public function apply(PlPage &$page)
    {
        foreach ($this->order() as $order) {
            if (!is_null($order)) {
                $this->set->addSort($order);
            }
        }
        $res = $this->set->get($this->limit());

        $show_bounds = $this->bounds();
        if ($show_bounds) {
            $start = current($res);
            $end   = end($res);
            if ($show_bounds == 1) {
                $first = $this->getBoundValue($start);
                $last  = $this->getBoundValue($end);
            } elseif ($show_bounds == -1) {
                $first = $this->getBoundValue($end);
                $last  = $this->getBoundValue($start);
            }
            $page->assign('first', $first);
            $page->assign('last', $last);
        }

        $page->assign('show_bounds', $show_bounds);
        $page->assign('order', Env::v('order', $this->defaultkey));
        $page->assign('orders', $this->sortkeys);
        $page->assign_by_ref('plview', $this);
        if (is_array($res)) {
            $page->assign('set_keys', array_keys($res));
        }
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
