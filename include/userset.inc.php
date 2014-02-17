<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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
                    new UFO_Name(),
                ), 'pertinence'));
        }
        $this->addSort(new PlViewOrder(
                            'name',
                            array(new UFO_Name()),
                            'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(),
                ), 'promotion'));
        $this->addSort(new PlViewOrder('date_mod', array(
                    new UFO_ProfileUpdate(true),
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(),
                ), 'dernière modification'));
        parent::__construct($set, $params);
    }

    public function apply(PlPage $page)
    {
        if (array_key_exists('starts_with', $this->params)
            && $this->params['starts_with'] != ""
            && $this->params['starts_with'] != null) {

            $this->set->addCond(
                new UFC_NameInitial($this->params['starts_with'])
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
        $this->addSort(new PlViewOrder('name', array(new UFO_Name()), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(),
                ), 'promotion'));
        $this->addSort(new PlViewOrder('date_mod', array(
                    new UFO_ProfileUpdate(true),
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(),
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
        $this->addSort(new PlViewOrder('name', array(new UFO_Name()), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(),
                ), 'promotion'));
        $this->addSort(new PlViewOrder('date_mod', array(
                    new UFO_ProfileUpdate(true),
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(),
                ), 'dernière modification'));
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
        $this->addSort(new PlViewOrder('name', array(new UFO_Name()), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                    new UFO_Promo(UserFilter::DISPLAY, true),
                    new UFO_Name(),
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
    private $full_count;

    public function __construct(PlSet $set, array $params)
    {
        $set->getIds();
        $this->full_count = $set->count();

        $this->entriesPerPage = 24;
        $this->defaultkey = 'name';
        if (@$params['with_score']) {
            $this->addSort(new PlViewOrder('score', array(
                            new UFO_Score(true),
                            new UFO_ProfileUpdate(true),
                            new UFO_Promo(UserFilter::DISPLAY, true),
                            new UFO_Name(),
            ), 'pertinence'));
        }
        $set->addCond(new UFC_Photo());
        $this->addSort(new PlViewOrder('name', array(new UFO_Name()), 'nom'));
        $this->addSort(new PlViewOrder('promo', array(
                        new UFO_Promo(UserFilter::DISPLAY, true),
                        new UFO_Name(),
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
        $page->assign('full_count', $this->full_count);
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
            $uids = $this->set->getIds(new PlLimit());
            $pids = Profile::getPIDsFromUIDs($uids);
            GeolocModule::assign_json_to_map($page, $pids);
            $page->runJSON();
            exit;
        } else {
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

    /* Convert a single address field into 3 lines.
     */
    public static function split_address($address)
    {
        $lines = preg_split("/(\r|\n)+/", $address, -1, PREG_SPLIT_NO_EMPTY);
        $nb_lines = count($lines);
        switch ($nb_lines) {
        case 0:
            // No data => nothing
            return array("", "", "");
        case 1:
            // Single line => Assume it's city+zipcode
            $line = $lines[0];
            return array("", "", $line);
        case 2:
            // Two lines => Assume it's street \n city
            $line1 = $lines[0];
            $line3 = $lines[1];
            return array($line1, "", $line3);
        case 3:
            return $lines;
        default:
            // More than 3 lines => Keep 2 last intact, merge other lines.
            $line3 = array_pop($lines);
            $line2 = array_pop($lines);
            $line1 = implode(" ", $lines);
            return array($line1, $line2, $line3);
        }
    }

    public function apply(PlPage $page)
    {
        if ($this->set instanceof UserSet) {
            $uids = $this->set->getIds(new PlLimit());
            $pids = Profile::getPIDsFromUIDs($uids);
        } else {
            $pids = $this->set->getIds(new PlLimit());
        }
        $visibility = Visibility::defaultForRead(Visibility::VIEW_AX);
        pl_cached_content_headers('text/x-csv', 'iso-8859-1', 1, 'adresses.csv');

        $csv = fopen('php://output', 'w');
        fputcsv($csv,
            array('AX_ID', 'PROMOTION', 'CIVILITE', 'NOM', 'PRENOM', 'SOCIETE', 'ADRESSE', 'ADRESSE1', 'ADRESSE2', 'ADRESSE3', 'CP', 'EMAIL', 'NHABITE_PLUS_A_LADRESSE'),
            ';');

        if (!empty($pids)) {
            $res = XDB::query("SELECT  p.ax_id, pd.promo, p.title,
                                       IF (pn.firstname_ordinary = '', UPPER(pn.firstname_main), UPPER(pn.firstname_ordinary)) AS firstname,
                                       IF (pn.lastname_ordinary = '', UPPER(pn.lastname_main), UPPER(pn.lastname_ordinary)) AS lastname,
                                       UPPER(pje.name), pa.postalText, pa.postal_code_fr AS postal_code, p.email_directory,
                                       IF (FIND_IN_SET('deliveryIssue', pa.flags), 'oui', '') AS delivery_issue
                                 FROM  profile_addresses    AS pa
                           INNER JOIN  profiles             AS p    ON (pa.pid = p.pid)
                           INNER JOIN  profile_display      AS pd   ON (pd.pid = pa.pid)
                           INNER JOIN  profile_public_names AS pn   ON (pn.pid = pa.pid)
                            LEFT JOIN  profile_job          AS pj   ON (pj.pid = pa.pid
                                                                        AND pj.id = IF(pa.type = 'job', pa.id, NULL))
                            LEFT JOIN  profile_job_enum     AS pje  ON (pj.jobid = pje.id)
                                WHERE  pa.pid IN {?} AND FIND_IN_SET('dn_best_mail', pa.flags)", $pids);
            foreach ($res->fetchAllRow() as $item) {
                list($axid, $promo, $title, $lastname, $firstname, $company, $full_address, $zipcode, $email, $delivery_issue) = array_map('utf8_decode', $item);
                $lines = self::split_address($full_address);
                fputcsv($csv, array(
                    $axid, $promo, $title, $lastname, $firstname, $company,
                    $full_address, $lines[0], $lines[1], $lines[2], $zipcode,
                    $email, $delivery_issue), ';');
            }
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
