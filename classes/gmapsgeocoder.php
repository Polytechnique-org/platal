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

    public function getGeocodedAddress(Address &$address, $defaultLanguage = null, $forceLanguage = false) {
        $this->prepareAddress($address);
        $textAddress = $this->getTextToGeocode($address->text);
        if (is_null($defaultLanguage)) {
            $defaultLanguage = Platal::globals()->geocoder->gmaps_hl;
        }

        // Try to geocode the full address.
        if (($geocodedData = $this->getPlacemarkForAddress($textAddress, $defaultLanguage))) {
            $this->getUpdatedAddress($address, $geocodedData, null, $forceLanguage);
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
            if (($geocodedData = $this->getPlacemarkForAddress($toGeocode, $defaultLanguage))) {
                $this->getUpdatedAddress($address, $geocodedData, $extraLines, $forceLanguage);
                return;
            }
        }
    }

    public function stripGeocodingFromAddress(Address &$address) {
        $address->geocodedText = null;
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
    private function getUpdatedAddress(Address &$address, array $geocodedData, $extraLines, $forceLanguage) {
        $this->fillAddressWithGeocoding($address, $geocodedData, false);
        $this->formatAddress($address, $extraLines, $forceLanguage);
    }

    // Retrieves the Placemark object (see #getPlacemarkFromJson()) for the @p
    // address, by querying the Google Maps API. Returns the array on success,
    // and null otherwise.
    private function getPlacemarkForAddress($address, $defaultLanguage) {
        $url     = $this->getGeocodingUrl($address, $defaultLanguage);
        $geoData = $this->getGeoJsonFromUrl($url);

        return ($geoData ? $this->getPlacemarkFromJson($geoData) : null);
    }

    // Prepares address to be geocoded
    private function prepareAddress(Address &$address) {
        $address->text = preg_replace('/\s*\n\s*/m', "\n", trim($address->text));
    }

    // Builds the Google Maps geocoder url to fetch information about @p address.
    // Returns the built url.
    private function getGeocodingUrl($address, $defaultLanguage) {
        global $globals;

        $parameters = array(
            'key'    => $globals->geocoder->gmaps_key,
            'sensor' => 'false',   // The queried address wasn't obtained from a GPS sensor.
            'hl'     => $defaultLanguage,
            'oe'     => 'utf8',    // Output encoding.
            'output' => 'json',    // Output format.
            'gl'     => $globals->geocoder->gmaps_gl,
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
    private function fillAddressWithGeocoding(Address &$address, $geocodedData, $isLocal) {
        // The geocoded address three is
        // Country -> AdministrativeArea -> SubAdministrativeArea -> Locality -> Thoroughfare
        // with all the possible shortcuts
        // The address is formatted as xAL, or eXtensible Address Language, an international
        // standard for address formatting.
        // xAL documentation: http://www.oasis-open.org/committees/ciq/ciq.html#6
        if ($isLocal) {
            $ext = 'Local';
        } else {
            $ext = ucfirst(Platal::globals()->geocoder->gmaps_hl);
            $address->geocodedText = str_replace(', ', "\n", $geocodedData['address']);
        }

        if (isset($geocodedData['AddressDetails']['Accuracy'])) {
            $address->accuracy = $geocodedData['AddressDetails']['Accuracy'];
        }

        $currentPosition = $geocodedData['AddressDetails'];
        if (isset($currentPosition['Country'])) {
            $country = 'country' . $ext;
            $currentPosition    = $currentPosition['Country'];
            $address->countryId = $currentPosition['CountryNameCode'];
            $address->$country  = $currentPosition['CountryName'];
        }
        if (isset($currentPosition['AdministrativeArea'])) {
            $administrativeAreaName = 'administrativeAreaName' . $ext;
            $currentPosition                  = $currentPosition['AdministrativeArea'];
            $address->$administrativeAreaName = $currentPosition['AdministrativeAreaName'];
        }
        if (isset($currentPosition['SubAdministrativeArea'])) {
            $subAdministrativeAreaName = 'subAdministrativeAreaName' . $ext;
            $currentPosition                     = $currentPosition['SubAdministrativeArea'];
            $address->$subAdministrativeAreaName = $currentPosition['SubAdministrativeAreaName'];
        }
        if (isset($currentPosition['Locality'])) {
            $localityName = 'localityName' . $ext;
            $currentPosition        = $currentPosition['Locality'];
            $address->$localityName = $currentPosition['LocalityName'];
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

    // Compares the geocoded address with the given address and returns true
    // iff their are close enough to be considered as equals or not.
    private function compareAddress($address)
    {
        $same = true;
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

        return $same;
    }

    // Formats the text of the geocoded address using the unused data and
    // compares it to the given address. If they are too different, the user
    // will be asked to choose between them.
    private function formatAddress(Address &$address, $extraLines, $forceLanguage)
    {
        if ($extraLines) {
            $address->geocodedText = $extraLines . "\n" . $address->geocodedText;
        }

        if ($this->compareAddress($address)) {
            $address->geocodedText = null;
        } elseif (!$forceLanguage) {
            $languages = XDB::fetchOneCell('SELECT  IF(ISNULL(gc1.belongsTo), gl1.language, gl2.language)
                                              FROM  geoloc_countries AS gc1
                                        INNER JOIN  geoloc_languages AS gl1 ON (gc1.iso_3166_1_a2 = gl1.iso_3166_1_a2)
                                         LEFT JOIN  geoloc_countries AS gc2 ON (gc1.belongsTo = gc2.iso_3166_1_a2)
                                         LEFT JOIN  geoloc_languages AS gl2 ON (gc2.iso_3166_1_a2 = gl2.iso_3166_1_a2)
                                             WHERE  gc1.iso_3166_1_a2 = {?}',
                                           $address->countryId);
            $toGeocode = substr($address->text, strlen($extraLines));
            foreach (explode(',', $languages) as $language) {
                if ($language != Platal::globals()->geocoder->gmaps_hl) {
                    $geocodedData = $this->getPlacemarkForAddress($toGeocode, $language);
                    $address->geocodedText = str_replace(', ', "\n", $geocodedData['address']);
                    if ($extraLines) {
                        $address->geocodedText = $extraLines . "\n" . $address->geocodedText;
                    }
                    if ($this->compareAddress($address)) {
                        $this->fillAddressWithGeocoding($address, $geocodedData, true);
                        $address->geocodedText = null;
                        break;
                    }
                }
            }
            $address->geocodedText = str_replace("\n", "\r\n", $address->geocodedText);
        }
        $address->text = str_replace("\n", "\r\n", $address->text);
    }

    // Trims the name of the real country if it contains an ISO 3166-1 non-country
    // item. For that purpose, we compare the last but one line of the address with
    // all non-country items of ISO 3166-1.
    private function getTextToGeocode($text)
    {
        $res = XDB::iterator('SELECT  countryEn, country
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
        if ($needle) {
            foreach ($countries as $country) {
                if (strtoupper($country) === $needle) {
                    $isPseudoCountry = true;
                    break;
                }
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
