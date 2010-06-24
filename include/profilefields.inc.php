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

// {{{ class ProfileField
/** To store a "field" from the profile
 * Provides functions for loading a batch of such data
 */
abstract class ProfileField
{
    public static $fields = array(
        Profile::FETCH_ADDRESSES      => 'ProfileAddresses',
        Profile::FETCH_CORPS          => 'ProfileCorps',
        Profile::FETCH_EDU            => 'ProfileEducation',
        Profile::FETCH_JOBS           => 'ProfileJobs',
        Profile::FETCH_MEDALS         => 'ProfileMedals',
        Profile::FETCH_NETWORKING     => 'ProfileNetworking',
        Profile::FETCH_PHONES         => 'ProfilePhones',
        Profile::FETCH_MENTOR_SECTOR  => 'ProfileMentoringSectors',
        Profile::FETCH_MENTOR_COUNTRY => 'ProfileMentoringCountries',
    );

    /** The profile to which this field belongs
     */
    public $pid;

    /** Fetches data from the database for the given pids, compatible with
     * the visibility context.
     * @param $pids An array of pids
     * @param $visibility The level of visibility fetched fields must have
     * @return a PlIterator yielding data suitable for a "new ProfileBlah($data)"
     * XXX MUST be reimplemented for each kind of ProfileField
     */
    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        return PlIteratorUtils::emptyIterator();
    }

    public static function buildForPID($cls, $pid, ProfileVisibility $visibility)
    {
        $res = self::buildFromPIDs($cls, array($pid), $visibility);
        return array_pop($res);
    }

    /** Build a list of ProfileFields from a set of pids
     * @param $cls The name of the field to create ('ProfileMedals', ...)
     * @param $pids An array of pids
     * @param $visibility An array of allowed visibility contexts
     * @return An array of $pid => ProfileField
     */
    public static function buildFromPIDs($cls, array $pids, ProfileVisibility $visibility)
    {
        $it = new ProfileFieldIterator($cls, $pids, $visibility);
        $res = array();
        while ($pf = $it->next()) {
            $res[$pf->pid] = $pf;
        }
        return $res;
    }

    public static function getForPID($cls, $pid, ProfileVisibility $visibility)
    {
        $it = new ProfileFieldIterator($cls, array($pid), $visibility);
        return $it->next();
    }
}
// }}}

// {{{ class ProfileFieldIterator
class ProfileFieldIterator implements PlIterator
{
    private $data;
    private $cls;

    public function __construct($cls, array $pids, ProfileVisibility $visibility)
    {
        $this->data = call_user_func(array($cls, 'fetchData'), $pids, $visibility);
        $this->cls = $cls;
    }

    public function next()
    {
        $d = $this->data->next();
        if ($d == null) {
            return null;
        } else {
            $cls = $this->cls;
            return new $cls($d);
        }
    }

    public function total()
    {
        return $this->data->total();
    }

    public function first()
    {
        return $this->data->first();
    }

    public function last()
    {
        return $this->data->last();
    }
}
// }}}

// {{{ class Phone
class Phone
{
    const TYPE_FAX    = 'fax';
    const TYPE_FIXED  = 'fixed';
    const TYPE_MOBILE = 'mobile';
    public $type;

    public $search;
    public $display;
    public $comment = '';

    const LINK_JOB     = 'pro';
    const LINK_ADDRESS = 'address';
    const LINK_PROFILE = 'user';
    const LINK_COMPANY = 'hq';
    public $link_type;
    public $link_id;

    public $id;

    /** Fields are :
     * $type, $search, $display, $link_type, $link_id, $comment, $pid, $id
     */
    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function hasFlags($flags) {
        return $this->hasType($flags) && $this->hasLink($flags);
    }

    /** Returns true if this phone's type matches the flags
     */
    public function hasType($flags) {
        $flags = $flags & Profile::PHONE_TYPE_ANY;
        return (
            ($flags == Profile::PHONE_TYPE_ANY)
            ||
            (($flags & Profile::PHONE_TYPE_FAX) && $this->type == self::TYPE_FAX)
            ||
            (($flags & Profile::PHONE_TYPE_FIXED) && $this->type == self::TYPE_FIXED)
            ||
            (($flags & Profile::PHONE_TYPE_MOBILE) && $this->type == self::TYPE_MOBILE)
        );
    }

    /** User accessible version of the type
     */
    public function displayType($short = false)
    {
        switch ($this->type) {
          case Phone::TYPE_FIXED:
            return $short ? 'Tél' : 'Fixe';
          case Phone::TYPE_FAX:
            return 'Fax';
          case Phone::TYPE_MOBILE:
            return $short ? 'Mob' : 'Mobile';
          default:
            return $this->type;
        }
    }

    /** Returns true if this phone's link matches the flags
     */
    public function hasLink($flags) {
        $flags = $flags & Profile::PHONE_LINK_ANY;
        return (
            ($flags == Profile::PHONE_LINK_ANY)
            ||
            (($flags & Profile::PHONE_LINK_COMPANY) && $this->link_type == self::LINK_COMPANY)
            ||
            (($flags & Profile::PHONE_LINK_JOB) && $this->link_type == self::LINK_JOB)
            ||
            (($flags & Profile::PHONE_LINK_ADDRESS) && $this->link_type == self::LINK_ADDRESS)
            ||
            (($flags & Profile::PHONE_LINK_PROFILE) && $this->link_type == self::LINK_PROFILE)
        );
    }
}
// }}}
// {{{ class Company
class Company
{
    public $id;
    public $name;
    public $acronym;
    public $url;
    public $phone = null;
    public $address = null;

    /** Fields are:
     * $id, $name, $acronym, $url
     */
    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function setPhone(Phone &$phone)
    {
        if ($phone->link_type == Phone::LINK_COMPANY && $phone->link_id == $this->id) {
            $this->phone = $phone;
        }
    }

    public function setAddress(Address &$address)
    {
        if ($address->link_type == Address::LINK_COMPANY && $address->link_id == $this->id) {
            $this->address = $address;
        }
    }

}
// }}}
// {{{ class Job
class Job
{
    public $pid;
    public $id;

    public $company = null;
    private $phones = array();
    private $address = null;

    public $jobid;

    public $description;
    public $user_site;
    public $user_email;

    public $sector;
    public $subsector;
    public $subsubsector;

    /** Fields are:
     * pid, id, company_id, description, url, email
     */
    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
        $this->company = CompanyList::get($this->jobid);
    }

    public function phones()
    {
        return $this->phones;
    }

    public function address()
    {
        return $this->address;
    }

    public function addPhone(Phone &$phone)
    {
        if ($phone->link_type == Phone::LINK_JOB && $phone->link_id == $this->id && $phone->pid == $this->pid) {
            $this->phones[] = $phone;
        }
    }

    public function setAddress(Address $address)
    {
        if ($address->link_id == Address::LINK_JOB && $address->link_id == $this->id && $address->pid == $this->pid) {
            $this->address = $address;
        }
    }
}
// }}}
// {{{ class Address
class Address
{
    const LINK_JOB     = 'job';
    const LINK_COMPANY = 'hq';
    const LINK_PROFILE = 'home';

    public $flags;
    public $link_id;
    public $link_type;

    public $text;
    public $postalCode;
    public $latitude;
    public $longitude;

    public $locality;
    public $subAdministrativeArea;
    public $administrativeArea;
    public $country;

    public $comment;

    private $phones = array();

    /** Fields are:
     * pîd, id, link_id, link_type, flags, text, postcode, country
     */
    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
        $this->flags = new PlFlagSet($this->flags);
    }

    public function addPhone(Phone &$phone)
    {
        if ($phone->link_type == Phone::LINK_ADDRESS && $phone->link_id == $this->id && $phone->pid == $this->pid) {
            $this->phones[$phone->id] = $phone;
        }
    }

    public function phones()
    {
        return $this->phones;
    }

    public function hasFlag($flag)
    {
        if (!$this->flags instanceof PlFlagSet) {
            $this->flags = new PlFlagSet($this->flags);
        }
        return $this->flags->hasFlag($flag);
    }
}
// }}}
// {{{ class Education
class Education
{
    public $id;
    public $pid;

    public $entry_year;
    public $grad_year;
    public $program;
    public $flags;

    public $school;
    public $school_short;
    public $school_url;
    public $country;

    public $degree;
    public $degree_short;
    public $degree_level;

    public $field;

    public function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
        $this->flags      = new PlFlagSet($this->flags);
    }
}
// }}}

// {{{ class ProfileEducation                         [ Field ]
class ProfileEducation extends ProfileField
{
    private $educations = array();

    public function __construct(PlInnerSubIterator $it)
    {
        $this->pid = $it->value();
        while ($edu = $it->next()) {
            $this->educations[$edu['id']] = new Education($edu);
        }
    }

    public function get($flags, $limit)
    {
        $educations = array();
        $year = getdate();
        $year = $year['year'];
        $nb = 0;
        foreach ($this->educations as $id => $edu) {
            if (
                (($flags & Profile::EDUCATION_MAIN) && $edu->flags->hasFlag('primary'))
                ||
                (($flags & Profile::EDUCATION_EXTRA) && !$edu->flags->hasFlag('primary'))
                ||
                (($flags & Profile::EDUCATION_FINISHED) && $edu->grad_year <= $year)
                ||
                (($flags & Profile::EDUCATION_CURRENT) && $edu->grad_year > $year)
                ||
                ($flags & Profile::EDUCATION_ALL)
            ) {
                $educations[$id] = $edu;
                ++$nb;
            }
            if ($limit != null && $nb >= $limit) {
                break;
            }
        }
        return $educations;
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        $data = XDB::iterator('SELECT  pe.id, pe.pid,
                                       pe.entry_year, pe.grad_year, pe.program, pe.flags,
                                       pee.name AS school, pee.abbreviation AS school_short,
                                       pee.url AS school_url, gc.countryFR AS country,
                                       pede.degree, pede.abbreviation AS degree_short, pede.level AS degree_level,
                                       pefe.field
                                 FROM  profile_education AS pe
                            LEFT JOIN  profile_education_enum AS pee ON (pee.id = pe.eduid)
                            LEFT JOIN  geoloc_countries AS gc ON (gc.iso_3166_1_a2 = pee.country)
                            LEFT JOIN  profile_education_degree_enum AS pede ON (pede.id = pe.degreeid)
                            LEFT JOIN  profile_education_field_enum AS pefe ON (pefe.id = pe.fieldid)
                                WHERE  pe.pid IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids) . ',
                                       NOT FIND_IN_SET(\'primary\', pe.flags), pe.entry_year, pe.id',
                                $pids);

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }
}
// }}}
// {{{ class ProfileMedals                            [ Field ]
class ProfileMedals extends ProfileField
{
    public $medals = array();

    public function __construct(PlInnerSubIterator $it)
    {
        $this->pid = $it->value();
        while ($medal = $it->next()) {
            $this->medals[$medal['mid']] = $medal;
        }
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        $data = XDB::iterator('SELECT  pm.pid, pm.mid, pm.gid, pme.text, pme.img, pmge.text AS grade
                                 FROM  profile_medals AS pm
                            LEFT JOIN  profiles AS p ON (pm.pid = p.pid)
                            LEFT JOIN  profile_medal_enum AS pme ON (pme.id = pm.mid)
                            LEFT JOIN  profile_medal_grade_enum AS pmge ON (pmge.mid = pm.mid AND pmge.gid = pm.gid)
                                WHERE  pm.pid IN {?} AND p.medals_pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pm.pid', $pids),
                                $pids, $visibility->levels());

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }
}
// }}}
// {{{ class ProfileNetworking                        [ Field ]
class ProfileNetworking extends ProfileField
{
    private $networks = array();

    public function __construct(PlInnerSubIterator $it)
    {
        $this->pid = $it->value();
        while ($network = $it->next()) {
            $network['network_type'] = new PlFlagSet($network['network_type']);
            $this->networks[$network['id']] = $network;
        }
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        $data = XDB::iterator('SELECT  pid, id, address, pne.nwid, pne.network_type, pne.link, pne.name
                                 FROM  profile_networking AS pn
                            LEFT JOIN  profile_networking_enum AS pne USING(nwid)
                                WHERE  pid IN {?} AND pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids) . ',
                                       pn.nwid, id',
                               $pids, $visibility->levels());

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }

    public function get($flags, $limit = null)
    {
        if (!$flags) {
            $flags = Profile::NETWORKING_ALL;
        }
        $nws = array();
        $nb = 0;
        foreach ($this->networks as $id => $nw) {
            if (($flags & Profile::NETWORKING_WEB) && $nw['network_type']->hasFlag('web') ||
                ($flags & Profile::NETWORKING_IM) && $nw['network_type']->hasFlag('im') ||
                ($flags & Profile::NETWORKING_SOCIAL) && $nw['network_type']->hasFlag('social') ||
                ($flags & Profile::NETWORKING_ALL)) {
                $nws[$id] = $nw;
                ++$nb;
                if (isset($limit) && $nb >= $limit) {
                    break;
                }
            }
        }
        return $nws;
    }
}
// }}}
// {{{ class ProfileCorps                             [ Field ]
class ProfileCorps extends ProfileField
{
    public $original;
    public $current;

    public $original_name;
    public $original_abbrev;
    public $original_still_exists;

    public $current_name;
    public $current_abbrev;
    public $current_still_exists;
    public $current_rank;
    public $current_rank_abbrev;

    public function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        $data = XDB::iterator('SELECT  pc.pid, pc.original_corpsid AS original, pc.current_corpsid AS current,
                                       pceo.name AS original_name, pceo.abbreviation AS original_abbrev,
                                       pceo.still_exists AS original_still_exists,
                                       pcec.name AS current_name, pcec.abbreviation AS current_abbrev,
                                       pcec.still_exists AS current_still_exists,
                                       pcrec.name AS current_rank, pcrec.abbreviation AS current_rank_abbrev,
                                       rankid
                                 FROM  profile_corps AS pc
                            LEFT JOIN  profile_corps_enum AS pceo ON (pceo.id = pc.original_corpsid)
                            LEFT JOIN  profile_corps_enum AS pcec ON (pcec.id = pc.current_corpsid)
                            LEFT JOIN  profile_corps_rank_enum AS pcrec ON (pcrec.id = pc.rankid)
                                WHERE  pc.pid IN {?} AND pc.corps_pub IN {?} AND pceo.id != 1
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids),
                                $pids, $visibility->levels());

        return $data;
    }
}
// }}}
// {{{ class ProfileMentoringSectors                  [ Field ]
class ProfileMentoringSectors extends ProfileField
{
    public $sectors = array();

    public function __construct(PlInnerSubIterator $it)
    {
        $this->pid = $it->value();
        while ($sector = $it->next()) {
            $this->sectors[] = $sector;
        }
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        $data = XDB::iterator('SELECT  pms.pid, pjse.name AS sector, pjsse.name AS subsector
                                 FROM  profile_mentor_sector AS pms
                            LEFT JOIN  profile_job_sector_enum AS pjse ON (pjse.id = pms.sectorid)
                            LEFT JOIN  profile_job_subsector_enum AS pjsse ON (pjsse.id = pms.subsectorid)
                                WHERE  pms.pid IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pms.pid', $pids),
                                $pids);

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }
}
// }}}
// {{{ class ProfileMentoringCountries                [ Field ]
class ProfileMentoringCountries extends ProfileField
{
    public $countries = array();

    public function __construct(PlInnerSubIterator $it)
    {
        $this->pid = $it->value();
        while ($country = $it->next()) {
            $this->countries[$country['id']] = $country['name'];
        }
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        $data = XDB::iterator('SELECT  pmc.pid, pmc.country AS id, gc.countryFR AS name
                                 FROM  profile_mentor_country AS pmc
                            LEFT JOIN  geoloc_countries AS gc ON (gc.iso_3166_1_a2 = pmc.country)
                                WHERE  pmc.pid IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pmc.pid', $pids),
                                $pids);

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }
}
// }}}

/** Loading of data for a Profile :
 * 1) load jobs, addresses, phones
 * 2) attach phones to addresses, jobs and profiles
 * 3) attach addresses to jobs and profiles
 */

// {{{ class ProfileAddresses                         [ Field ]
class ProfileAddresses extends ProfileField
{
    private $addresses = array();

    public function __construct(PlIterator $it)
    {
        if ($it instanceof PlInnerSubIterator) {
            $this->pid = $it->value();
        }

        while ($addr = $it->next()) {
            $this->addresses[$addr['id']] = new Address($addr);
        }
    }

    public function get($flags, $limit = null)
    {
        $res = array();
        $nb = 0;
        foreach ($this->addresses as $addr) {
            if (
                (($flags & Profile::ADDRESS_MAIN) && $addr->hasFlag('current'))
                ||
                (($flags & Profile::ADDRESS_POSTAL) && $addr->hasFlag('mail'))
                ||
                (($flags & Profile::ADDRESS_PERSO) && $addr->link_type == Address::LINK_PROFILE)
                ||
                (($flags & Profile::ADDRESS_PRO) && $addr->link_type == Address::LINK_JOB)
            ) {
                $res[] = $addr;
                $nb++;
            }
            if ($limit != null && $nb == $limit) {
                break;
            }
        }
        return $res;
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        $data = XDB::iterator('SELECT  pa.id, pa.pid, pa.flags, pa.type AS link_type,
                                       IF(pa.type = \'home\', pid, jobid) AS link_id,
                                       pa.text, pa.postalCode, pa.latitude, pa.longitude, pa.comment,
                                       gl.name AS locality, gas.name AS subAdministrativeArea,
                                       ga.name AS administrativeArea, gc.countryFR AS country
                                 FROM  profile_addresses AS pa
                            LEFT JOIN  geoloc_localities AS gl ON (gl.id = pa.localityId)
                            LEFT JOIN  geoloc_administrativeareas AS ga ON (ga.id = pa.administrativeAreaId)
                            LEFT JOIN  geoloc_administrativeareas AS gas ON (gas.id = pa.subAdministrativeAreaId)
                            LEFT JOIN  geoloc_countries AS gc ON (gc.iso_3166_1_a2 = pa.countryId)
                                WHERE  pa.pid in {?} AND pa.pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids),
                               $pids, $visibility->levels());

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }

    public function addPhones(ProfilePhones $phones)
    {
        $p = $phones->get(Profile::PHONE_LINK_ADDRESS | Profile::PHONE_TYPE_ANY);
        foreach ($p as $phone) {
            if ($phone->link_type == Phone::LINK_ADDRESS && array_key_exists($phone->link_id, $this->addresses)) {
                $this->addresses[$phone->link_id]->addPhone($phone);
            }
        }
    }
}
// }}}
// {{{ class ProfilePhones                            [ Field ]
class ProfilePhones extends ProfileField
{
    private $phones = array();

    public function __construct(PlInnerSubIterator $it)
    {
        $this->pid = $it->value();
        while ($phone = $it->next()) {
            $this->phones[] = new Phone($phone);
        }
    }

    public function get($flags, $limit = null)
    {
        $phones = array();
        $nb = 0;
        foreach ($this->phones as $id => $phone) {
            if ($phone->hasFlags($flags)) {
                $phones[$id] = $phone;
                ++$nb;
                if ($limit != null && $nb == $limit) {
                    break;
                }
            }
        }
        return $phones;
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        $data = XDB::iterator('SELECT  tel_type AS type, search_tel AS search, display_tel AS display, link_type, comment, pid, link_id, tel_id AS id
                                 FROM  profile_phones
                                WHERE  pid IN {?} AND pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids),
                                 $pids, $visibility->levels());
        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }
}
// }}}
// {{{ class ProfileJobs                              [ Field ]
class ProfileJobs extends ProfileField
{
    private $jobs = array();

    public function __construct(PlInnerSubIterator $jobs)
    {
        $this->pid = $jobs->value();
        while ($job = $jobs->next()) {
            $this->jobs[$job['id']] = new Job($job);
        }
    }

    public static function fetchData(array $pids, ProfileVisibility $visibility)
    {
        CompanyList::preload($pids);
        $data = XDB::iterator('SELECT  pj.id, pj.pid, pj.description, pj.url as user_site,
                                       IF(pj.email_pub IN {?}, pj.email, NULL) AS user_email,
                                       pj.jobid, pjse.name AS sector, pjsse.name AS subsector,
                                       pjssse.name AS subsubsector
                                 FROM  profile_job AS pj
                            LEFT JOIN  profile_job_sector_enum AS pjse ON (pjse.id = pj.sectorid)
                            LEFT JOIN  profile_job_subsector_enum AS pjsse ON (pjsse.id = pj.subsectorid)
                            LEFT JOIN  profile_job_subsubsector_enum AS pjssse ON (pjssse.id = pj.subsubsectorid)
                                WHERE  pj.pid IN {?} AND pj.pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids) . ',
                                       pj.id',
                                 $visibility->levels(), $pids, $visibility->levels());
        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }

    public function get($flags, $limit = null)
    {
        $jobs = array();
        $nb = 0;
        foreach ($this->jobs as $id => $job) {
            $jobs[$id] = $job;
            ++$nb;
            if ($limit != null && $nb >= $limit) {
                break;
            }
        }
        return $jobs;
    }

    public function addPhones(ProfilePhones $phones)
    {
        $p = $phones->get(Profile::PHONE_LINK_JOB | Profile::PHONE_TYPE_ANY);
        foreach ($p as $phone) {
            if ($phone->link_type == Phone::LINK_JOB && array_key_exists($phone->link_id, $this->jobs)) {
                $this->jobs[$phone->link_id]->addPhone($phone);
            }
        }
    }

    public function addAddresses(ProfileAddresses $addresses)
    {
        $a = $addresses->get(Profile::ADDRESS_PRO);
        foreach ($a as $address) {
            if ($address->link_type == Address::LINK_JOB && array_key_exists($address->link_id, $this->jobs)) {
                $this->jobs[$address->link_id]->setAddress($address);
            }
        }
    }

    public function addCompanies(array $companies)
    {
        foreach ($this->jobs as $job) {
            $this->company = $companies[$job->jobid];
        }
    }
}
// }}}

// {{{ class CompanyList
class CompanyList
{
    static private $fullload = false;
    static private $companies = array();

    static public function preload($pids = array())
    {
        if (self::$fullload) {
            return;
        }
        // Load raw data
        if (count($pids)) {
            $join = 'LEFT JOIN profile_job ON (profile_job.jobid = pje.id)';
            $where = 'WHERE profile_job.pid IN ' . XDB::formatArray($pids);
        } else {
            $join = '';
            $where = '';
        }

        $it = XDB::iterator('SELECT  pje.id, pje.name, pje.acronym, pje.url,
                                     pa.flags, pa.text, pa.postalCode, pa.countryId,
                                     pa.type, pa.pub
                               FROM  profile_job_enum AS pje
                          LEFT JOIN  profile_addresses AS pa ON (pje.id = pa.jobid AND pa.type = \'hq\')
                                  ' . $join . '
                                  ' . $where);
        $newcompanies = array();
        while ($row = $it->next()) {
            $cp = new Company($row);
            $addr = new Address($row);
            $cp->setAddress($addr);
            if (!array_key_exists($row['id'], self::$companies)) {
                $newcompanies[] = $row['id'];
            }
            self::$companies[$row['id']] = $cp;
        }

        // TODO: determine whether there can be phones attached to a hq's address
        // Add phones to hq
        if (count($newcompanies)) {
            $it = XDB::iterator('SELECT  search_tel AS search, display_tel AS display, comment, link_id, tel_type AS type, link_type, tel_id AS id
                                   FROM  profile_phones
                                  WHERE  link_id IN {?} AND link_type = \'hq\'',
                                    $newcompanies);
            while ($row = $it->next()) {
                $p = new Phone($row);
                self::$companies[$row['link_id']]->setPhone($p);
            }
        }

        if (count($pids) == 0) {
            self::$fullload = true;
        }
    }

    static public function get($id)
    {
        if (!array_key_exists($id, self::$companies)) {
            self::preload();
        }
        return self::$companies[$id];
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
