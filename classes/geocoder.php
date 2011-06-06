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

// Interface for an address geocoder. It provides support for transforming a
// free form address into a fully structured one.
abstract class Geocoder {
    // Geocodes @p the address, and returns the corresponding updated address.
    // Unknown key-value pairs available in the input map are retained as-is.
    abstract public function getGeocodedAddress(Address $address);

    // Cleans the address from its geocoded data
    abstract public function stripGeocodingFromAddress(Address $address);

    // Updates profile_addresses_components_enum, if needed, with new
    // geocoded data and returns the corresponding id.
    static public function getComponentId(array $component)
    {
        $where = '';
        foreach ($component['types'] as $type) {
            $where .= XDB::format(' AND FIND_IN_SET({?}, types)', $type);
        }

        $id = XDB::fetchOneCell('SELECT  id
                                   FROM  profile_addresses_components_enum
                                  WHERE  short_name = {?} AND long_name = {?}' . $where,
                                $component['short_name'], $component['long_name']);
        if (is_null($id)) {
            XDB::execute('INSERT INTO  profile_addresses_components_enum (short_name, long_name, types)
                               VALUES  ({?}, {?}, {?})',
                         $component['short_name'], $component['long_name'], implode(',', $component['types']));
            $id = XDB::insertId();
        }
        return $id;
    }

    // Returns the part of the text preceeding the line with the postal code
    // and the city name, within the limit of $limit number of lines.
    static public function getFirstLines($text, $postalCode, $limit)
    {
        $text = str_replace("\r", '', $text);
        $textArray = explode("\n", $text);
        $linesNb = $limit;

        for ($i = 0; $i < count($textArray); ++$i) {
            if ($i > $limit || strpos($textArray[$i], $postalCode) !== false) {
                $linesNb = $i;
                break;
            }
        }
        $firstLines = implode("\n", array_slice($textArray, 0, $linesNb));

        // Adds empty lines to complete the $limit lines required.
        for (; $i < $limit; ++$i) {
            $firstLines .= "\n";
        }
        return $firstLines;
    }

    // Returns the number of non geocoded addresses for a profile.
    static public function countNonGeocoded($pid)
    {
        $count = XDB::fetchOneCell('SELECT  COUNT(*)
                                      FROM  profile_addresses AS pa
                                     WHERE  pid = {?} AND type = \'home\'
                                            AND NOT EXISTS (SELECT  *
                                                              FROM  profile_addresses_components AS pc
                                                             WHERE  pa.pid = pc.pid AND pa.jobid = pc.jobid AND pa.groupid = pc.groupid
                                                                    AND pa.type = pc.type AND pa.id = pc.id)',
                                   $pid);
        return $count;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
