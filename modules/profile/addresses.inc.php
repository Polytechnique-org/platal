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
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            return $page->values['addresses'];
        }
        foreach ($value as $key=>&$adr) {
            if ($adr['removed']) {
                unset($value[$key]);
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
        $res = XDB::query("SELECT  adrid AS id, adr1, adr2, adr3,
                                   postcode, city, cityid, region, regiontxt,
                                   fax, glat, glng, datemaj, pub,
                                   FIND_IN_SET('res-secondaire', statut) AS secondaire,
                                   FIND_IN_SET('courrier', statut) AS mail,
                                   FIND_IN_SET('temporary', statut) AS temporary,
                                   FIND_IN_SET('active', statut) AS current,
                                   FIND_IN_SET('coord-checked', statut) AS checked,
                                   FIND_IN_SET('coord-valid', statut) AS valid
                             FROM  adresses
                            WHERE  uid = {?}
                         ORDER BY  adrid",
                           S::i('uid'));
        $this->values['addresses'] = $res->fetchAllAssoc();

        $res = XDB::iterRow("SELECT  adrid, telid, tel_type, tel_pub, tel
                               FROM  tels
                              WHERE  uid = {?}
                           ORDER BY  adrid",
                            S::i('uid'));
        $i = 0;
        while (list($adrid, $telid, $type, $pub, $tel) = $res->next()) {
            while ($this->values['addresses'][$i]['id'] < $adrid) {
                $i++;
            }
            $address =& $this->values['addresses'][$i];
            if (!isset($address['tel'])) {
                $address['tel'] = array();
            }
            if ($address['id'] == $adrid) {
                $address['tel'][] = array('id'   => $telid,
                                          'type' => $type,
                                          'pub'  => $pub,
                                          'tel'  => $tel);
            }
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
