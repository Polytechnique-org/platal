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
    public $where           = null;  /* Geolocalize ? Addresse ==> google map
                                        Each asso sould have its list of reusable addresses
                                        and UI should list previous (recent) addresses. */
    public $begin           = null;
    public $end             = null;

    public $prices          = null;
    public $useCategories   = null;

    public function __construct(array $data) {
    }
}


/** Class representing a XNetEvent {{{1
 */
class XNetEvent
{
    /* Payment types */
    const PAYMENT_TELEPAYMENT       = 'telepayment';
    const PAYMENT_MONEY             = 'money';

    private $tofetch;

    public $id                      = null;
    public $shortname               = null;
    public $title                   = null;
    public $description             = null;
    public $subscriptionLimit       = null;

    public $closed                  = null;
    public $memberOnly              = null;
    public $invite                  = null;
    public $publicList              = null;
    public $paymentIsSubscription   = null;

    public $categories              = null;
    public $parts                   = null;

    public function __construct($id = null) {
        $this->tofetch = $id;
        $this->id = -1;
        $this->shortname   = null;
        $this->title       = null;
        $this->description = null;
        $this->subscriptionLimit = null;
    }

    private function fetchData() {
        if ($this->id >= 0) {
            return;
        }
        // TODO: fetch data from database
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
