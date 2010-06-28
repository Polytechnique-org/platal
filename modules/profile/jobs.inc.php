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

class ProfileSettingJob extends ProfileSettingGeocoding
{
    private $pub;
    private $email_new;
    private $email;
    private $url;
    private $bool;
    private $checks;

    public function __construct()
    {
        $this->pub    = new ProfileSettingPub();
        $this->email
                      = $this->email_new
                      = new ProfileSettingEmail();
        $this->url    = new ProfileSettingWeb();
        $this->bool   = new ProfileSettingBool();
        $this->checks = array('url'      => array('w_url'),
                              'email'    => array('w_email'),
                              'pub'      => array('pub', 'w_email_pub'),
                             );
    }

    public function emptyJob()
    {
        return array(
            'id'               => '0',
            'jobid'            => '',
            'pub'              => 'private',
            'name'             => '',
            'hq_acronym'       => '',
            'hq_url'           => '',
            'hq_email'         => '',
            'hq_address'       => array(
                'text'                    => '',
                'accuracy'                => '',
                'postalText'              => '',
                'postalCode'              => '',
                'administrativeAreaId'    => '',
                'subAdministrativeAreaId' => '',
                'localityId'              => '',
                'countryId'               => '',
                'latitude'                => '',
                'longitude'               => '',
                'north'                   => '',
                'south'                   => '',
                'east'                    => '',
                'west'                    => '',
                'cedex'                   => '',
                'updateTime'              => '',
                'changed'                 => '0',
                'removed'                 => '0',
            ),
            'hq_phone'         => '',
            'hq_fax'           => '',
            'subSubSectorName' => null,
            'sector'           => '0',
            'subSector'        => '0',
            'subSubSector'     => '0',
            'description'      => '',
            'w_url'            => '',
            'w_address'        => array(
                'pub'                     => 'private',
                'text'                    => '',
                'accuracy'                => '',
                'postalText'              => '',
                'postalCode'              => '',
                'administrativeAreaId'    => '',
                'subAdministrativeAreaId' => '',
                'localityId'              => '',
                'countryId'               => '',
                'latitude'                => '',
                'longitude'               => '',
                'north'                   => '',
                'south'                   => '',
                'east'                    => '',
                'west'                    => '',
                'cedex'                   => '',
                'updateTime'              => '',
                'changed'                 => '0',
                'removed'                 => '0',
            ),
            'w_email'          => '',
            'w_email_pub'      => 'private',
            'w_email_new'      => '',
            'w_phone'          => array(0 => array(
                'type'    => 'fixed',
                'tel'     => '',
                'pub'     => 'private',
                'comment' => '',
            )),
        );
    }

    private function cleanJob(ProfilePage &$page, $jobid, array &$job, &$success)
    {
        $success = true;
        if ($job['w_email'] == "new@example.org") {
            $job['w_email'] = $job['w_email_new'];
        }
        foreach ($this->checks as $obj=>&$fields) {
            $chk =& $this->$obj;
            foreach ($fields as $field) {
                $job[$field] = $chk->value($page, $field, $job[$field], $s);
                if (!$s) {
                    $success = false;
                    $job[$field . '_error'] = true;
                }
            }
        }
        if (!$job['subSubSectorName']) {
            $res = XDB::query("SELECT  name
                                 FROM  profile_job_subsubsector_enum
                                WHERE  id = {?}",
                              $job['subSubSector']);
            $job['subSubSectorName'] = $res->fetchOneCell();
        } else {
            $res = XDB::query("SELECT  sectorid, subsectorid, id
                                 FROM  profile_job_subsubsector_enum
                                WHERE  name = {?}",
                              $job['subSubSectorName']);
            if ($res->numRows() != 1) {
                $success = false;
                $job['sector_error'] = true;
            } else {
                list($job['sector'], $job['subSector'], $job['subSubSector']) = $res->fetchOneRow();
            }
        }
        if ($job['name']) {
            $res = XDB::query("SELECT  id
                                 FROM  profile_job_enum
                                WHERE  name = {?}",
                              $job['name']);
            if ($res->numRows() != 1) {
                $this->geocodeAddress($job['hq_address'], $s);
                if (!$s) {
                    $gmapsGeocoder = new GMapsGeocoder();
                    $job['hq_address'] = $gmapsGeocoder->stripGeocodingFromAddress($job['hq_address']);
                }
                $req = new EntrReq(S::user(), $page->profile, $jobid, $job['name'], $job['hq_acronym'], $job['hq_url'],
                                   $job['hq_email'], $job['hq_fixed'], $job['hq_fax'], $job['hq_address']);
                $req->submit();
                $job['jobid'] = null;
            } else {
                $job['jobid'] = $res->fetchOneCell();
            }
        }
        $job['w_address']['pub'] = $this->pub->value($page, 'address_pub', $job['w_address']['pub'], $s);
        if (!isset($job['w_phone'])) {
            $job['w_phone'] = array();
        }
        $profiletel = new ProfileSettingPhones('pro', $jobid);
        $job['w_phone'] = $profiletel->value($page, 'tel', $job['w_phone'], $s);

        unset($job['removed']);
        unset($job['new']);
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        require_once 'validations.inc.php';
        $entreprise = ProfileValidate::get_typed_requests($page->pid(), 'entreprise');
        $entr_val = 0;

        $init = false;
        if (is_null($value)) {
            $value = $page->values['jobs'];
            $init = true;
        }
        $success = true;
        foreach ($value as $key => &$job) {
            $job['name'] = trim($job['name']);
            if ($job['name'] == '' && $entreprise) {
                $job['tmp_name'] = $entreprise[$entr_val]->name;
                ++$entr_val;
            } else if ($job['name'] == '') {
                $job['name_error'] = true;
                $success = false;
            }

            if (isset($job['removed']) && $job['removed']) {
                if ($job['name'] == '' && $entreprise) {
                    $entreprise[$entr_val - 1]->clean();
                }
                array_splice($value, $key, 1);
            }
        }
        foreach ($value as $key => &$job) {
            $ls = true;
            $this->geocodeAddress($job['w_address'], $s);
            $ls = ($ls && $s);
            $this->cleanJob($page, $key, $job, $s);
            $ls = ($ls && $s);
            if (!$init) {
                $success = ($success && $ls);
            }
        }
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        // TODO: use address and phone classes to update profile_job_enum and profile_phones once they are done.

        require_once 'profil.func.inc.php';

        XDB::execute("DELETE FROM  profile_job
                            WHERE  pid = {?}",
                     $page->pid());
        XDB::execute("DELETE FROM  profile_addresses
                            WHERE  pid = {?} AND type = 'job'",
                     $page->pid());
        XDB::execute("DELETE FROM  profile_phones
                            WHERE  pid = {?} AND link_type = 'pro'",
                     $page->pid());
        foreach ($value as $id => &$job) {
            if (isset($job['name']) && $job['name']) {
                if (isset($job['jobid']) && $job['jobid']) {
                    XDB::execute("INSERT INTO  profile_job (pid, id, description, sectorid, subsectorid,
                                                            subsubsectorid, email, url, pub, email_pub, jobid)
                                       VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})",
                                 $page->pid(), $id, $job['description'], $job['sector'], $job['subSector'],
                                 $job['subSubSector'], $job['w_email'], $job['w_url'], $job['pub'], $job['w_email_pub'], $job['jobid']);
                } else {
                    XDB::execute("INSERT INTO  profile_job (pid, id, description, sectorid, subsectorid,
                                                            subsubsectorid, email, url, pub, email_pub)
                                       VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})",
                                 $page->pid(), $id, $job['description'], $job['sector'], $job['subSector'],
                                 $job['subSubSector'], $job['w_email'], $job['w_url'], $job['pub'], $job['w_email_pub']);
                }
                $address = new ProfileSettingAddress();
                $address->saveAddress($page->pid(), $id, $job['w_address'], 'job');
                $profiletel = new ProfileSettingPhones('pro', $id);
                $profiletel->saveTels($page->pid(), 'tel', $job['w_phone']);
            }
        }
    }
}

class ProfileSettingJobs extends ProfilePage
{
    protected $pg_template = 'profile/jobs.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['cv'] = null;
        $this->settings['corps'] = null;
        $this->settings['jobs'] = new ProfileSettingJob();
        $this->watched = array('cv' => true, 'jobs' => true, 'corps' => true);
    }

    protected function _fetchData()
    {
        // Checkout the CV
        $res = XDB::query("SELECT  cv
                             FROM  profiles
                            WHERE  pid = {?}",
                          $this->pid());
        $this->values['cv'] = $res->fetchOneCell();

        // Checkout the corps
        $res = XDB::query("SELECT  original_corpsid AS original, current_corpsid AS current,
                                   rankid AS rank, corps_pub AS pub
                             FROM  profile_corps
                            WHERE  pid = {?}",
                        $this->pid());
        $this->values['corps'] = $res->fetchOneAssoc();

        // Build the jobs tree
        $res = XDB::iterRow("SELECT  j.id, j.jobid, je.name, j.sectorid, j.subsectorid, j.subsubsectorid,
                                     s.name, j.description, j.email, j.email_pub, j.url, j.pub,
                                     je.acronym, je.url, je.email,
                                     aw.accuracy, aw.text, aw.postalText, aw.postalCode, aw.localityId,
                                     aw.subAdministrativeAreaId, aw.administrativeAreaId, aw.countryId,
                                     aw.latitude, aw.longitude, aw.pub, aw.updateTime,
                                     aw.north, aw.south, aw.east, aw.west,
                                     ah.accuracy, ah.text, ah.postalText, ah.postalCode, ah.localityId,
                                     ah.subAdministrativeAreaId, ah.administrativeAreaId, ah.countryId,
                                     ah.latitude, ah.longitude, ah.pub, ah.updateTime,
                                     ah.north, ah.south, ah.east, ah.west
                               FROM  profile_job                   AS j
                          LEFT JOIN  profile_job_enum              AS je ON (j.jobid = je.id)
                          LEFT JOIN  profile_job_subsubsector_enum AS s  ON (s.id = j.subsubsectorid)
                          LEFT JOIN  profile_addresses             AS aw ON (aw.pid = j.pid AND aw.type = 'job'
                                                                             AND aw.id = j.id)
                          LEFT JOIN  profile_addresses             AS ah ON (ah.jobid = j.jobid AND ah.type = 'hq')
                              WHERE  j.pid = {?}
                           ORDER BY  j.id",
                            $this->pid());
        $this->values['jobs'] = array();

        if ($res->numRows() > 0) {
            while (list($id, $jobid, $name, $sector, $subSector, $subSubSector,
                        $subSubSectorName, $description, $w_email, $w_emailPub, $w_url, $pub,
                        $hq_acronym, $hq_url, $hq_email,
                        $w_accuracy, $w_text, $w_postalText, $w_postalCode, $w_localityId,
                        $w_subAdministrativeAreaId, $w_administrativeAreaId, $w_countryId,
                        $w_latitude, $w_longitude, $w_pub, $w_updateTime,
                        $w_north, $w_south, $w_east, $w_west,
                        $hq_accuracy, $hq_text, $hq_postalText, $hq_postalCode, $hq_localityId,
                        $hq_subAdministrativeAreaId, $hq_administrativeAreaId, $hq_countryId,
                        $hq_latitude, $hq_longitude, $hq_pub, $hq_updateTime,
                        $hq_north, $hq_south, $hq_east, $hq_west,
                       ) = $res->next()) {
                $this->values['jobs'][] = array(
                    'id'               => $id,
                    'jobid'            => $jobid,
                    'name'             => $name,
                    'sector'           => $sector,
                    'subSector'        => $subSector,
                    'subSubSector'     => $subSubSector,
                    'subSubSectorName' => $subSubSectorName,
                    'description'      => $description,
                    'pub'              => $pub,
                    'w_email'          => $w_email,
                    'w_email_pub'      => $w_emailPub,
                    'w_url'            => $w_url,
                    'hq_acronym'       => $hq_acronym,
                    'hq_url'           => $hq_url,
                    'hq_email'         => $hq_email,
                    'w_address'        => array(
                        'accuracy'                => $w_accuracy,
                        'text'                    => $w_text,
                        'postalText'              => $w_postalText,
                        'postalCode'              => $w_postalCode,
                        'localityId'              => $w_localityId,
                        'subAdministrativeAreaId' => $w_subAdministrativeAreaId,
                        'administrativeAreaId'    => $w_administrativeAreaId,
                        'countryId'               => $w_countryId,
                        'latitude'                => $w_latitude,
                        'longitude'               => $w_longitude,
                        'pub'                     => $w_pub,
                        'updateTime'              => $w_updateTime,
                        'north'                   => $w_north,
                        'south'                   => $w_south,
                        'east'                    => $w_east,
                        'west'                    => $w_west,
                    ),
                    'hq_address'       => array(
                        'accuracy'                => $hq_accuracy,
                        'text'                    => $hq_text,
                        'postalText'              => $hq_postalText,
                        'postalCode'              => $hq_postalCode,
                        'localityId'              => $hq_localityId,
                        'subAdministrativeAreaId' => $hq_subAdministrativeAreaId,
                        'administrativeAreaId'    => $hq_administrativeAreaId,
                        'countryId'               => $hq_countryId,
                        'latitude'                => $hq_latitude,
                        'longitude'               => $hq_longitude,
                        'pub'                     => $hq_pub,
                        'updateTime'              => $hq_updateTime,
                        'north'                   => $hq_north,
                        'south'                   => $hq_south,
                        'east'                    => $hq_east,
                        'west'                    => $hq_west,
                    ),
                );
            }

            $res = XDB::iterator("SELECT  link_id AS jobid, tel_type AS type, pub, display_tel AS tel, comment
                                    FROM  profile_phones
                                   WHERE  pid = {?} AND link_type = 'pro'
                                ORDER BY  link_id",
                                 $this->pid());
            $i = 0;
            $jobNb = count($this->values['jobs']);
            while ($phone = $res->next()) {
                $jobid = $phone['jobid'];
                while ($i < $jobNb && $this->values['jobs'][$i]['id'] < $jobid) {
                    $i++;
                }
                if ($i >= $jobNb) {
                    break;
                }
                $job =& $this->values['jobs'][$i];
                if (!isset($job['w_phone'])) {
                    $job['w_phone'] = array();
                }
                if ($job['id'] == $jobid) {
                    $job['w_phone'][] = $phone;
                }
            }
            foreach ($this->values['jobs'] as $id => &$job) {
                if (!isset($job['w_phone'])) {
                    $job['w_phone'] = array(
                        0 => array(
                            'type'    => 'fixed',
                            'tel'     => '',
                            'pub'     => 'private',
                            'comment' => '',
                        )
                    );
                }
            }
 
            $job['w_email_new'] = '';
            if (!isset($job['hq_phone'])) {
                $job['hq_phone'] = '';
            }
            if (!isset($job['hq_fax'])) {
                $job['hq_fax'] = '';
            }
            if (!isset($job['w_email_pub'])) {
                $job['w_email_pub'] = 'private';
            }
            if (!$job['hq_address']['text']) {
                $job['hq_address'] = array(
                    'text'                    => '',
                    'accuracy'                => '',
                    'postalText'              => '',
                    'postalCode'              => '',
                    'administrativeAreaId'    => '',
                    'subAdministrativeAreaId' => '',
                    'localityId'              => '',
                    'countryId'               => '',
                    'latitude'                => '',
                    'longitude'               => '',
                    'north'                   => '',
                    'south'                   => '',
                    'east'                    => '',
                    'west'                    => '',
                    'cedex'                   => '',
                    'updateTime'              => '',
                    'changed'                 => '0',
                    'removed'                 => '0',
                );
            }
            $job['w_address']['cedex'] = '';
            $job['w_address']['changed'] = '0';
            $job['w_address']['removed'] = '0';
        } else {
            $this->values['jobs'][] = $this->settings['jobs']->emptyJob();
        }
    }

    protected function _saveData()
    {
        if ($this->changed['cv']) {
            XDB::execute("UPDATE  profiles
                             SET  cv = {?}
                           WHERE  pid = {?}",
                         $this->values['cv'], $this->pid());
        }

        if ($this->changed['corps']) {
            XDB::execute('REPLACE INTO  profile_corps (original_corpsid, current_corpsid, rankid, corps_pub, pid)
                                VALUES  ({?}, {?}, {?}, {?}, {?})',
                          $this->values['corps']['original'], $this->values['corps']['current'],
                          $this->values['corps']['rank'], $this->values['corps']['pub'], $this->pid());
        }
    }

    public function _prepare(PlPage &$page, $id)
    {
        require_once 'emails.combobox.inc.php';
        fill_email_combobox($page, $this->owner, $this->profile);

        $res = XDB::query("SELECT  id, name AS label
                             FROM  profile_job_sector_enum");
        $page->assign('sectors', $res->fetchAllAssoc());

        $res = XDB::iterator("SELECT  id, name
                                FROM  profile_corps_enum
                            ORDER BY  id = 1 DESC, name");
        $page->assign('original_corps', $res->fetchAllAssoc());

        $res = XDB::iterator("SELECT  id, name
                                FROM  profile_corps_enum
                               WHERE  still_exists = 1
                            ORDER BY  id = 1 DESC, name");
        $page->assign('current_corps', $res->fetchAllAssoc());

        $res = XDB::iterator("SELECT  id, name
                                FROM  profile_corps_rank_enum
                            ORDER BY  id = 1 DESC, name");
        $page->assign('corps_rank', $res->fetchAllAssoc());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
