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
    // Maximum levenshtein distance authorized between input and geocoded text in a single line.
    const MAX_LINE_DISTANCE = 5;
    // Maximum levenshtein distance authorized between input and geocoded text in the whole text.
    const MAX_TOTAL_DISTANCE = 6;

    public function getGeocodedAddress(Address &$address) {
        $this->prepareAddress($address);
        $textAddress = $this->getTextToGeocode($address->text);

        // Try to geocode the full address.
        if (($geocodedData = $this->getPlacemarkForAddress($textAddress))) {
            $this->getUpdatedAddress($address, $geocodedData, null);
            return;
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
                $this->getUpdatedAddress($address, $geocodedData, $extraLines);
                return;
            }
        }
    }

    public function stripGeocodingFromAddress(Address &$address) {
        $address->geocodedText = null;
        $address->geocodedPostalText = null;
        $address->geoloc_choice = null;
        $address->countryId = null;
        $address->country = null;
        $address->administrativeAreaName = null;
        $address->subAdministrativeAreaName = null;
        $address->localityName = null;
        $address->thoroughfareName = null;
        $address->postalCode = null;
        $address->accuracy = 0;
    }

    // Updates the address with the geocoded information from Google Maps. Also
    // cleans up the final informations.
    private function getUpdatedAddress(Address &$address, array $geocodedData, $extraLines) {
        $this->fillAddressWithGeocoding($address, $geocodedData);
        $this->formatAddress($address, $extraLines);
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
    private function prepareAddress(Address &$address) {
        $address->text = preg_replace('/\s*\n\s*/m', "\n", trim($address->text));
        $address->postalText = $this->getPostalAddress($address->text);
    }

    // Builds the Google Maps geocoder url to fetch information about @p address.
    // Returns the built url.
    private function getGeocodingUrl($address) {
        global $globals;

        $parameters = array(
            'key'    => $globals->geocoder->gmaps_key,
            'sensor' => 'false',   // The queried address wasn't obtained from a GPS sensor.
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
                $bt->stop(0, 'Could not retrieve geocoded address from GoogleMaps.');
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
    private function fillAddressWithGeocoding(Address &$address, $geocodedData) {
        // The geocoded address three is
        // Country -> AdministrativeArea -> SubAdministrativeArea -> Locality -> Thoroughfare
        // with all the possible shortcuts
        // The address is formatted as xAL, or eXtensible Address Language, an international
        // standard for address formatting.
        // xAL documentation: http://www.oasis-open.org/committees/ciq/ciq.html#6
        $address->geocodedText = str_replace(', ', "\n", $geocodedData['address']);
        if (isset($geocodedData['AddressDetails']['Accuracy'])) {
            $address->accuracy = $geocodedData['AddressDetails']['Accuracy'];
        }

        $currentPosition = $geocodedData['AddressDetails'];
        if (isset($currentPosition['Country'])) {
            $currentPosition    = $currentPosition['Country'];
            $address->countryId = $currentPosition['CountryNameCode'];
            $address->country   = $currentPosition['CountryName'];
        }
        if (isset($currentPosition['AdministrativeArea'])) {
            $currentPosition                 = $currentPosition['AdministrativeArea'];
            $address->administrativeAreaName = $currentPosition['AdministrativeAreaName'];
        }
        if (isset($currentPosition['SubAdministrativeArea'])) {
            $currentPosition                    = $currentPosition['SubAdministrativeArea'];
            $address->subAdministrativeAreaName = $currentPosition['SubAdministrativeAreaName'];
        }
        if (isset($currentPosition['Locality'])) {
            $currentPosition       = $currentPosition['Locality'];
            $address->localityName = $currentPosition['LocalityName'];
        }
        if (isset($currentPosition['Thoroughfare'])) {
            $address->thoroughfareName = $currentPosition['Thoroughfare']['ThoroughfareName'];
        }
        if (isset($currentPosition['PostalCode'])) {
            $address->postalCode = $currentPosition['PostalCode']['PostalCodeNumber'];
        }

        // Gets coordinates.
        if (isset($geocodedData['Point']['coordinates'][0])) {
            $address->latitude = $geocodedData['Point']['coordinates'][0];
        }
        if (isset($geocodedData['Point']['coordinates'][1])) {
            $address->longitude = $geocodedData['Point']['coordinates'][1];
        }
        if (isset($geocodedData['ExtendedData']['LatLonBox']['north'])) {
            $address->north = $geocodedData['ExtendedData']['LatLonBox']['north'];
        }
        if (isset($geocodedData['ExtendedData']['LatLonBox']['south'])) {
            $address->south = $geocodedData['ExtendedData']['LatLonBox']['south'];
        }
        if (isset($geocodedData['ExtendedData']['LatLonBox']['east'])) {
            $address->east = $geocodedData['ExtendedData']['LatLonBox']['east'];
        }
        if (isset($geocodedData['ExtendedData']['LatLonBox']['west'])) {
            $address->west = $geocodedData['ExtendedData']['LatLonBox']['west'];
        }
    }

    // Formats the text of the geocoded address using the unused data and
    // compares it to the given address. If they are too different, the user
    // will be asked to choose between them.
    private function formatAddress(Address &$address, $extraLines) {
        $same = true;
        if ($extraLines) {
            $address->geocodedText = $extraLines . "\n" . $address->geocodedText;
        }
        $address->geocodedPostalText = $this->getPostalAddress($address->geocodedText);
        $geoloc = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                          array('', "\n"), $address->geocodedText));
        $text   = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                          array('', "\n"), $address->text));
        $arrayGeoloc = explode("\n", $geoloc);
        $arrayText   = explode("\n", $text);
        $countGeoloc = count($arrayGeoloc);
        $countText   = count($arrayText);

        $totalDistance = 0;
        if (($countText > $countGeoloc) || ($countText < $countGeoloc - 1)
            || (($countText == $countGeoloc - 1)
                && ($arrayText[$countText - 1] == strtoupper($address->country)))) {
            $same = false;
        } else {
            for ($i = 0; $i < $countGeoloc && $i < $countText; ++$i) {
                $lineDistance = levenshtein($arrayText[$i], trim($arrayGeoloc[$i]));
                $totalDistance += $lineDistance;
                if ($lineDistance > self::MAX_LINE_DISTANCE || $totalDistance > self::MAX_TOTAL_DISTANCE) {
                    $same = false;
                    break;
                }
            }
        }

        if ($same) {
            $address->geocodedText = null;
            $address->geocodedPostalText = null;
        } else {
            $address->geocodedText = str_replace("\n", "\r\n", $address->geocodedText);
            $address->geocodedPostalText = str_replace("\n", "\r\n", $address->geocodedPostalText);
        }
        $address->text = str_replace("\n", "\r\n", $address->text);
        $address->postalText = str_replace("\n", "\r\n", $address->postalText);
    }

    // Returns the address formated for postal use.
    // The main rules are (cf AFNOR XPZ 10-011):
    // -everything in upper case;
    // -if there are more then than 38 characters in a line, split it;
    // -if there are more then than 32 characters in the description of the "street", use abbreviations.
    private function getPostalAddress($text) {
         static $abbreviations = array(
             'IMPASSE'   => 'IMP',
             'RUE'       => 'R',
             'AVENUE'    => 'AV',
             'BOULEVARD' => 'BVD',
             'ROUTE'     => 'R',
             'STREET'    => 'ST',
             'ROAD'      => 'RD',
             );

        $text = strtoupper($text);
        $arrayText = explode("\n", $text);
        $postalText = '';

        foreach ($arrayText as $i => $line) {
            $postalText .= (($i == 0) ? '' : "\n");
            if (($length = strlen($line)) > 32) {
                $words = explode(' ', $line);
                $count = 0;
                foreach ($words as $word) {
                    if (isset($abbreviations[$word])) {
                        $word = $abbreviations[$word];
                    }
                    if ($count + ($wordLength = strlen($word)) <= 38) {
                        $postalText .= (($count == 0) ? '' : ' ') . $word;
                        $count += (($count == 0) ? 0 : 1) + $wordLength;
                    } else {
                        $postalText .= "\n" . $word;
                        $count = strlen($word);
                    }
                }
            } else {
                $postalText .= $line;
            }
        }
        return $postalText;
    }

    // Trims the name of the real country if it contains an ISO 3166-1 non-country
    // item. For that purpose, we compare the last but one line of the address with
    // all non-country items of ISO 3166-1.
    private function getTextToGeocode($text)
    {
        $res = XDB::iterator('SELECT  country, countryFR
                                FROM  geoloc_countries
                               WHERE  belongsTo IS NOT NULL');
        $countries = array();
        foreach ($res as $item) {
            $countries[] = $item[0];
            $countries[] = $item[1];
        }
        $textLines  = explode("\n", $text);
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
        return $text;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
