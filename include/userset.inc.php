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

class UserSet extends PlSet
{
    public function __construct(PlFilterCondition &$cond, $orders = null)
    {
        parent::__construct($cond, $orders);
    }

    protected function buildFilter(PlFilterCondition &$cond, $orders)
    {
        return new UserFilter($cond, $orders);
    }
}

class ProfileSet extends PlSet
{
    public function __construct(PlFilterCondition &$cond, $orders = null)
    {
        parent::__construct($cond, $orders);
    }

    protected function buildFilter(PlFilterCondition &$cond, $orders)
    {
        return new ProfileFilter($cond, $orders);
    }
}

class SearchSet extends ProfileSet
{
    public  $advanced = false;
    private $score    = null;
    private $quick    = false;

    public function __construct($quick = false, $no_search = false, PlFilterCondition $cond = null)
    {
        if ($no_search) {
            return;
        }

        $this->quick = $quick;

        if (is_null($cond)) {
            $this->conds = new PFC_And();
        } else if ($cond instanceof PFC_And) {
            $this->conds = $cond;
        } else {
            $this->conds = new PFC_And($cond);
        }

        if ($quick) {
            $this->getQuick();
        } else {
            $this->getAdvanced();
        }
    }

    private function getQuick()
    {
        if (!S::logged()) {
            Env::kill('with_soundex');
        }

        require_once 'ufbuilder.inc.php';
        $ufb = new UFB_QuickSearch();

        if (!$ufb->isValid()) {
            return;
        }

        $ufc = $ufb->getUFC();
        $this->conds->addChild($ufc);

        $orders = $ufb->getOrders();

        if (S::logged() && Env::has('nonins')) {
            $this->conds = new PFC_And($this->conds,
                new PFC_Not(new UFC_Dead()),
                new PFC_Not(new UFC_Registered())
            );
        }

        parent::__construct($this->conds, $orders);
    }

    private function getAdvanced()
    {
        $this->advanced = true;
        require_once 'ufbuilder.inc.php';
        $ufb = new UFB_AdvancedSearch();

        if (!$ufb->isValid()) {
            return;
        }

        $this->conds->addChild($ufb->getUFC());
    }

    public function &get(PlLimit $limit = null, $orders = array())
    {
        $orders = array_merge($orders, $this->orders);

        $uf = $this->buildFilter($this->conds, $orders);
        if (is_null($limit)) {
            $limit = new PlLimit(20, 0);
        }
        $it          = $uf->getProfiles($limit);
        $this->count = $uf->getTotalCount();
        return $it;
    }
}

class ArraySet extends UserSet
{
    public function __construct(array $users)
    {
        $hruids = User::getBulkHruid($users, array('User', '_silent_user_callback'));
        if (is_null($hruids) || count($hruids) == 0) {
            $cond = new PFC_False();
        } else {
            $cond = new UFC_Hruid($hruids);
        }
        parent::__construct($cond);
    }
}

abstract class ProfileView extends MultipageView
{
    protected function getBoundValue($obj)
    {
        if ($obj instanceof Profile) {
            switch ($this->bound_field) {
            case 'name':
                $name = $obj->name('%l');
                return strtoupper($name);
            case 'promo':
                return $obj->promo();
            default:
                return null;
            }
        }
        return null;
    }
}

class MinificheView extends ProfileView
{
    public function __construct(PlSet &$set, $data, array $params)
    {
        require_once 'education.func.inc.php';
        global $globals;
        $this->entriesPerPage = $globals->search->per_page;
        if (@$params['with_score']) {
            $this->addSort(new PlViewOrder('score', array(
                    new UFO_Score(true),
                    new UFO_ProfileUpdate(true),
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(Profile::DN_SORT),
                ), 'pertinence'));
        }
        $this->addSort(new PlViewOrder(
                            'name',
                            array(new UFO_Name(Profile::DN_SORT)),
                            'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(Profile::DN_SORT),
                ), 'promotion'));
        $this->addSort(new PlViewOrder('date_mod', array(
                    new UFO_ProfileUpdate(true),
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(Profile::DN_SORT),
                ), 'dernière modification'));
        parent::__construct($set, $data, $params);
    }

    public function bounds()
    {
        $order = Env::v('order', $this->defaultkey);
        $show_bounds = 0;
        if (($order == "name") || ($order == "-name")) {
            $this->bound_field = "name";
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

class MentorView extends ProfileView
{
    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->entriesPerPage = 10;
        $this->addSort(new PlViewOrder('rand', array(new PFO_Random(S::i('uid'))), 'aléatoirement'));
        $this->addSort(new PlViewOrder('name', array(new UFO_Name(Profile::DN_SORT)), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(Profile::DN_SORT),
                ), 'promotion'));
        $this->addSort(new PlViewOrder('date_mod', array(
                    new UFO_ProfileUpdate(true),
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(Profile::DN_SORT),
                ), 'dernière modification'));
        parent::__construct($set, $data, $params);
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

class TrombiView extends ProfileView
{
    public function __construct(PlSet &$set, $data, array $params)
    {
        $this->entriesPerPage = 24;
        if (@$params['with_score']) {
            $this->addSort(new PlViewOrder('score', array(
                            new UFO_Score(true),
                            new UFO_ProfileUpdate(true),
                            new UFO_Promo(UserFilter::DISPLAY, true),
                            new UFO_Name(Profile::DN_SORT),
            ), 'pertinence'));
        }
        $this->addSort(new PlViewOrder('name', array(new UFO_Name(Profile::DN_SORT)), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                        new UFO_Promo(UserFilter::DISPLAY, true),
                        new UFO_Name(Profile::DN_SORT),
                    ), 'promotion'));
        parent::__construct($set, $data, $params);
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

    public function apply(PlPage &$page)
    {
        $page->assign_by_ref('set', $this->set->get(new PlLimit(5, 0)));
    }

    public function args()
    {
        return null;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
