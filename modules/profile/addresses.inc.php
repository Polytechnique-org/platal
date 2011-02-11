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

class ProfileSettingAddresses implements ProfileSetting
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        $addresses = array();

        if (is_null($value)) {
            $it = Address::iterate(array($page->pid()), array(Address::LINK_PROFILE), array(0));
            while ($address = $it->next()) {
                $addresses[] = $address->toFormArray();
            }
            if (count($addresses) == 0) {
                $address = new Address();
                $addresses[] = $address->toFormArray();
            }
            return $addresses;
        }

        return Address::formatFormArray($value, $success);
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        $deletePrivate = S::user()->isMe($page->owner) || S::admin();

        Phone::deletePhones($page->pid(), Phone::LINK_ADDRESS, null, $deletePrivate);
        Address::deleteAddresses($page->pid(), Address::LINK_PROFILE, null, $deletePrivate);
        Address::saveFromArray($value, $page->pid(), Address::LINK_PROFILE, null, $deletePrivate);
        if (S::user()->isMe($page->owner) && count($value) > 1) {
            Platal::page()->trigWarning('Attention, tu as plusieurs adresses sur ton profil. Pense à supprimer celles qui sont obsolètes.');
        }
    }

    public function getText($value)
    {
        return Address::formArrayToString($value);
    }
}

class ProfilePageAddresses extends ProfilePage
{
    protected $pg_template = 'profile/adresses.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['addresses'] = new ProfileSettingAddresses();
        $this->watched['addresses']  = true;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
