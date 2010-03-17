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
    /** The profile to which this field belongs
     */
    public $pid;

    /** Fetches data from the database for the given pids, compatible with
     * the visibility context.
     * @param $pids An array of pids
     * @param $visibility The level of visibility fetched fields must have
     * @return a PlIterator yielding data suitable for a "new ProfileBlah($data)"
     */
    abstract public static function fetchData(array $pids, $visibility);

    public static function buildForPID($cls, $pid, $visibility)
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
    public static function buildFromPIDs($cls, array $pids, $visibility)
    {
        $it = new ProfileFieldIterator($cls, $pids, $visibility);
        $res = array();
        while ($pf = $it->next()) {
            $res[$pf->pid] = $pf;
        }
        return $res;
    }

    public static function getForPID($cls, $pid, $visibility)
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

    public function __construct($cls, array $pids, $visibility)
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

    const LINK_JOB     = 'job';
    const LINK_ADDRESS = 'address';
    const LINK_PROFILE = 'user';
    const LINK_COMPANY = 'hq';
    public $link_type;
    public $link_id;

    /** Fields are :
     * $type, $search, $display, $link_type, $link_id, $comment, $pid, $id
     */
    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
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
    public function __construct($date)
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

    private $company = null;
    private $phones = array();
    private $address = null;

    public $company_id;

    public $description;
    public $url;
    public $email;

    /** Fields are:
     * pid, id, company_id, description, url, email
     */
    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function phones()
    {
        return $this->phones;
    }

    public function company()
    {
        return $this->company;
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

    public function setCompany(Company $company)
    {
        $this->company = $company;
    }
}
// }}}
// {{{ class Address
class Address
{
    const LINK_JOB     = 'job';
    const LINK_COMPANY = 'hq';
    const LINK_PROFILE = 'home';

    public $link_id;
    public $link_type;

    public $flags;
    public $text;
    public $postcode;
    public $country;

    private $phones = array();

    /** Fields are:
     * pîd, id, link_id, link_type, flags, text, postcode, country
     */
    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function addPhone(Phone &$phone)
    {
        if ($phone->link_type == Phone::LINK_ADDRESS && $phone->link_id == $this->id && $phone->pid == $this->pid) {
            $this->phones[] = $phone;
        }
    }

    public function phones()
    {
        return $this->phones;
    }

    public function hasFlags($flags)
    {
        return $flags & $this->flags;
    }
}
// }}}
// {{{ class Education
class Education
{
    public $eduid;
    public $degreeid;
    public $fieldid;

    public $entry_year;
    public $grad_year;
    public $program;
    public $flags;

    public function __construct(array $data)
    {
        $this->eduid    = $data['eduid'];
        $this->degreeid = $data['degreeid'];
        $this->fieldid  = $data['fieldid'];

        $this->entry_year = $data['entry_year'];
        $this->grad_year  = $data['grad_year'];
        $this->program    = $data['program'];
        $this->flags      = new PlFlagSet($data['flags']);
    }
}
// }}}

// {{{ class ProfileEducation                         [ Field ]
class ProfileEducation extends ProfileField
{
    private $educations = array();

    private function __construct(PlIterator $it)
    {
        $this->pid = $it->value();
        $this->visibility = Profile::VISIBILITY_PUBLIC;
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
            ) {
                $educations[$id] = $edu;
                ++$nb;
            }
            if ($limit != null && $nb >= $limit) {
                break;
            }
        }
        return PlIteratorUtils::fromArray($educations);
    }

    public static function fetchData(array $pids, $visibility)
    {
        $data = XDB::iterator('SELECT  id, pid, eduid, degreeid, fieldid,
                                       entry_year, grad_year, program, flags
                                 FROM  profile_education
                                WHERE  pid IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids) . ',
                                       NOT FIND_IN_SET(\'primary\', flags), entry_year, id',
                                $pids);

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }
}
// }}}
// {{{ class ProfileMedals                            [ Field ]
class ProfileMedals extends ProfileField
{
    public $medals = array();

    private function __construct(PlIterator $it)
    {
        while ($medal = $it->next()) {
            $this->medals[$medal['mid']] = $medal['gid'];
        }
    }

    public static function fetchData(array $pids, $visibility)
    {
        $data = XDB::iterator('SELECT  pm.pid, pm.mid, pm.gid
                                 FROM  profile_medals AS pm
                            LEFT JOIN  profiles AS p ON (pm.pid = p.pid)
                                WHERE  pm.pid IN {?} AND p.medals_pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pm.pid', $pids),
                                XDB::formatArray($pids),
                                XDB::formatArray($visibility)
                            );

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }
}
// }}}
// {{{ class ProfileNetworking                        [ Field ]
class ProfileNetworking extends ProfileField
{
    private $networks = array();

    private function __construct(PlIterator $it)
    {
        while ($network = $it->next()) {
            $this->networks[$network['nwid']] = $network['address'];
        }
    }

    public static function fetchData(array $pids, $visibility)
    {
        $data = XDB::iterator('SELECT  pid, nwid, address, network_type
                                 FROM  profile_networking
                                WHERE  pid IN {?} AND pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids) . ',
                                       network_type, nwid',
                               $pids, $visibility);

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }

    public function get($flags, $limit = null)
    {
        $nws = array();
        $nb = 0;
        foreach ($this->networks as $id => $nw) {
            // XXX hardcoded reference to web site index
            if (
                (($flags & self::NETWORKING_WEB) && $nw['network_type'] == 0)
                ||
                (! ($flags & self::NETWORKING_WEB))
            ) {
                $nws[$id] = $nw;
                ++$nb;
            }
            if ($nb >= $limit) {
                break;
            }
        }
        return PlIteratorUtils::fromArray($nws);
    }
}
// }}}
// {{{ class ProfilePhoto                             [ Field ]
class ProfilePhoto extends ProfileField
{
    public $pic;

    public function __construct(array $data)
    {
        if ($data == null || count($data) == 0) {
            $this->pic = null;
        } else {
            $this->pid = $data['pid'];
            $this->pic = PlImage::fromDATA($data['attach'],
                                           $data['attachmime'],
                                           $data['x'],
                                           $data['y']);
        }
    }

    public static function fetchData(array $pids, $visibility)
    {
        $data = XDB::iterator('SELECT  *
                                 FROM  profile_photos
                                WHERE  pid IN {?} AND attachmime IN (\'jpeg\', \'png\') AND pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids),
                               $pids, $visibility);

        return $data;
    }
}
// }}}
// {{{ class ProfileCorps                             [ Field ]
class ProfileCorps extends ProfileField
{
    public $original;
    public $current;
    public $rank;

    private function __construct(array $data)
    {
        $this->original = $data['original_corpsid'];
        $this->current  = $data['current_corpsid'];
        $this->rank     = $data['rankid'];
        $this->visibility = $data['corps_pub'];
    }

    public static function fetchData(array $pids, $visibility)
    {
        $data = XDB::iterator('SELECT  pid, original_corpsid, current_corpsid,
                                       rankid
                                 FROM  profile_corps
                                WHERE  pid IN {?} AND corps_pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids),
                                XDB::formatArray($pids),
                                XDB::formatArray($visibility)
                            );

        return $data;
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
            $this->addresses[] = new Address($addr);
        }
    }

    public function get($flags, $limit = null)
    {
        $res = array();
        $nb = 0;
        foreach ($this->addresses as $addr) {
            if ($addr->hasFlags($flags)) {
                $res[] = $addr;
                $nb++;
            }
            if ($limit != null && $nb == $limit) {
                break;
            }
        }
        return PlIteratorUtils::fromArray($res);
    }

    public static function fetchData(array $pids, $visibility)
    {
        $data = XDB::iterator('SELECT  pid, text, postalCode, type, latitude, longitude,
                                       flags, type
                                 FROM  profile_addresses
                                WHERE  pid in {?} AND pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids),
                               $pids, $visibility);

        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }

    public function addPhones($phones)
    {
        foreach ($phones as $phone) {
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

    private function __construct(PlIterator $phones)
    {
        while ($phone = $it->next()) {
            $this->phones[] = Phone::buildFromData($phone);
        }
    }

    public static function fetchData(array $pids, $visibility)
    {
        $data = XDB::iterator('SELECT  type, search, display, link_type, comment
                                 FROM  profile_phones
                                WHERE  pid IN {?} AND pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids),
                                 XDB::formatArray($pids),
                                 XDB::formatArray($visibility)
                             );
        return PlIteratorUtils::subIterator($data, PlIteratorUtils::arrayValueCallback('pid'));
    }
}
// }}}
// {{{ class ProfileJobs                              [ Field ]
class ProfileJobs extends ProfileField
{
    private $jobs = array();

    private function __construct(PlIterator $jobs)
    {
        while ($job = $jobs->next()) {
            $this->jobs[$job['id']] = Jobs::buildFromData($job);
        }
    }

    public static function fetchData(array $pids, $visibility)
    {
        $data = XDB::iterator('SELECT  id, pid, description, url,
                                       jobid, sectorid, subsctorid, subsubsectorid,
                                       IF(email_pub IN {?}, email, NULL) AS email
                                 FROM  profile_job
                                WHERE  pid IN {?} AND pub IN {?}
                             ORDER BY  ' . XDB::formatCustomOrder('pid', $pids) . ',
                                       id',
                                 $visibility, $pids, $visibility);
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
        return PlIteratorUtils::fromArray($jobs);
    }

    public function addPhones(array $phones)
    {
        foreach ($phones as $phone)
        {
            if ($phone->link_type == Phone::LINK_JOB && array_key_exists($phone->link_id, $this->jobs)) {
                $this->jobs[$phone->link_id]->addPhones($phone);
            }
        }
    }

    public static function addAddresses(array $addresses)
    {
        foreach ($addresses as $address)
        {
            if ($address->link_type == Address::LINK_JOB && array_key_exists($address->link_id, $this->jobs)) {
                $this->jobs[$address->link_id]->setAddress($address);
            }
        }
    }

    public static function addCompanies(array $companies)
    {
        foreach ($this->jobs as $job)
        {
            $job->setCompany($companies[$job->company_id]);
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
            $join = 'LEFT JOIN profile_jobs ON (profile_job.jobid = profile_job_enum.id)';
            $where = 'profile_jobs.pid IN ' . XDB::formatArray($pids);
        } else {
            $join = '';
            $where = '';
        }

        $it = XDB::iterator('SELECT  pje.id, pje.name, pje.acronmy, pje.url,
                                     pa.flags, pa.text, pa.postcode, pa.country,
                                     pa.link_type, pa.pub
                               FROM  profile_job_enum AS pje
                          LEFT JOIN  profile_addresses AS pa ON (pje.id = pa.jobid AND pa.type = \'hq\')
                                  ' . $join . '
                                  ' . $where);
        while ($row = $it->next()) {
            $cp = Company::buildFromData($row);
            $addr = Address::buildFromData($row);
            $cp->setAddress($addr);
            self::$companies[$row['id']] = $cp;
        }

        // TODO: add phones to addresses
        if (count($pids) == 0) {
            self::$fullload = true;
        }
    }

    static public function getCompany($id)
    {
        if (!array_key_exists($id, self::$companies)) {
            self::preload();
        }
        return self::$companies[$id];
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
