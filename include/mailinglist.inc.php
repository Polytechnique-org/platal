<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

// {{{ class MailingList

class MailingList
{
    public $address;        // Fully qualified address of the list
    public $mbox;           // mailbox for the list
    public $domain;         // domain for the list
    protected $mmclient;    // The XML-RPC client for Mailman requests

    public function __construct($mbox, $domain, $user=null, $sudo=false)
    {
        global $globals;

        $this->mbox = $mbox;
        $this->domain = $domain;
        $this->address = "$mbox@$domain";

        if (is_null($user)) {
            $user = S::user();
        }

        if ($sudo) {
            // Sudo mode can only be used from crons & co
            $login = $globals->lists->system_login . "+" . $user->id();
            $pass = $globals->lists->system_password;
        } else {
            $login = $user->id();
            $pass = $user->password();
        }

        $this->mmclient = new MMList($login, $pass, $this->domain);
    }

    /** Instantiate a MailingList from its address.
     */
    public static function fromAddress($address, $user=null, $sudo=false)
    {
        if (strstr($address, '@') !== false) {
            list($mbox, $domain) = explode('@', $address);
        } else {
            global $globals;
            $mbox = $address;
            $domain = $globals->mail->domain;
        }
        return new MailingList($mbox, $domain, $user, $sudo);
    }

    /** Retrieve the MailingList associated with a given promo.
     */
    public static function promo($promo, $user=null, $sudo=false)
    {
        global $globals;
        $mail_domain = $globals->mail->domain;
        return new MailingList('promo', "$promo.$mail_domain", $user, $sudo);
    }

    const KIND_BOUNCE = 'bounces';
    const KIND_OWNER = 'owner';
    public function getAddress($kind)
    {
        return $this->mbox . '-' . $kind . '@' . $this->domain;
    }

    /** Subscribe the current user to the list
     */
    public function subscribe()
    {
        return $this->mmclient->subscribe($this->mbox);
    }

    public static function subscribeTo($mbox, $domain, $user=null, $sudo=false)
    {
        $mlist = new MailingList($mbox, $domain, $user, $sudo);
        return $mlist->subscribe();
    }

    public static function subscribePromo($promo, $user=null, $sudo=false)
    {
        $mlist = MailingList::promo($promo, $user, $sudo);
        return $mlist->subscribe();
    }

    /** Subscribe a batch of users to the list
     */
    public function subscribeBulk($members)
    {
        return $this->mmclient->mass_subscribe($this->mbox, $members);
    }

    /** Unsubscribe the current user from the list
     */
    public function unsubscribe()
    {
        return $this->mmclient->unsubscribe($this->mbox);
    }

    /** Unsubscribe a batch of users from the list
     */
    public function unsubscribeBulk($members)
    {
        return $this->mmclient->mass_unsubscribe($this->mbox, $members);
    }

    /** Retrieve owners for the list.
     *
     * TODO: document the return type
     */
    public function getOwners()
    {
        return $this->mmclient->get_owners($this->mbox);
    }

    /** Add an owner to the list
     */
    public function addOwner($email)
    {
        return $this->mmclient->add_owner($this->mbox, $email);
    }

    /** Remove an owner from the list
     */
    public function removeOwner($email)
    {
        return $this->mmclient->del_owner($this->mbox, $email);
    }

    /** Retrieve members of the list.
     *
     * TODO: document the return type
     */
    public function getMembers()
    {
        return $this->mmclient->get_members($this->mbox);
    }

    /** Retrieve a subset of list members.
     *
     * TODO: document the return type
     */
    public function getMembersLimit($page, $number_per_page)
    {
        return $this->mmclient->get_members_limit($this->mbox, $page, $number_per_page);
    }

    const SUB_ENOACCESS = -1;
    const SUB_NOTSUBSCRIBED = 0;
    const SUB_PENDING = 1;
    const SUB_SUBSCRIBED = 2;

    /** Check the subscription status of the user
     */
    public function subscriptionState()
    {
        return $this->mmclient->get_subscription_status($this->mbox);
    }

    /** Fetch pending list operations.
     *
     * TODO: document the return type
     */
    public function getPendingOps()
    {
        return $this->mmclient->get_pending_ops($this->mbox);
    }

    const REQ_ACCEPT = 1;
    const REQ_REJECT = 2;
    const REQ_DISCARD = 3;
    const REQ_SUBSCRIBE = 4;

    /** Handle a mailing list request
     */
    public function handleRequest($kind, $value, $comment='')
    {
        return $this->mmclient->handle_request($this->mbox, $value, $kind,
           utf8_decode($comment));
    }

    /** Retrieve the current status of a pending subscription request
     */
    public function getPendingSubscription($email)
    {
        return $this->mmclient->get_pending_sub($this->mbox, $email);
    }

    /** Retrieve pending mails
     */
    public function getPendingMail($mid)
    {
        return $this->mmclient->get_pending_mail($this->mbox, $mid);
    }

    /** Create a list
     */
    public static function create($mbox, $domain, $user, $description,
        $advertise, $moderation_level, $subscription_level,
        $owners, $members, $sudo=false)
    {
        $mlist = new MailingList($mbox, $domain, $user, $sudo);
        return $mlist->mmclient->create_list($mlist->mbox, utf8_decode($description),
            $advertise, $moderation_level, $subscription_level,
            $owners, $members);
    }

    /** Delete a list
     */
    public function delete($remove_archives=false)
    {
        return $this->mmclient->delete_list($this->mbox, $remove_archives);
    }

    /** Set antispam level.
     */
    public function setBogoLevel($level)
    {
        return $this->mmclient->set_bogo_level($this->mbox, $level);
    }

    /** Get antispam level.
     *
     * @return int
     */
    public function getBogoLevel()
    {
        $bogo = $this->mmclient->get_bogo_level($this->mbox);
        return $bogo;
    }

    /** Set public options.
     *
     * @param $options array
     */
    public function setOwnerOptions($options)
    {
        return $this->mmclient->set_owner_options($this->mbox, $options);
    }

    /** Retrieve owner options
     *
     * @return array
     */
    public function getOwnerOptions()
    {
        return $this->mmclient->get_owner_options($this->mbox);
    }

    /** Set admin options.
     *
     * @param $options array
     */
    public function setAdminOptions($options)
    {
        return $this->mmclient->set_admin_options($this->mbox, $options);
    }

    /** Retrieve admin options
     *
     * @return array
     */
    public function getAdminOptions()
    {
        return $this->mmclient->get_admin_options($this->mbox);
    }

    /** Check options, optionnally fixing them.
     */
    public function checkOptions($fix=false)
    {
        return $this->mmclient->check_options($this->mbox, $fix);
    }

    /** Add an email to the list of whitelisted senders
     */
    public function whitelistAdd($email)
    {
        return $this->mmclient->add_to_wl($this->mbox, $email);
    }

    /** Remove an email from the list of whitelisted senders
     */
    public function whitelistRemove($email)
    {
        return $this->mmclient->del_from_wl($this->mbox, $email);
    }
}

// }}}

// vim:set et sw=4 sts=4 sws=4 fenc=utf-8:
?>
