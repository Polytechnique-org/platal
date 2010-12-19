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
    private $valid    = true;

    public function __construct($quick = false, PlFilterCondition $cond = null)
    {
        if (isset($no_search)) {
            return;
        }

        $this->quick = $quick;

        if (is_null($cond)) {
            $conds = new PFC_And();
        } else if ($cond instanceof PFC_And) {
            $conds = $cond;
        } else {
            $conds = new PFC_And($cond);
        }

        if ($quick) {
            $this->getQuick($conds);
        } else {
            $this->getAdvanced($conds);
        }
    }

    public function isValid()
    {
        return $this->valid;
    }

    /** Sets up the conditions for a Quick Search
     * @param $conds Additional conds (as a PFC_And)
     */
    private function getQuick($conds)
    {
        if (!S::logged()) {
            Env::kill('with_soundex');
        }

        require_once 'ufbuilder.inc.php';
        $ufb = new UFB_QuickSearch();

        if (!$ufb->isValid()) {
            $this->valid = false;
            return;
        }

        $ufc = $ufb->getUFC();
        $conds->addChild($ufc);

        $orders = $ufb->getOrders();

        if (S::logged() && Env::has('nonins')) {
            $conds = new PFC_And($conds,
                new PFC_Not(new UFC_Dead()),
                new PFC_Not(new UFC_Registered())
            );
        }

        parent::__construct($conds, $orders);
    }

    /** Sets up the conditions for an Advanced Search
     * @param $conds Additional conds (as a PFC_And)
     */
    private function getAdvanced($conds)
    {
        $this->advanced = true;
        require_once 'ufbuilder.inc.php';
        $ufb = new UFB_AdvancedSearch();

        if (!$ufb->isValid()) {
            $this->valid = false;
            return;
        }

        $ufc = $ufb->getUFC();
        $conds->addChild($ufc);

        $orders = $ufb->getOrders();

        parent::__construct($conds, $orders);
    }

    /** Add a "rechercher=Chercher" field to the query to simulate the POST
     * behaviour.
     */
    public function args()
    {
        $args = parent::args();
        if (!isset($args['rechercher'])) {
            $args['rechercher'] = 'Chercher';
        }
        return $args;
    }

    protected function &getFilterResults(PlFilter &$pf, PlLimit $limit)
    {
        $profiles = $pf->getProfiles($limit, Profile::FETCH_MINIFICHES);
        return $profiles;
    }
}

/** Simple set based on an array of User objects
 */
class ArraySet extends ProfileSet
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

/** A multipage view for profiles
 * Allows the display of bounds when sorting by name or promo.
 */
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
}

/** An extended multipage view for profiles, as minifiches.
 * Allows to sort by:
 * - score (for a search query)
 * - name
 * - promo
 * - latest modification
 *
 * Paramaters for this view are:
 * - with_score: whether to allow ordering by score (set only for a quick search)
 * - starts_with: show only names beginning with the given letter
 */
class MinificheView extends ProfileView
{
    public function __construct(PlSet &$set, array $params)
    {
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
        parent::__construct($set, $params);
    }

    public function apply(PlPage &$page)
    {
        if (array_key_exists('starts_with', $this->params)
            && $this->params['starts_with'] != ""
            && $this->params['starts_with'] != null) {

            $this->set->addCond(
                new UFC_Name(Profile::LASTNAME,
                    $this->params['starts_with'], UFC_Name::PREFIX)
            );
        }
        return parent::apply($page);
    }

    public function templateName()
    {
        return 'include/plview.minifiche.tpl';
    }
}

class MentorView extends ProfileView
{
    public function __construct(PlSet &$set, array $params)
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
        parent::__construct($set, $params);
    }

    public function templateName()
    {
        return 'include/plview.referent.tpl';
    }
}

class TrombiView extends ProfileView
{
    public function __construct(PlSet &$set, array $params)
    {
        $this->entriesPerPage = 24;
        $this->defaultkey = 'name';
        if (@$params['with_score']) {
            $this->addSort(new PlViewOrder('score', array(
                            new UFO_Score(true),
                            new UFO_ProfileUpdate(true),
                            new UFO_Promo(UserFilter::DISPLAY, true),
                            new UFO_Name(Profile::DN_SORT),
            ), 'pertinence'));
        }
        $set->addCond(new UFC_Photo());
        $this->addSort(new PlViewOrder('name', array(new UFO_Name(Profile::DN_SORT)), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                        new UFO_Promo(UserFilter::DISPLAY, true),
                        new UFO_Name(Profile::DN_SORT),
                    ), 'promotion'));
        parent::__construct($set, $params);
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
    public function __construct(PlSet &$set, array $params)
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
