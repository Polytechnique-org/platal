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

/** Class Address is meant to perform most of the access to the table profile_addresses.
 *
 * profile_addresses describes an Address, which can be related to either a
 * Profile, a Job or a Company:
 * - for a Profile:
 *   - `type` is set to 'home'
 *   - `pid` is set to the related profile pid (in profiles)
 *   - `id` is the id of the address in the list of those related to that profile
 *   - `jobid` is set to 0
 *
 * - for a Company:
 *   - `type` is set to 'hq'
 *   - `pid` is set to 0
 *   - `jobid` is set to the id of the company (in profile_job_enum)
 *   - `id` is set to 0 (only one address per Company)
 *
 * - for a Job:
 *   - `type` is set to 'job'
 *   - `pid` is set to the pid of the Profile of the related Job (in both profiles and profile_job)
 *   - `id` is the id of the job to which we refer (in profile_job)
 *   - `jobid` is set to 0
 *
 * Thus an Address can be linked to a Company, a Profile, or a Job.
 */
class Address
{
    const LINK_JOB     = 'job';
    const LINK_COMPANY = 'hq';
    const LINK_PROFILE = 'home';

    // Primary key fields: the quadruplet ($pid, $jobid, $type, $id) defines a unique address.
    public $pid = 0;
    public $jobid = 0;
    public $type = Address::LINK_PROFILE;
    public $id = 0;

    // Geocoding fields.
    public $accuracy = 0;
    public $text = '';
    public $postalText = '';
    public $postalCode = null;
    public $localityId = null;
    public $subAdministrativeAreaId = null;
    public $administrativeAreaId = null;
    public $localityName = null;
    public $subAdministrativeAreaName = null;
    public $administrativeAreaName = null;
    public $countryId = null;
    public $latitude = null;
    public $longitude = null;
    public $north = null;
    public $south = null;
    public $east = null;
    public $west = null;
    public $geocodedText = null;
    public $geocodedPostalText = null;
    public $geocodeChosen = null;

    // Database's field required for both 'home' and 'job' addresses.
    public $pub = 'private';

    // Database's fields required for 'home' addresses.
    public $flags = null; // 'current', 'temporary', 'secondary', 'mail', 'cedex'
    public $comment = null;
    public $current = null;
    public $temporary = null;
    public $secondary = null;
    public $mail = null;

    // Remaining fields that do not belong to profile_addresses.
    public $phones = array();
    public $error = false;
    public $changed = 0;
    public $removed = 0;

    public function __construct(array $data = array())
    {
        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
        }

        if ($this->type == self::LINK_PROFILE) {
            if (!is_null($this->flags)) {
                $this->flags = new PlFlagSet($this->flags);
            } else {
                static $flags = array('current', 'temporary', 'secondary', 'mail');

                $this->flags = new PlFlagSet();
                foreach ($flags as $flag) {
                    if (!is_null($this->$flag) && ($this->$flag == 1 || $this->$flag == 'on')) {
                        $this->flags->addFlag($flag, 1);
                        $this->$flag = null;
                    }
                    $this->flags->addFlag('cedex', (strpos(strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                                                                   array('', "\n"), $this->text)), 'CEDEX')) !== false);
                }
            }
        }
    }

    public function phones()
    {
        return $this->phones;
    }

    public function addPhone(Phone &$phone)
    {
        if ($phone->linkType() == Phone::LINK_ADDRESS && $phone->pid() == $this->pid) {
            $this->phones[$phone->uniqueId()] = $phone;
        }
    }

    public function hasFlag($flag)
    {
        return $this->flags->hasFlag($flag);
    }

    public function format(array $format = array())
    {
        if (empty($format)) {
            $format['requireGeocoding'] = false;
            $format['stripGeocoding'] = false;
        }
        $this->text = trim($this->text);
        if ($this->removed == 1) {
            $this->text = '';
            return true;
        }

        if ($format['requireGeocoding'] || $this->changed == 1) {
            $gmapsGeocoder = new GMapsGeocoder();
            $gmapsGeocoder->getGeocodedAddress($this);
            $this->changed = 0;
            $this->error = !empty($this->geocodedText);
        }
        if ($format['stripGeocoding'] || ($this->type == self::LINK_COMPANY && $this->error) || $this->geocodeChosen === '0') {
            $gmapsGeocoder = new GMapsGeocoder();
            $gmapsGeocoder->stripGeocodingFromAddress($this);
            if ($this->geocodeChosen === '0') {
                $mailer = new PlMailer('profile/geocoding.mail.tpl');
                $mailer->assign('text', $this->text);
                $mailer->assign('geoloc', $this->geocodedText);
                $mailer->send();
            }
        }
        $this->geocodeChosen = null;
        $this->phones = Phone::formatFormArray($this->phones, $this->error);
        return !$this->error;
    }

    public function toFormArray()
    {
        $address = array(
            'accuracy'                  => $this->accuracy,
            'text'                      => $this->text,
            'postalText'                => $this->postalText,
            'postalCode'                => $this->postalCode,
            'localityId'                => $this->localityId,
            'subAdministrativeAreaId'   => $this->subAdministrativeAreaId,
            'administrativeAreaId'      => $this->administrativeAreaId,
            'countryId'                 => $this->countryId,
            'localityName'              => $this->localityName,
            'subAdministrativeAreaName' => $this->subAdministrativeAreaName,
            'administrativeAreaName'    => $this->administrativeAreaName,
            'latitude'                  => $this->latitude,
            'longitude'                 => $this->longitude,
            'north'                     => $this->north,
            'south'                     => $this->south,
            'east'                      => $this->east,
            'west'                      => $this->west,
            'error'                     => $this->error,
            'changed'                   => $this->changed,
            'removed'                   => $this->removed,
        );
        if (!is_null($this->geocodedText)) {
            $address['geocodedText'] = $this->geocodedText;
            $address['geocodedPostalText'] = $this->geocodedPostalText;
            $address['geocodeChosen'] = $this->geocodeChosen;
        }

        if ($this->type == self::LINK_PROFILE || $this->type == self::LINK_JOB) {
            $address['pub'] = $this->pub;
        }
        if ($this->type == self::LINK_PROFILE) {
            static $flags = array('current', 'temporary', 'secondary', 'mail', 'cedex');

            foreach ($flags as $flag) {
                $address[$flag] = $this->flags->hasFlag($flag);
            }
            $address['comment'] = $this->comment;
            $address['phones']  = Phone::formatFormArray($this->phones);
        }

        return $address;
    }

    private function toString()
    {
        $address = 'Adresse : ' . $this->text;
        if ($this->type == self::LINK_PROFILE || $this->type == self::LINK_JOB) {
            $address .= ', affichage : ' . $this->pub;
        }
        if ($this->type == self::LINK_PROFILE) {
            static $flags = array(
                'current'   => 'actuelle',
                'temporary' => 'temporaire',
                'secondary' => 'secondaire',
                'mail'      => 'conctactable par courier',
                'cedex'     => 'type cédex',
            );

            $address .= ', commentaire : ' . $this->comment;
            foreach ($flags as $flag => $flagName) {
                if ($this->flags->hasFlag($flag)) {
                    $address .= ', ' . $flagName;
                }
            }
            if ($phones = Phone::formArrayToString($this->phones)) {
                $address .= ', ' . $phones;
            }
        }
        return $address;
    }

    private function isEmpty()
    {
        return (!$this->text || $this->text == '');
    }

    public function save()
    {
        static $areas = array('administrativeArea', 'subAdministrativeArea', 'locality');

        $this->format();
        if (!$this->isEmpty()) {
            foreach ($areas as $area) {
                Geocoder::getAreaId($this, $area);
            }

            XDB::execute('INSERT INTO  profile_addresses (pid, jobid, type, id, flags, accuracy,
                                                          text, postalText, postalCode, localityId,
                                                          subAdministrativeAreaId, administrativeAreaId,
                                                          countryId, latitude, longitude, pub, comment,
                                                          north, south, east, west)
                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?},
                                        {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                         $this->pid, $this->jobid, $this->type, $this->id, $this->flags, $this->accuracy,
                         $this->text, $this->postalText, $this->postalCode, $this->localityId,
                         $this->subAdministrativeAreaId, $this->administrativeAreaId,
                         $this->countryId, $this->latitude, $this->longitude,
                         $this->pub, $this->comment,
                         $this->north, $this->south, $this->east, $this->west);

            if ($this->type == self::LINK_PROFILE) {
                Phone::savePhones($this->phones, $this->pid, Phone::LINK_ADDRESS, $this->id);
            }
        }
    }

    static public function delete($pid, $type, $jobid = null)
    {
        $where = '';
        if (!is_null($pid)) {
            $where = XDB::format(' AND pid = {?}', $pid);
        }
        if (!is_null($jobid)) {
            $where = XDB::format(' AND jobid = {?}', $jobid);
        }
        XDB::execute('DELETE FROM  profile_addresses
                            WHERE  type = {?}' . $where,
                     $type);
        if ($type == self::LINK_PROFILE) {
            Phone::deletePhones($pid, Phone::LINK_ADDRESS);
        }
    }

    /** Saves addresses into the database.
     * @param $data: an array of form formatted addresses.
     * @param $pid, $type, $linkid: pid, type and id concerned by the update.
     */
    static public function saveFromArray(array $data, $pid, $type = self::LINK_PROFILE, $linkid = null)
    {
        foreach ($data as $id => $value) {
            if (!is_null($linkid)) {
                $value['id'] = $linkid;
            } else {
                $value['id'] = $id;
            }
            if (!is_null($pid)) {
                $value['pid'] = $pid;
            }
            if (!is_null($type)) {
                $value['type'] = $type;
            }
            $address = new Address($value);
            $address->save();
        }
    }

    static private function formArrayWalk(array $data, $function, &$success = true, $requiresEmptyAddress = false)
    {
        $addresses = array();
        foreach ($data as $item) {
            $address = new Address($item);
            $success = ($address->format() && $success);
            if (!$address->isEmpty()) {
                $addresses[] = call_user_func(array($address, $function));
            }
        }
        if (count($address) == 0 && $requiresEmptyAddress) {
            $address = new Address();
            $addresses[] = call_user_func(array($address, $function));
        }
        return $addresses;
    }

    // Formats an array of form addresses into an array of form formatted addresses.
    static public function formatFormArray(array $data, &$success = true)
    {
        // Only a single address can be the profile's current address and she must have one.
        $hasCurrent = false;
        foreach ($data as $key => &$address) {
            if (isset($address['current']) && $address['current']) {
                if ($hasCurrent) {
                    $address['current'] = false;
                } else {
                    $hasCurrent = true;
                }
            }
        }
        if (!$hasCurrent && count($value) > 0) {
            foreach ($value as &$address) {
                $address['current'] = true;
                break;
            }
        }

        return self::formArrayWalk($data, 'toFormArray', $success, true);
    }

    static public function formArrayToString(array $data)
    {
        return implode(' ; ', self::formArrayWalk($data, 'toString'));
    }

    static public function iterate(array $pids = array(), array $types = array(),
                                   array $jobids = array(), array $pubs = array())
    {
        return new AddressIterator($pids, $types, $jobids, $pubs);
    }
}

/** Iterator over a set of Phones
 *
 * @param $pid, $type, $jobid, $pub
 *
 * The iterator contains the phones that correspond to the value stored in the
 * parameters' arrays.
 */
class AddressIterator implements PlIterator
{
    private $dbiter;

    public function __construct(array $pids, array $types, array $jobids, array $pubs)
    {
        $where = array();
        if (count($pids) != 0) {
            $where[] = XDB::format('(pa.pid IN {?})', $pids);
        }
        if (count($types) != 0) {
            $where[] = XDB::format('(pa.type IN {?})', $types);
        }
        if (count($jobids) != 0) {
            $where[] = XDB::format('(pa.jobid IN {?})', $jobids);
        }
        if (count($pubs) != 0) {
            $where[] = XDB::format('(pa.pub IN {?})', $pubs);
        }
        $sql = 'SELECT  pa.pid, pa.jobid, pa.type, pa.id, pa.flags,
                        pa.accuracy, pa.text, pa.postalText, pa.postalCode,
                        pa.localityId, pa.subAdministrativeAreaId,
                        pa.administrativeAreaId, pa.countryId,
                        pa.latitude, pa.longitude, pa.north, pa.south, pa.east, pa.west,
                        pa.pub, pa.comment,
                        gl.name AS locality, gs.name AS subAdministrativeArea,
                        ga.name AS administrativeArea, gc.countryFR AS country
                  FROM  profile_addresses             AS pa
             LEFT JOIN  geoloc_localities             AS gl ON (gl.id = pa.localityId)
             LEFT JOIN  geoloc_administrativeareas    AS ga ON (ga.id = pa.administrativeAreaId)
             LEFT JOIN  geoloc_subadministrativeareas AS gs ON (gs.id = pa.subAdministrativeAreaId)
             LEFT JOIN  geoloc_countries              AS gc ON (gc.iso_3166_1_a2 = pa.countryId)
                 WHERE  ' . implode(' AND ', $where) . '
              ORDER BY  pa.pid, pa.jobid, pa.id';
        $this->dbiter = XDB::iterator($sql);
    }

    public function next()
    {
        if (is_null($this->dbiter)) {
            return null;
        }
        $data = $this->dbiter->next();
        if (is_null($data)) {
            return null;
        }
        // Adds phones to addresses.
        $it = Phone::iterate(array($data['pid']), array(Phone::LINK_ADDRESS), array($data['id']));
        while ($phone = $it->next()) {
            $data['phones'][$phone->id()] = $phone->toFormArray();
        }
        return new Address($data);
    }

    public function total()
    {
        return $this->dbiter->total();
    }

    public function first()
    {
        return $this->dbiter->first();
    }

    public function last()
    {
        return $this->dbiter->last();
    }

    public function value()
    {
        return $this->dbiter;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
