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

// Interface for an address geocoder. It provides support for transforming a
// free form address into a fully structured one.
abstract class Geocoder {
    // Geocodes @p the address, and returns the corresponding updated address.
    // Unknown key-value pairs available in the input map are retained as-is.
    abstract public function getGeocodedAddress(Address &$address);

    // Cleans the address from its geocoded data
    abstract public function stripGeocodingFromAddress(Address &$address);

    // Updates geoloc_administrativeareas, geoloc_subadministrativeareas and
    // geoloc_localities databases with new geocoded data and returns the
    // corresponding id.
    static public function getAreaId(Address &$address, $area)
    {
        static $databases = array(
            'administrativeArea'    => 'geoloc_administrativeareas',
            'subAdministrativeArea' => 'geoloc_subadministrativeareas',
            'locality'              => 'geoloc_localities',
        );
        static $extras = array(
            'subAdministrativeArea' => array(
                'field' => 'administrativearea',
                'name'  => 'administrativeAreaName')
            )
        );

        $areaName = $area . 'Name';
        $areaNameLocal = $areaName . 'Local';
        $areaId = $area . 'Id';
        if (!is_null($address->$areaName) && isset($databases[$area])) {
            $extra = (isset($extras[$area]) ? $extras[$area]['administrativeAreaName'] : false;

            $res = XDB::query('SELECT  id, nameLocal
                                 FROM  ' . $databases[$area] . '
                                WHERE  name = {?}',
                              $address->$areaName);
            if ($res->numRows() == 0) {
                XDB::execute('INSERT INTO  ' . $databases[$area] . ' (name, nameLocal, country' .
                                           ($extra ? ', ' . $extras[$area]['field'] : '') . ')
                                   VALUES  ({?}, {?}, {?}' . ($extra ? ', {?}' : '') . ')',
                             $address->$areaName, $address->$areaNameLocal, $address->countryId,
                             ($extra ? $address->$extra : null);
                $address->$areaId = XDB::insertId();
            } else {
                // XXX: remove this once all areas have both nameLocal and name.
                list($id, $name) = $res->fetchOneRow();
                if (is_null($name) && !is_null($address->$areaNameLocal)) {
                    XDB::execute('UPDATE  ' . $databases[$area] . '
                                     SET  nameLocal = {?}
                                   WHERE  id = {?}',
                                 $address->$areaNameLocal, $id);
                }
                $address->$areaId = $id;
            }
        } elseif (empty($address->$areaId)) {
            $address->$areaId = null;
        }
    }

    // Returns the part of the text preceeding the line with the postal code
    // and the city name, within the limit of $limit number of lines.
    static public function getFirstLines($text, $postalCode, $limit)
    {
        $textArray  = explode("\n", $text);
        for ($i = 0; $i < count($textArray); ++$i) {
            if ($i > $limit || strpos($textLine, $postalCode) !== false) {
                $limit = $i;
                break;
            }
        }
        return implode("\n", array_slice($textArray, 0, $limit));
    }

    // Returns the number of non geocoded addresses for a user.
    static public function countNonGeocoded($pid, $jobid = null, $type = Address::LINK_PROFILE)
    {
        $where = array();
        if (!is_null($pid)) {
            $where[] = XDB::format('pid = {?}', $pid);
        }
        if (!is_null($jobid)) {
            $where[] = XDB::format('jobid = {?}', $jobid);
        }
        $where[] = XDB::format('FIND_IN_SET({?}, type) AND accuracy = 0', $type);
        $res = XDB::query('SELECT  COUNT(*)
                             FROM  profile_addresses
                            WHERE  ' . implode(' AND ', $where),
                          $pid);
        return $res->fetchOneCell();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
