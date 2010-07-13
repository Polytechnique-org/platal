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

// Interface for an address geocoder. It provides support for transforming a free
// form address into a fully structured one.
// TODO: define and use an Address object instead of a key-value map.
abstract class Geocoder {
    // Geocodes @p the address, and returns the corresponding updated address.
    // Unknown key-value pairs available in the input map are retained as-is.
    abstract public function getGeocodedAddress(array $address);

    // Cleans the address from its geocoded data
    abstract public function stripGeocodingFromAddress(array $address);

    // Updates geoloc_administrativeareas, geoloc_subadministrativeareas and
    // geoloc_localities databases with new geocoded data and returns the
    // corresponding id.
    static public function getAreaId(array &$address, $area)
    {
        static $databases = array(
            'administrativeArea'    => 'geoloc_administrativeareas',
            'subAdministrativeArea' => 'geoloc_subadministrativeareas',
            'locality'              => 'geoloc_localities',
            );

        if (isset($address[$area . 'Name']) && isset($databases[$area])) {
            $res = XDB::query("SELECT  id
                                 FROM  " . $databases[$area] . "
                                WHERE  name = {?}",
                              $address[$area . 'Name']);
            if ($res->numRows() == 0) {
                XDB::execute('INSERT INTO  ' . $databases[$area] . ' (name, country)
                                   VALUES  ({?}, {?})',
                             $address[$area . 'Name'], $address['countryId']);
                $address[$area . 'Id'] = XDB::insertId();
            } else {
                $address[$area . 'Id'] = $res->fetchOneCell();
            }
        }
    }

    // Returns the part of the text preceeding the line with the postal code
    // and the city name, within the limit of $limit number of lines.
    static public function getFirstLines($text, $postalCode, $limit)
    {
        $textArray  = explode("\n", $text);
        for ($i = 0; $i < count($textArray); ++$i) {
            if ($i > $limit || strpos($textLine, $postalCode) !== false) {
                $limit = $i; break;
            }
        }
        return implode("\n", array_slice($textArray, 0, $limit));
    }

    // Returns the number of non geocoded addresses for a user.
    static public function countNonGeocoded($pid)
    {
        $res = XDB::query("SELECT  COUNT(*)
                             FROM  profile_addresses
                            WHERE  pid = {?} AND FIND_IN_SET('home', type) AND accuracy = 0",
                          $pid);
        return $res->fetchOneCell();
    }
}

// Implementation of a Geocoder using the Google Maps API. Please refer to
// the following links for details:
// http://code.google.com/apis/maps/documentation/services.html#Geocoding
// http://code.google.com/intl/en/apis/maps/documentation/geocoding/
// http://code.google.com/apis/maps/documentation/reference.html#GGeoAddressAccuracy
//
// It requires the properties gmaps_key and gmaps_url to be defined in section
// Geocoder in plat/al's configuration (platal.ini & platal.conf).
class GMapsGeocoder extends Geocoder {

    // Maximum number of Geocoding calls to the Google Maps API.
    const MAX_GMAPS_RPC_CALLS = 5;

    public function getGeocodedAddress(array $address) {
        $address = $this->prepareAddress($address);
        $textAddress = $this->getTextToGeocode($address);

        // Try to geocode the full address.
        if (($geocodedData = $this->getPlacemarkForAddress($textAddress))) {
            return $this->getUpdatedAddress($address, $geocodedData, null);
        }

        // If the full geocoding failed, try to geocode only the final part of the address.
        // We start by geocoding everything but the first line, and continue until we get
        // a result. To respect the limit of GMaps calls, we ignore the first few lines
        // if there are too many address lines.
        $addressLines = explode("\n", $textAddress);
        $linesCount   = count($addressLines);
        for ($i = max(1, $linesCount - self::MAX_GMAPS_RPC_CALLS + 1); $i < $linesCount; ++$i) {
            $extraLines = implode("\n", array_slice($addressLines, 0, $i));
            $toGeocode  = implode("\n", array_slice($addressLines, $i));
            if (($geocodedData = $this->getPlacemarkForAddress($toGeocode))) {
                return $this->getUpdatedAddress($address, $geocodedData, $extraLines);
            }
        }

        // No geocoding could be done, the initial address is returned as-is.
        return $address;
    }

    public function stripGeocodingFromAddress(array $address) {
        unset($address['geoloc'], $address['geoloc_choice'], $address['geocodedPostalText'],
              $address['countryId'], $address['country'], $address['administrativeAreaName'],
              $address['subAdministrativeAreaName'], $address['localityName'],
              $address['thoroughfareName'], $address['postalCode']);
        $address['accuracy'] = 0;
        return $address;
    }

    // Updates the address with the geocoded information from Google Maps. Also
    // cleans up the final informations.
    private function getUpdatedAddress(array $address, array $geocodedData, $extraLines) {
        $this->fillAddressWithGeocoding(&$address, $geocodedData);

        // If the accuracy is 6, it means only the street has been gecoded
        // but not the number, thus we need to fix it.
        if ($address['accuracy'] == 6) {
            $this->fixStreetNumber($address);
        }

        // We can now format the address.
        $this->formatAddress($address, $extraLines);

        return $address;
    }

    // Retrieves the Placemark object (see #getPlacemarkFromJson()) for the @p
    // address, by querying the Google Maps API. Returns the array on success,
    // and null otherwise.
    private function getPlacemarkForAddress($address) {
        $url     = $this->getGeocodingUrl($address);
        $geoData = $this->getGeoJsonFromUrl($url);

        return ($geoData ? $this->getPlacemarkFromJson($geoData) : null);
    }

    // Prepares address to be geocoded
    private function prepareAddress($address) {
        $address['text'] = preg_replace('/\s*\n\s*/m', "\n", trim($address['text']));
        $address['postalText'] = $this->getPostalAddress($address['text']);
        $address['updateTime'] = time();
        unset($address['changed']);
        return $address;
    }

    // Builds the Google Maps geocoder url to fetch information about @p address.
    // Returns the built url.
    private function getGeocodingUrl($address) {
        global $globals;

        $parameters = array(
            'key'    => $globals->geocoder->gmaps_key,
            'sensor' => 'false',   // The queried address wasn't obtained from a GPS sensor.
            'hl'     => 'fr',      // Output langage.
            'oe'     => 'utf8',    // Output encoding.
            'output' => 'json',    // Output format.
            'gl'     => 'fr',      // Location preferences (addresses are in France by default).
            'q'      => $address,  // The queries address.
        );

        return $globals->geocoder->gmaps_url . '?' . http_build_query($parameters);
    }

    // Fetches JSON-encoded data from a Google Maps API url, and decode them.
    // Returns the json array on success, and null otherwise.
    private function getGeoJsonFromUrl($url) {
        global $globals;

        // Prepare a backtrace object to log errors.
        $bt = null;
        if ($globals->debug & DEBUG_BT) {
            if (!isset(PlBacktrace::$bt['Geoloc'])) {
                new PlBacktrace('Geoloc');
            }
            $bt = &PlBacktrace::$bt['Geoloc'];
            $bt->start($url);
        }

        // Fetch the geocoding data.
        $rawData = file_get_contents($url);
        if (!$rawData) {
            if ($bt) {
                $bt->stop(0, "Could not retrieve geocoded address from GoogleMaps.");
            }
            return null;
        }

        // Decode the JSON-encoded data, and check for their validity.
        $data = json_decode($rawData, true);
        if ($bt) {
            $bt->stop(count($data), null, $data);
        }

        return $data;
    }

    // Extracts the most appropriate placemark from the JSON data fetched from
    // Google Maps. Returns a Placemark array on success, and null otherwise. See
    // http://code.google.com/apis/maps/documentation/services.html#Geocoding_Structured
    // for details on the Placemark structure.
    private function getPlacemarkFromJson(array $data) {
        // Check for geocoding failures.
        if (!isset($data['Status']['code']) || $data['Status']['code'] != 200) {
            // TODO: handle non-200 codes in a better way, since the code might
            // indicate a temporary error on Google's side.
            return null;
        }

        // Check that at least one placemark was found.
        if (count($data['Placemark']) == 0) {
            return null;
        }

        // Extract the placemark with the best accuracy. This is not always the
        // best result (since the same address may yield two different placemarks).
        $result = $data['Placemark'][0];
        foreach ($data['Placemark'] as $place) {
            if ($place['AddressDetails']['Accuracy'] > $result['AddressDetails']['Accuracy']) {
                $result = $place;
            }
        }

        return $result;
    }

    // Fills the address with the geocoded data
    private function fillAddressWithGeocoding(&$address, $geocodedData) {
        // The geocoded address three is
        // Country -> AdministrativeArea -> SubAdministrativeArea -> Locality -> Thoroughfare
        // with all the possible shortcuts
        // The address is formatted as xAL, or eXtensible Address Language, an international
        // standard for address formatting.
        // xAL documentation: http://www.oasis-open.org/committees/ciq/ciq.html#6
        $address['geoloc'] = str_replace(", ", "\n", $geocodedData['address']);
        if (isset($geocodedData['AddressDetails']['Accuracy'])) {
            $address['accuracy'] = $geocodedData['AddressDetails']['Accuracy'];
        }

        $currentPosition = $geocodedData['AddressDetails'];
        if (isset($currentPosition['Country'])) {
            $currentPosition      = $currentPosition['Country'];
            $address['countryId'] = $currentPosition['CountryNameCode'];
            $address['country']   = $currentPosition['CountryName'];
        }
        if (isset($currentPosition['AdministrativeArea'])) {
            $currentPosition                   = $currentPosition['AdministrativeArea'];
            $address['administrativeAreaName'] = $currentPosition['AdministrativeAreaName'];
        }
        if (isset($currentPosition['SubAdministrativeArea'])) {
            $currentPosition                      = $currentPosition['SubAdministrativeArea'];
            $address['subAdministrativeAreaName'] = $currentPosition['SubAdministrativeAreaName'];
        }
        if (isset($currentPosition['Locality'])) {
            $currentPosition          = $currentPosition['Locality'];
            $address['localityName']  = $currentPosition['LocalityName'];
        }
        if (isset($currentPosition['Thoroughfare'])) {
            $address['thoroughfareName'] = $currentPosition['Thoroughfare']['ThoroughfareName'];
        }
        if (isset($currentPosition['PostalCode'])) {
            $address['postalCode'] = $currentPosition['PostalCode']['PostalCodeNumber'];
        }

        // Gets coordinates.
        if (isset($geocodedData['Point']['coordinates'][0])) {
            $address['latitude'] = $geocodedData['Point']['coordinates'][0];
        }
        if (isset($geocodedData['Point']['coordinates'][1])) {
            $address['longitude'] = $geocodedData['Point']['coordinates'][1];
        }
        if (isset($geocodedData['ExtendedData']['LatLonBox']['north'])) {
            $address['north'] = $geocodedData['ExtendedData']['LatLonBox']['north'];
        }
        if (isset($geocodedData['ExtendedData']['LatLonBox']['south'])) {
            $address['south'] = $geocodedData['ExtendedData']['LatLonBox']['south'];
        }
        if (isset($geocodedData['ExtendedData']['LatLonBox']['east'])) {
            $address['east'] = $geocodedData['ExtendedData']['LatLonBox']['east'];
        }
        if (isset($geocodedData['ExtendedData']['LatLonBox']['west'])) {
            $address['west'] = $geocodedData['ExtendedData']['LatLonBox']['west'];
        }
    }

    // Formats the text of the geocoded address using the unused data and
    // compares it to the given address. If they are too different, the user
    // will be asked to choose between them.
    private function formatAddress(&$address, $extraLines) {
        $same = true;
        if ($extraLines) {
            $address['geoloc'] = $extraLines . "\n" . $address['geoloc'];
        }
        $address['geocodedPostalText'] = $this->getPostalAddress($address['geoloc']);
        $geoloc = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                          array("", "\n"), $address['geoloc']));
        $text   = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                          array("", "\n"), $address['text']));
        $arrayGeoloc = explode("\n", $geoloc);
        $arrayText   = explode("\n", $text);
        $countGeoloc = count($arrayGeoloc);
        $countText   = count($arrayText);

        if (($countText > $countGeoloc) || ($countText < $countGeoloc - 1)
            || (($countText == $countGeoloc - 1)
                && ($arrayText[$countText - 1] == strtoupper($address['country'])))) {
            $same = false;
        } else {
            for ($i = 0; $i < $countGeoloc && $i < $countText; ++$i) {
                if (levenshtein($arrayText[$i], trim($arrayGeoloc[$i])) > 3) {
                    $same = false;
                }
            }
        }
        if ($same) {
            $address['text'] = $address['geoloc'];
            $address['postalText'] = $address['geocodedPostalText'];
            unset($address['geoloc'], $address['geocodedPostalText']);
        } else {
            $address['geoloc'] = str_replace("\n", "\r\n", $address['geoloc']);
            $address['geocodedPostalText'] = str_replace("\n", "\r\n", $address['geocodedPostalText']);
        }
        $address['text'] = str_replace("\n", "\r\n", $address['text']);
        $address['postalText'] = str_replace("\n", "\r\n", $address['postalText']);
    }
 
    // Returns the address formated for postal use.
    // The main rules are (cf AFNOR XPZ 10-011):
    // -everything in upper case;
    // -if there are more then than 38 characters in a lign, split it;
    // -if there are more then than 32 characters in the description of the "street", use abbreviations.
    private function getPostalAddress($text) {
         static $abbreviations = array(
             "IMPASSE"   => "IMP",
             "RUE"       => "R",
             "AVENUE"    => "AV",
             "BOULEVARD" => "BVD",
             "ROUTE"     => "R",
             "STREET"    => "ST",
             "ROAD"      => "RD",
             );

        $text = strtoupper($text);
        $arrayText = explode("\n", $text);
        $postalText = "";

        foreach ($arrayText as $i => $lign) {
            $postalText .= (($i == 0) ? "" : "\n");
            if (($length = strlen($lign)) > 32) {
                $words = explode(" ", $lign);
                $count = 0;
                foreach ($words as $word) {
                    if (isset($abbreviations[$word])) {
                        $word = $abbreviations[$word];
                    }
                    if ($count + ($wordLength = strlen($word)) <= 38) {
                        $postalText .= (($count == 0) ? "" : " ") . $word;
                        $count += (($count == 0) ? 0 : 1) + $wordLength;
                    } else {
                        $postalText .= "\n" . $word;
                        $count = strlen($word);
                    }
                }
            } else {
                $postalText .= $lign;
            }
        }
        return $postalText;
    }

    // Trims the name of the real country if it contains an ISO 3166-1 non-country
    // item. For that purpose, we compare the last but one line of the address with
    // all non-country items of ISO 3166-1.
    private function getTextToGeocode($address)
    {
        $res = XDB::iterator('SELECT  country, countryFR
                                FROM  geoloc_countries
                               WHERE  belongsTo IS NOT NULL');
        $countries = array();
        foreach ($res as $item) {
            $countries[] = $item[0];
            $countries[] = $item[1];
        }
        $textLines  = explode("\n", $address['text']);
        $countLines = count($textLines);
        $needle     = strtoupper(trim($textLines[$countLines - 2]));
        $isPseudoCountry = false;
        foreach ($countries as $country) {
            if (strtoupper($country) == $needle) {
                $isPseudoCountry = true;
                break;
            }
        }

        if ($isPseudoCountry) {
            return implode("\n", array_slice($textLines, 0, -1));
        }
        return $address['text'];
    }

    // Search for the lign from the given address that is the closest to the geocoded thoroughfareName
    // and replaces the corresponding lign in the geocoded text by it.
    static protected function fixStreetNumber(&$address)
    {
        if (isset($address['thoroughfareName'])) {
            $thoroughfareName  = $address['thoroughfareName'];
            $thoroughfareToken = strtoupper(trim(preg_replace(array("/[,\"'#~:;_\-]/", "/\r\n/"),
                                                              array("", "\n"), $thoroughfareName)));
            $geolocLines = explode("\n", $address['geoloc']);
            $textLines   = explode("\n", $address['text']);
            $mindist = strlen($thoroughfareToken);
            $minpos  = 0;
            $pos     = 0;
            foreach ($textLines as $i => $token) {
                if (($l = levenshtein(strtoupper(trim(preg_replace(array("/[,\"'#~:;_\-]/", "/\r\n/"),
                                                                   array("", "\n"), $token))),
                                      $thoroughfareToken)) < $mindist) {
                    $mindist = $l;
                    $minpos  = $i;
                }
            }
            foreach ($geolocLines as $i => $line) {
                if (strtoupper(trim($thoroughfareName)) == strtoupper(trim($line))) {
                    $pos = $i;
                    break;
                }
            }
            $geolocLines[$pos] = $textLines[$minpos];
            $address['geoloc'] = implode("\n", $geolocLines);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
