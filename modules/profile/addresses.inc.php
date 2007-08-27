<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

class ProfileAddress extends ProfileNoSave
{
    private function geolocAddress(&$address, &$success)
    {
        require_once 'geoloc.inc.php';
        if (@$address['parsevalid'] || (@$address['text'] && @$address['changed']) || !@$address['cityid']) {
            $address = array_merge($address, empty_address());
            $new = get_address_infos(@$address['text']);
            if (compare_addresses_text(@$adress['text'], $geotxt = get_address_text($new))
                || @$address['parsevalid']) {
                $address = array_merge($address, $new);
            } else {
                $success = false;
                $address = array_merge($address, cut_address(@$address['text']));
                $address['geoloc'] = $geotxt;
                $address['geoloc_cityid'] = $new['cityid'];
            }
        }
        $address['text'] = get_address_text($address);
        unset($address['parsevalid']);
        unset($address['changed']);
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $init = false;
        if (is_null($value)) {
            $value = $page->values['addresses'];
            $init = true;
        }
        foreach ($value as $key=>&$adr) {
            if (@$adr['removed']) {
                unset($value[$key]);
            }
        }
        $success = true;
        foreach ($value as $key=>&$adr) {
            $this->geolocAddress($adr, $s);
            if (!$init) {
                $success = $success && $s;
            }
        }
        return $value;
    }
}

class ProfileAddresses extends ProfilePage
{
    protected $pg_template = 'profile/adresses.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['addresses'] = new ProfileAddress();
    }

    protected function fetchData()
    {
        if (count($this->orig) > 0) {
            $this->values = $this->orig;
            return;
        }
        // Build the addresses tree
        $res = XDB::query("SELECT  a.adrid AS id, a.adr1, a.adr2, a.adr3,
                                   a.postcode, a.city, a.cityid, a.region, a.regiontxt,
                                   a.fax, a.glat, a.glng, a.datemaj, a.pub,
                                   a.country, gp.pays AS countrytxt, gp.display,
                                   FIND_IN_SET('res-secondaire', a.statut) AS secondaire,
                                   FIND_IN_SET('courrier', a.statut) AS mail,
                                   FIND_IN_SET('temporary', a.statut) AS temporary,
                                   FIND_IN_SET('active', a.statut) AS current
                             FROM  adresses AS a
                       INNER JOIN geoloc_pays AS gp ON(gp.a2 = a.country)
                            WHERE  uid = {?} AND NOT FIND_IN_SET('pro', statut)
                         ORDER BY  adrid",
                           S::i('uid'));
        $this->values['addresses'] = $res->fetchAllAssoc();

        $res = XDB::iterator("SELECT  adrid, tel_type AS type, tel_pub AS pub, tel
                                FROM  tels
                               WHERE  uid = {?}
                            ORDER BY  adrid",
                             S::i('uid'));
        $i = 0;
        while ($tel = $res->next()) {
            $adrid = $tel['adrid'];
            unset($tel['adrid']);
            while ($this->values['addresses'][$i]['id'] < $adrid) {
                $i++;
            }
            $address =& $this->values['addresses'][$i];
            if (!isset($address['tel'])) {
                $address['tel'] = array();
            }
            if ($address['id'] == $adrid) {
                $address['tel'][] = $tel;
            }
        }
        foreach ($this->values['addresses'] as $id=>&$address) {
            unset($address['id']);
        }
        parent::fetchData();
    }

    protected function saveData()
    {
        parent::saveData();
    }

    public function prepare(PlatalPage &$page)
    {
        parent::prepare($page);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
