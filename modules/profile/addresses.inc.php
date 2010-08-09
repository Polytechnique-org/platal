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

class ProfileSettingAddress extends ProfileSettingGeocoding
{
    private $bool;
    private $pub;

    public function __construct()
    {
        $this->bool = new ProfileSettingBool();
        $this->pub  = new ProfileSettingPub();
    }

    private function cleanAddress(ProfilePage &$page, $addrid, array &$address)
    {
        $address['tel']       = Phone::formatFormArray($address['tel'], $s);
        $address['current']   = $this->bool->value($page, 'current',   $address['current'],   $s);
        $address['temporary'] = $this->bool->value($page, 'temporary', $address['temporary'], $s);
        $address['secondary'] = $this->bool->value($page, 'secondary', $address['secondary'], $s);
        $address['mail']      = $this->bool->value($page, 'mail',      $address['mail'],      $s);
        $address['pub']       = $this->pub->value($page,  'pub',       $address['pub'],       $s);
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $init = false;
        if (is_null($value)) {
            $value = $page->values['addresses'];
            $init  = true;
        }
        foreach ($value as $key => &$address) {
            if (isset($address['removed']) && $address['removed']) {
                array_splice($value, $key, 1);
            }
        }
        $current = 0;
        $success = true;
        foreach ($value as $key => &$address) {
            if (isset($address['current']) && $address['current']) {
                $current++;
            }
        }
        if ($current == 0 && count($value) > 0) {
            foreach ($value as &$address) {
                $address['current'] = true;
                break;
            }
        } elseif ($current > 1) {
            $success = false;
        }
        foreach ($value as $key => &$address) {
            if (!trim($address['text'])) {
                unset($value[$key]);
            } elseif (!$init) {
                $this->geocodeAddress($address, $s);
                $success = $success && $s;
            }
            $this->cleanAddress($page, $key, $address);
        }
        return $value;
    }

    public function saveAddress($pid, $addrid, array &$address, $type)
    {
        require_once 'geocoding.inc.php';

        $flags = new PlFlagSet();
        $flags->addFlag('current', $address['current']);
        $flags->addFlag('temporary', $address['temporary']);
        $flags->addFlag('secondary', $address['secondary']);
        $flags->addFlag('mail', $address['mail']);
        $flags->addFlag('cedex', $address['cedex'] =
            (strpos(strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                            array("", "\n"), $address['text'])), 'CEDEX')) !== false);
        Geocoder::getAreaId($address, "administrativeArea");
        Geocoder::getAreaId($address, "subAdministrativeArea");
        Geocoder::getAreaId($address, "locality");
        XDB::execute("INSERT INTO  profile_addresses (pid, type, id, flags, accuracy,
                                                      text, postalText, postalCode, localityId,
                                                      subAdministrativeAreaId, administrativeAreaId,
                                                      countryId, latitude, longitude, updateTime, pub, comment,
                                                      north, south, east, west)
                           VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?},
                                    {?}, {?}, FROM_UNIXTIME({?}), {?}, {?}, {?}, {?}, {?}, {?})",
                     $pid, $type, $addrid, $flags, $address['accuracy'],
                     $address['text'], $address['postalText'], $address['postalCode'], $address['localityId'],
                     $address['subAdministrativeAreaId'], $address['administrativeAreaId'],
                     $address['countryId'], $address['latitude'], $address['longitude'],
                     $address['updateTime'], $address['pub'], $address['comment'],
                     $address['north'], $address['south'], $address['east'], $address['west']);
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        XDB::execute("DELETE FROM  profile_addresses
                            WHERE  pid = {?} AND type = 'home'",
                     $page->pid());
        Phone::deletePhones($page->pid(), Phone::LINK_ADDRESS);
        foreach ($value as $addrid => &$address) {
            $this->saveAddress($page->pid(), $addrid, $address, 'home');
            Phone::savePhones($address['tel'], $page->pid(), Phone::LINK_ADDRESS, $addrid);
        }
    }

    public function getText($value) {
        $addresses = array();
        foreach ($value as $addrid => $address) {
            $phones = Phone::formArrayToString($address['tel']);
            $addresses[] = 'Adresse : ' . $address['text'] . ', affichage : ' . $address['pub']
                         . ', commentaire : ' . $address['comment'] . ', actuelle : ' . ($address['current'] ? 'oui' : 'non')
                         . ', temporaire : ' . ($address['temporary'] ? 'oui' : 'non') . ', secondaire : '
                         . ($address['secondary'] ? 'oui' : 'non') . ', conctactable par courier : '
                         . ($address['mail'] ? 'oui' : 'non') . ($phones ? ', ' . $phones : '');
        }
        return implode(' ; ' , $addresses);
    }
}

class ProfileSettingAddresses extends ProfilePage
{
    protected $pg_template = 'profile/adresses.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['addresses'] = new ProfileSettingAddress();
        $this->watched['addresses']  = true;
    }

    protected function _fetchData()
    {
        $res = XDB::query("SELECT  id, accuracy, text, postalText,
                                   postalCode, localityId, subAdministrativeAreaId, administrativeAreaId,
                                   countryId, latitude, longitude, pub, comment, UNIX_TIMESTAMP(updateTime) AS updateTime,
                                   north, south, east, west,
                                   FIND_IN_SET('current', flags) AS current,
                                   FIND_IN_SET('temporary', flags) AS temporary,
                                   FIND_IN_SET('secondary', flags) AS secondary,
                                   FIND_IN_SET('mail', flags) AS mail,
                                   FIND_IN_SET('cedex', flags) AS cedex
                             FROM  profile_addresses
                            WHERE  pid = {?} AND type = 'home'
                         ORDER BY  id",
                           $this->pid());
        if ($res->numRows() == 0) {
            $this->values['addresses'] = array();
        } else {
            $this->values['addresses'] = $res->fetchAllAssoc();
        }

        // Adds phones to addresses.
        $it = Phone::iterate(array($this->pid()), array(Phone::LINK_ADDRESS));
        while ($phone = $it->next()) {
            $this->values['addresses'][$phone->linkId()]['tel'][$phone->id()] = $phone->toFormArray();
        }

        // Properly formats addresses.
        foreach ($this->values['addresses'] as $id => &$address) {
            $phone = new Phone();
            if (!isset($address['tel'])) {
                $address['tel'] = array(0 => $phone->toFormArray());
            }
            unset($address['id']);
            $address['changed'] = 0;
            $address['removed'] = 0;
        }
        //var_dump($this->values['addresses']['tel']);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
