<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
    public function __construct(PlFilterCondition $cond, $orders = null)
    {
        parent::__construct($cond, $orders);
    }

    protected function buildFilter(PlFilterCondition $cond, $orders)
    {
        return new UserFilter($cond, $orders);
    }
}

class ProfileSet extends PlSet
{
    public function __construct(PlFilterCondition $cond, $orders = null)
    {
        parent::__construct($cond, $orders);
    }

    protected function buildFilter(PlFilterCondition $cond, $orders)
    {
        return new ProfileFilter($cond, $orders);
    }
}

require_once "ufbuilder.inc.php";

class SearchSet extends ProfileSet
{
    protected $score    = null;
    protected $valid    = true;

    public function __construct(UserFilterBuilder $ufb, PlFilterCondition $cond = null)
    {
        if (is_null($cond)) {
            $conds = new PFC_And();
        } else if ($cond instanceof PFC_And) {
            $conds = $cond;
        } else {
            $conds = new PFC_And($cond);
        }

        if (!$ufb->isValid()) {
            $this->valid = false;
            return;
        }

        $ufc = $ufb->getUFC();
        $conds->addChild($ufc);

        $orders = $ufb->getOrders();

        parent::__construct($conds, $orders);
    }

    public function isValid()
    {
        return $this->valid;
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

    protected function &getFilterResults(PlFilter $pf, PlLimit $limit)
    {
        $profiles = $pf->getProfiles($limit, Profile::FETCH_MINIFICHES);
        return $profiles;
    }
}

// Specialized SearchSet for quick search.
class QuickSearchSet extends SearchSet
{
    public function __construct(PlFilterCondition $cond = null)
    {
        if (!S::logged()) {
            Env::kill('with_soundex');
        }

        parent::__construct(new UFB_QuickSearch(), $cond);
    }
}

// Specialized SearchSet for advanced search.
class AdvancedSearchSet extends SearchSet
{
    public function __construct($xorg_admin_fields, $ax_admin_fields,
                                PlFilterCondition $cond = null)
    {
        parent::__construct(new UFB_AdvancedSearch($xorg_admin_fields, $ax_admin_fields),
                            $cond);
    }
}

/** Simple set based on an array of User emails
 */
class UserArraySet extends UserSet
{
    public function __construct(array $emails)
    {
        parent::__construct(new UFC_Email($emails));
    }
}

/** Simple set based on an array of Profile emails
 */
class ProfileArraySet extends ProfileSet
{
    public function __construct(array $emails)
    {
        parent::__construct(new UFC_Email($emails));
    }
}


/** A multipage view for profiles or users
 * Allows the display of bounds when sorting by name or promo.
 */
abstract class MixedView extends MultipageView
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
        } elseif ($obj instanceof User) {
            switch ($this->bound_field) {
            case 'name':
                $name = $obj->lastName();
                return strtoupper($name);
            case 'promo':
                if ($obj->hasProfile()) {
                    return $obj->profile()->promo();
                } else {
                    return 'ext';
                }
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
class MinificheView extends MixedView
{
    public function __construct(PlSet $set, array $params)
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

    public function apply(PlPage $page)
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

class MentorView extends MixedView
{
    public function __construct(PlSet $set, array $params)
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

class GroupMemberView extends MixedView
{
    public function __construct(PlSet $set, array $params)
    {
        $this->entriesPerPage = 20;
        $this->addSort(new PlViewOrder('name', array(new UFO_Name(Profile::DN_SORT)), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(Profile::DN_SORT),
                ), 'promotion'));
        parent::__construct($set, $params);
    }

    public function templateName()
    {
        return 'include/plview.groupmember.tpl';
    }
}

class ListMemberView extends MixedView
{
    public function __construct(PlSet $set, array $params)
    {
        $this->entriesPerPage = 100;
        $this->addSort(new PlViewOrder('name', array(new UFO_Name(Profile::DN_SORT)), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(Profile::DN_SORT),
                ), 'promotion'));
        parent::__construct($set, $params);
    }

    public function templateName()
    {
        return 'include/plview.listmember.tpl';
    }
}

class TrombiView extends MixedView
{
    public function __construct(PlSet $set, array $params)
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

    public function apply(PlPage $page)
    {
        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            global $globals;
            $page->assign('mainsiteurl', 'https://' . $globals->core->secure_domain . '/');
        }
        return parent::apply($page);
    }
}

class MapView implements PlView
{
    private $set;

    public function __construct(PlSet $set, array $params)
    {
        $this->set = $set;
    }

    public function apply(PlPage $page)
    {
        Platal::load('geoloc');

        if (Get::b('ajax')) {
            $pids = $this->set->getIds(new PlLimit());
            GeolocModule::assign_json_to_map($page, $pids);
            $page->runJSON();
            exit;
        } else {
            $this->set->getIds(new PlLimit());
            GeolocModule::prepare_map($page);
            return 'geoloc/index.tpl';
        }
    }

    public function args()
    {
        return $this->set->args();
    }
}

class GadgetView implements PlView
{
    public function __construct(PlSet $set, array $params)
    {
        $this->set =& $set;
    }

    public function apply(PlPage $page)
    {
        $page->assign_by_ref('set', $this->set->get(new PlLimit(5, 0)));
    }

    public function args()
    {
        return null;
    }
}

class AddressesView implements PlView
{
    private $set;

    public function __construct(PlSet $set, array $params)
    {
        $this->set =& $set;
    }

    public function apply(PlPage $page)
    {
        $pids = $this->set->getIds(new PlLimit());
        $visibility = new ProfileVisibility(ProfileVisibility::VIS_AX);
        pl_cached_content_headers('text/x-csv', 1);

        $csv = fopen('php://output', 'w');
        fputcsv($csv, array('adresses'), ';');
        $res = XDB::query('SELECT  pd.public_name, pa.postalText
                             FROM  profile_addresses AS pa
                       INNER JOIN  profile_display   AS pd ON (pd.pid = pa.pid)
                            WHERE  pa.type = \'home\' AND pa.pub IN (\'public\', \'ax\') AND FIND_IN_SET(\'mail\', pa.flags) AND pa.pid IN {?}
                         GROUP BY  pa.pid', $pids);
        foreach ($res->fetchAllAssoc() as $item) {
            fputcsv($csv, $item, ';');
        }
        fclose($csv);
        exit();
    }

    public function args()
    {
        return $this->set->args();
    }
}

class JSonView implements PlView
{
    private $set;
    private $params;

    public function __construct(PlSet $set, array $params)
    {
        $this->set    = $set;
        $this->params = $params;
    }

    public function apply(PlPage $page)
    {
        $export = array();
        $start  = isset($this->params['offset']) ? $this->params['offset'] : 0;
        $count  = isset($this->params['count'])  ? $this->params['count']  : 10;
        $profiles = $this->set->get(new PlLimit($start, $count));
        foreach ($profiles as $profile) {
            $export[] = $profile->export();
        }
        $page->jsonAssign('profile_count', $this->set->count());
        $page->jsonAssign('profiles', $export);
    }

    public function args()
    {
        return $this->set->args();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
