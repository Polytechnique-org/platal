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

require_once('user.func.inc.php');

class VCard extends PlVCard
{
    private $user_list = array();
    private $count     = 0;
    private $freetext  = null;
    private $photos    = true;

    public function __construct($photos = true, $freetext = null)
    {
        PlVCard::$folding = false;
        $this->freetext = $freetext;
        $this->photos   = $photos;
    }

    public function addUser($user)
    {
        $forlife = get_user_forlife($user, '_silent_user_callback');
        if ($forlife) {
            $this->user_list[] = get_user_forlife($user);
            $this->count++;
        }
    }

    public function addUsers(array $users) {
        foreach ($users as $user) {
            $this->addUser($user);
        }
    }

    protected function fetch()
    {
        return PlIteratorUtils::fromArray($this->user_list);
    }

    protected function buildEntry($entry)
    {
        global $globals;
        $login = $entry['value'];
        $user  = get_user_details($login);

        if (empty($user['nom_usage'])) {
            $entry = new PlVCardEntry($user['prenom'], $user['nom'], null, null, @$user['nickname']);
        } else {
            $entry = new PlVCardEntry($user['prenom'], array($user['nom'], $user['nom_usage']), null, null, @$user['nickname']);
        }

        // Free text
        $freetext = '(' . $user['promo'] . ')';
        if ($this->freetext) {
            $freetext .= "\n" . $this->freetext;
        }
        if (strlen(trim($user['freetext']))) {
            $freetext .= "\n" . MiniWiki::WikiToText($user['freetext']);
        }
        $entry->set('NOTE', $freetext);

        // Mobile
        if (!empty($user['mobile'])) {
            $entry->addTel(null, $user['mobile'], false, true, true, false, true, true);
        }

        // Emails
        $entry->addMail(null, $user['bestalias'] . '@' . $globals->mail->domain, true);
        $entry->addMail(null, $user['bestalias'] . '@' . $globals->mail->domain2);
        if ($user['bestalias'] != $user['forlife']) {
            $entry->addMail(null, $user['forlife'] . '@' . $globals->mail->domain);
            $entry->addMail(null, $user['forlife'] . '@' . $globals->mail->domain2);
        }

        // Homes
        foreach ($user['adr'] as $adr) {
            $street = array($adr['adr1']);
            if (!empty($adr['adr2'])) {
                $street[] = $adr['adr2'];
            }
            if (!empty($adr['adr3'])) {
                $street[] = $adr['adr3'];
            }
            $group = $entry->addHome($street, null, null, $adr['postcode'], $adr['city'], $adr['region'], @$adr['country'],
                                     $adr['active'], $adr['courier'], $adr['courier']);
            if (!empty($adr['tels'])) {
                foreach ($adr['tels'] as $tel) {
                    $fax = $tel['tel_type'] == 'Fax';
                    $entry->addTel($group, $tel['tel'], $fax, !$fax, !$fax, false, false, !$fax && $adr['active'] && empty($user['mobile']));
                }
            }
        }

        // Pro
        foreach ($user['adr_pro'] as $pro) {
            $street = array($adr['adr1']);
            if (!empty($pro['adr2'])) {
                $street[] = $pro['adr2'];
            }
            if (!empty($pro['adr3'])) {
                $street[] = $pro['adr3'];
            }
            $group = $entry->addWork($pro['entreprise'], null, $pro['poste'], $pro['fonction'],
                                     $street, null, null, $pro['postcode'], $pro['city'], $pro['region'], @$pro['country']);
            if (!empty($pro['tel'])) {
                $entry->addTel($group, $pro['tel']);
            }
            if (!empty($pro['fax'])) {
                $entry->addTel($group, $pro['fax'], true);
            }
            if (!empty($pro['email'])) {
                $entry->addMail($group, $pro['email']);
            }
        }

        // Melix
        $res = XDB::query(
                "SELECT alias
                   FROM virtual
             INNER JOIN virtual_redirect USING(vid)
             INNER JOIN auth_user_quick  ON ( user_id = {?} AND emails_alias_pub = 'public' )
                  WHERE ( redirect={?} OR redirect={?} )
                        AND alias LIKE '%@{$globals->mail->alias_dom}'",
                $user['user_id'],
                $user['forlife'].'@'.$globals->mail->domain,
                $user['forlife'].'@'.$globals->mail->domain2);
        if ($res->numRows()) {
            $entry->addMail(null, $res->fetchOneCell());
        }

        // Custom fields
        if (count($user['gpxs_name'])) {
            $entry->set('X-GROUPS', join(', ', $user['gpxs_name']));
        }
        if (count($user['binets'])) {
            $entry->set('X-BINETS', join(', ', $user['binets']));
        }
        if (!empty($user['section'])) {
            $entry->set('X-SECTION', $user['section']);
        }

        // Photo
        if ($this->photos) {
            $res = XDB::query(
                    "SELECT attach, attachmime
                       FROM photo   AS p
                 INNER JOIN aliases AS a ON (a.id = p.uid AND a.type = 'a_vie')
                      WHERE a.alias = {?}", $login);
            if ($res->numRows()) {
                list($data, $type) = $res->fetchOneRow();
                $entry->setPhoto($data, strtoupper($type));
            }
        }
        return $entry;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
