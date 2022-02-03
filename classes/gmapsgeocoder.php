<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

// Implementation of a Geocoder using the Google Maps API v3. Please refer
// to the following link for details:
// http://code.google.com/apis/maps/documentation/geocoding/
//
// It requires the properties gmaps_url to be defined in section Geocoder
// in plat/al's configuration (platal.ini & platal.conf).
class GMapsGeocoder extends Geocoder {

    // Maximum number of Geocoding calls to the Google Maps API.
    const MAX_GMAPS_RPC_CALLS = 5;

    static public function buildStaticMapURL($latitude, $longitude, $color, $separator = '&')
    {
        if (!$latitude || !$longitude) {
            return null;
        }

        $parameters = array(
            'size'    => '300x100',
            'markers' => 'color:' . $color . '|' . $latitude . ',' . $longitude,
            'zoom'    => '12',
            'sensor'  => 'false'
        );

        return Platal::globals()->maps->static_map . '?' . http_build_query($parameters, '', $separator);
    }

    public function getGeocodedAddress(Address $address, $defaultLanguage = null, $forceLanguage = false) {
        $this->prepareAddress($address);
        $textAddress = $this->getTextToGeocode($address->text);
        if (is_null($defaultLanguage)) {
            $defaultLanguage = Platal::globals()->geocoder->gmaps_language;
        }

        // Try to geocode the full address.
        $address->geocoding_calls = 1;
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
            ++$address->geocoding_calls;
            if (($geocodedData = $this->getPlacemarkForAddress($toGeocode, $defaultLanguage))) {
                $this->getUpdatedAddress($address, $geocodedData, $extraLines, $forceLanguage);
                return;
            }
        }
    }

    public function stripGeocodingFromAddress(Address $address) {
        $address->formatted_address = '';
        $address->types = '';
        $address->latitude = null;
        $address->longitude = null;
        $address->southwest_latitude = null;
        $address->southwest_longitude = null;
        $address->northeast_latitude = null;
        $address->northeast_longitude = null;
        $address->location_type = null;
        $address->partial_match = false;
    }

    // Updates the address with the geocoded information from Google Maps. Also
    // cleans up the final informations.
    private function getUpdatedAddress(Address $address, array $geocodedData, $extraLines, $forceLanguage) {
        $this->fillAddressWithGeocoding($address, $geocodedData, false);
        $this->formatAddress($address, $extraLines, $forceLanguage);
    }

    // Retrieves the Placemark object (see #getPlacemarkFromJson()) for the @p
    // address, by querying the Google Maps API. Returns the array on success,
    // and null otherwise.
    private function getPlacemarkForAddress($address, $defaultLanguage) {
        $url     = $this->getGeocodingUrl($address, $defaultLanguage);
        $geoData = $this->getGeoJsonFromUrl($url);

        return ($geoData ? $this->getPlacemarkFromJson($geoData, $url) : null);
    }

    // Prepares address to be geocoded
    private function prepareAddress(Address $address) {
        $address->text = preg_replace('/\s*\n\s*/m', "\n", trim($address->text));
    }

    // Builds the Google Maps geocoder url to fetch information about @p address.
    // Returns the built url.
    private function getGeocodingUrl($address, $defaultLanguage) {
        global $globals;

        $parameters = array(
            'language' => $defaultLanguage,
            'region'   => $globals->geocoder->gmaps_region,
            'sensor'   => 'false',  // The queried address wasn't obtained from a GPS sensor.
            'address'  => $address, // The queries address.
        );

        return $globals->geocoder->gmaps_url . 'json?' . http_build_query($parameters);
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
    // Google Maps. Returns a Placemark array on success, and null otherwise.
    // http://code.google.com/apis/maps/documentation/geocoding/#StatusCodes
    private function getPlacemarkFromJson(array $data, $url) {
        // Check for geocoding status.
        $status = $data['status'];

        // If no result, return null.
        if ($status == 'ZERO_RESULTS') {
            return null;
        }

        // If there are results return the first one.
        if ($status == 'OK') {
            return $data['results'][0];
        }
        return null;
    }

    // Fills the address with the geocoded data
    private function fillAddressWithGeocoding(Address $address, $geocodedData, $isLocal) {
        $address->types               = implode(',', $geocodedData['types']);
        $address->formatted_address   = $geocodedData['formatted_address'];
        $address->components          = $geocodedData['address_components'];
        $address->latitude            = $geocodedData['geometry']['location']['lat'];
        $address->longitude           = $geocodedData['geometry']['location']['lng'];
        $address->southwest_latitude  = $geocodedData['geometry']['viewport']['southwest']['lat'];
        $address->southwest_longitude = $geocodedData['geometry']['viewport']['southwest']['lng'];
        $address->northeast_latitude  = $geocodedData['geometry']['viewport']['northeast']['lat'];
        $address->northeast_longitude = $geocodedData['geometry']['viewport']['northeast']['lng'];
        $address->location_type       = $geocodedData['geometry']['location_type'];
        $address->partial_match       = isset($geocodedData['partial_match']) ? true : false;
    }

    // Formats the text of the geocoded address using the unused data and
    // compares it to the given address. If they are too different, the user
    // will be asked to choose between them.
    private function formatAddress(Address $address, $extraLines, $forceLanguage)
    {
        /* XXX: Check how to integrate this in the new geocoding system.
        if (!$forceLanguage) {
            $languages = XDB::fetchOneCell('SELECT  IF(ISNULL(gc1.belongsTo), gl1.language, gl2.language)
                                              FROM  geoloc_countries AS gc1
                                        INNER JOIN  geoloc_languages AS gl1 ON (gc1.iso_3166_1_a2 = gl1.iso_3166_1_a2)
                                         LEFT JOIN  geoloc_countries AS gc2 ON (gc1.belongsTo = gc2.iso_3166_1_a2)
                                         LEFT JOIN  geoloc_languages AS gl2 ON (gc2.iso_3166_1_a2 = gl2.iso_3166_1_a2)
                                             WHERE  gc1.iso_3166_1_a2 = {?}',
                                           $address->countryId);
            $toGeocode = substr($address->text, strlen($extraLines));
            foreach (explode(',', $languages) as $language) {
                if ($language != Platal::globals()->geocoder->gmaps_language) {
                    $geocodedData = $this->getPlacemarkForAddress($toGeocode, $language);
                    $this->fillAddressWithGeocoding($address, $geocodedData, true);
                    break;
                }
            }
        }*/
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
        if ($countLines < 2) {
            return $text;
        }
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
