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
    private $changed        = false;

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
        } else {
            $this->changed = true;
        }
    }

    public function save($event) {
        if ($this->changed) {
            XDB::execute("REPLACE INTO  groupex.events_part (event_id, part_id, title, description,
                                        url, place, begin, end, prices, flags)
                                VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})",
                        $event, $this->id, $this->title, $this->description, $this->url, $this->place,
                        $this->begin, $this->end, implode(',', $this->prices),
                        $this->useCategories ? '' : 'nocategories');
            // TODO: update corresponding subscriptions !!!
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
    private $changed                = false;
    private $partCount              = 0;

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
        $this->changed = is_null($id);
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
        $this->partsCount = count($this->parts);
        return true;
    }

    private function saveData() {
        $cats = implode(',', $this->categories);
        $dogs = array(); // Noting to do with animals, but previous variable was 'cats'
        if ($this->memberOnly) {
            $dogs[] = 'memberonly';
        }
        if ($this->invite) {
            $dogs[] = 'invite';
        }
        if ($this->publicList) {
            $dogs[] = 'publicList';
        }
        if ($this->paymentIsSubscription) {
            $dogs[] = 'paymentissubscription';
        }
        $dogs = implode(',', $dogs);
        global $globals, $page;
        if (is_null($this->id)) {
            if (!$this->bootstrapEventLists()) {
                return false;
            }
            if (!XDB::execute("INSERT INTO  groupex.events
                                           (asso_id, respo_uid, shortname, title, description,
                                            sublimit, categories, flags, state)
                                   VALUES  ({?}, {?}. {?}, {?}, {?}, {?}, {?}, {?}, 'prepare')",
                             $globals->asso('id'), $this->respoUID, $this->shortname, $this->title,
                             $this->description, $this->subscriptionLimit, $cats, $dogs)) {
                $page->trig("Impossible de créer l'événement");
                return false;
            }
            $this->id = XDB::insertId();
        } else if ($this->changed) {
            if (!XDB::execute("UPDATE  groupex.events
                                  SET  shortname = {?}, title = {?}, description = {?}, sublimit = {?},
                                       categories = {?}, flags = {?}
                                WHERE  id = {?} AND asso_id = {?}",
                              $this->shortname, $this->title, $this->description, $this->sublimit,
                              $cats, $dogs, $this->id, $globals->asso('id'))) {
                $page->trig("Impossible de mettre à jour l'événement");
                return false;
            }
        }
        foreach ($this->parts as &$part) {
            $part->save($this->id);
        }
        if ($this->partsCount > count($this->parts)) {
            XDB::execute("DELETE FROM  groupex.events_part
                                WHERE  event_id = {?} AND asso_id = {?} AND part_id > {?}",
                         $this->id, $globals->asso('id'), max(array_keys($this->parts)));
        }
        return true;
    }


    // Event edition functions {{{2

    public function setShortname($newname) {
        if (!$this->renameEventLists($this->shortname, $newname)) {
            return false;
        }
        $this->shortname = $newname;
        $this->changed = true;
        return true;
    }

    public function setTitle($title) {
        $this->title = $title;
        $this->changed = true;
        return true;
    }

    public function setDescription($description) {
        $this->description = $description;
        $this->changed = true;
        return true;
    }

    public function setSubscriptionLimit($date) {
        $this->subscriptionLimit = $date;
        $this->changed = true;
        return true;
    }


    // (Non-)Subscriber aliases management {{{2

    /** Build 2 aliases that can be used to contact the member of the group:
     *    - shortname-participants groups all subscribers to the event
     *    - shortname-absents groups all users that has not yet subscribed.
     * Users that tell they won't come to the event are on none of the two lists.
     *
     * This function MUST succeed in order to add an event to the databse.
     */
    private function bootstrapEventLists() {
        global $globals, $page;
        $participants = -1;
        $absents      = -1;
        if (!XDB::execute("INSERT INTO  virtual (alias, type)
                                VALUES  ({?}, 'evt')",
                           $this->shortname . '-participants@' . $globals->xnet->evts_domain)) {
            $page->trig("Le nom de l'événement est déjà utilisé, merci d'en choisir un autre.");
            return false;
        }
        $participants = XDB::insertId();
        // Don't know why this can fail, but be sure...
        if (!XDB::execute("INSERT INTO  virtual (alias, type)
                                VALUES  ({?}, 'evt')",
                          $this->shortname . '-absents@' . $globals->xnet->evts_domain)) {
            XDB::execute("DELETE FROM  virtual
                                WHERE  id = {?}",
                        $participants);
            $page->trig("Une erreur s'est produite lors de la création de l'événement");
            return false;
        }
        $absents = XDB::insertId();

        // Add all members of the group to the "absents" list
        XDB::execute("INSERT INTO  virtual_redirect
                           SELECT  {?} AS vid,
                                   IF(a.alias IS NOT NULL, CONCAT(a.alias, '@', {?}), m.email) AS redirect
                             FROM  groupex.membres AS m
                        LEFT JOIN  aliases AS a ON (m.uid = a.id AND a.type = 'a_vie')
                            WHERE asso_id = {?}",
                     $absents, strlen($globals->mail->domain) < strlen($globals->mail->domain2) ? $globals->mail->domain
                                                                                                : $globals->mail->domain2,
                     $globals->asso('id'));
        return true;
    }

    /** Change the name of the aliases used to send informations to members.
     * Success is a prerequist to any shortname change.
     */
    private function renameEventLists($oldname, $newname) {
        if (strtolower($oldname) == strtolower($newname)) {
            return true;
        }
        global $globals, $page;
        if (!XDB::execute("UPDATE  virtual
                              SET  alias = {?}
                            WHERE  alias = {?}",
                          $newname . '-absents@' . $globals->xnet->evts_domain,
                          $oldname . '-absents@' . $globals->xnet->evts_domain)) {
            $page->trig("Impossible d'utiliser $newname comme nom, celui-ci est déjà utilisé");
            return false;
        }
        // Don't know why this could fail
        if (!XDB::execute("UPDATE  virtual
                              SET  alias = {?}
                            WHERE  alias = {?}",
                          $newname . '-participants@' . $globals->xnet->evts_domain,
                          $oldname . '-participants@' . $globals->xnet->evts_domain)) {
            XDB::execute("UPDATE  virtual
                             SET  alias = {?}
                           WHERE  alias = {?}",
                          $oldname . '-absents@' . $globals->xnet->evts_domain,
                          $newname . '-absents@' . $globals->xnet->evts_domain);
            $page->trig("Une erreur s'est produite lors du renommage de $oldname");
            return false;
        }
        return true;
    }


    // User action events {{{2

    public function subscribe($login, $present, array $moments) {
        if ($this->id >= 0) {
            return false;
        }
        // TODO: do not forget to update participants/absents aliases
    }

    public function canSubscribe() {
        global $globals;
        return $this->nonmembre || $globals->perms->hasFlag('groupmember');
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
