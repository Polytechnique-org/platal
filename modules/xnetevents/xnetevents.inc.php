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

/** Class representing a 'moment' of an event {{{1
 */
class XNetEventPart
{
    public $id              = null;
    public $title           = null;
    public $description     = null;
    public $url             = null;
    public $place           = null;  /* Geolocalize ? Addresse ==> google map
                                        Each asso sould have its list of reusable addresses
                                        and UI should list previous (recent) addresses. */
    public $begin           = null;
    public $end             = null;

    public $prices          = null;
    public $useCategories   = null;

    public function __construct(array $data = null) {
        if (!is_null($data)) {
            foreach ($data as $name => $value) {
                $this->$name = $value;
            }
            $this->prices = explode(',', $this->prices);
        }
    }
}


/** Class representing a XNetEvent {{{1
 */
class XNetEvent
{
    /* Payment types */
    const PAYMENT_TELEPAYMENT       = 'telepayment';
    const PAYMENT_MONEY             = 'money';

    private $tofetch                = null;

    public $id                      = null;

    public $respoUID                = null;
    public $respoForlife            = null;
    public $respoNom                = null;
    public $respoPrenom             = null;
    public $respoPromo              = null;
    public $respoSexe               = null;

    public $shortname               = null;
    public $title                   = null;
    public $description             = null;
    public $subscriptionLimit       = null;

    public $closed                  = null;
    public $prepared                = null;

    public $memberOnly              = null;
    public $invite                  = null;
    public $publicList              = null;
    public $paymentIsSubscription   = null;

    public $categories              = null;
    public $parts                   = null;

    public function __construct($id = null) {
        $this->tofetch = $id;
    }

    private function fetchData() {
        if (!is_null($this->id) || is_null($this->tofetch)) {
            return !is_null($this->id);
        }
        global $globals;
        $it = XDB::query("SELECT  e.id, e.shortname, e.title, e.description,
                                  e.uid AS respoUID, u.prenom AS respoPrenom,
                                  IF(u.nom_usage != '', u.nom_usage, u.nom) AS respoNom,
                                  u.promo AS respoPromo, FIND_IN_SET('femme', u.flags) AS respoSexe,
                                  e.sublimit AS subscriptionLimit, e.categories,
                                  FIND_IN_SET('invite', e.flags) AS invite,
                                  FIND_IN_SET('memberonly', e.flags) AS memberOnly,
                                  FIND_IN_SET('publiclist', e.flags) AS publicList,
                                  FIND_IN_SET('paymentissubscription', e.flags) AS paymentIsSubscription,
                                  e.state IN ('close', 'archive') AS closed,
                                  e.state != 'prepare' AS prepared
                            FROM  groupex.events AS e
                      INNER JOIN  auth_user_md5 AS u ON(e.uid = u.user_id)
                           WHERE  e.asso_id = {?} AND (e.id = {?} OR e.shortname = {?})",
                          $globals->asso('id'), $this->tofetch, $this->tofetch);
        if (!($data = $it->fetchOneAssoc())) {
            return false;
        }
        foreach ($data as $name => $value) {
            $this->$name = $value;
        }
        $this->categories = explode(',', $this->categories);
        $this->parts = array();
        $it = XDB::iterator("SELECT  ep.part_id AS id, ep.title, ep.description,
                                     ep.url, ep.place, ep.begin, ep.end, ep.prices,
                                     NOT FIND_IN_SET('nocategories', ep.flags) AS useCategories
                               FROM  events_part
                              WHERE  ep.event_id = {?}",
                            $this->id);
        while (($data = $it->next())) {
            $this->parts[$data['id']] = new XNetEventPart($data);
        }
        return true;
    }

    private function saveData() {
        // TODO: update database
    }


    // Event edition functions {{{2

    public function setShortname($newname = null) {
        // TODO: do not forget to update partitipants/absents aliases
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setSubscriptionLimit($date) {
        $this->subscriptionLimit = $date;
    }


    // User action events {{{2

    public function subscribe($login, $present, array $moments) {
        if ($this->id >= 0) {
            return false;
        }
        // TODO: do not forget to update participants/absents aliases
    }

    public function payment($login, $value, $method) {
        if ($this->id >= 0) {
            return false;
        }
    }
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
