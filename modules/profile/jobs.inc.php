<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

class ProfileJob extends ProfileGeoloc
{
    private $pub;
    private $mail_new;
    private $mail;
    private $web;
    private $bool;
    private $checks;

    public function __construct()
    {
        $this->pub    = new ProfilePub();
        $this->mail
                      = $this->mail_new
                      = new ProfileEmail();
        $this->web    = new ProfileWeb();
        $this->bool   = new ProfileBool();
        $this->checks = array('web'      => array('w_web'),
                              'mail_new' => array('w_email_new'),
                              'mail'     => array('w_email'),
                              'pub'      => array('pub', 'w_email_pub'));
    }

    private function cleanJob(ProfilePage &$page, $jobid, array &$job, &$success)
    {
        $success = true;
        foreach ($this->checks as $obj=>&$fields) {
            $chk =& $this->$obj;
            foreach ($fields as $field) {
                if ($field == "w_email_new") {
                    if ($job['w_email'] == "new@example.org") {
                        $job['w_email'] = $job[$field];
                    }
                    continue;
                }
                $job[$field] = $chk->value($page, $field, $job[$field], $s);
                if (!$s) {
                    $success = false;
                    $job[$field . '_error'] = true;
                }
            }
        }
        if (!$job['sss_secteur_name']) {
            $res = XDB::query("SELECT  name
                                 FROM  profile_job_subsubsector_enum
                                WHERE  id = {?}",
                              $job['sss_secteur']);
            $job['sss_secteur_name'] = $res->fetchOneCell();
        } else {
            $res = XDB::query("SELECT  sectorid, subsectorid, id
                                 FROM  profile_job_subsubsector_enum
                                WHERE  name = {?}",
                              $job['sss_secteur_name']);
            if ($res->numRows() != 1) {
                $success = false;
                $job['sector_error'] = true;
            } else {
                list($job['secteur'], $job['ss_secteur'], $job['sss_secteur']) = $res->fetchOneRow();
            }
        }
        if ($job['name']) {
            $res = XDB::query("SELECT  id
                                 FROM  profile_job_enum
                                WHERE  name = {?}",
                              $job['name']);
            if ($res->numRows() != 1) {
                $user = S::user();
                $req = new EntrReq($user, $jobid, $job['name'], $job['acronym'], $job['hq_web'], $job['hq_email'], $job['hq_tel'], $job['hq_fax']);
                $req->submit();
                $job['jobid'] = null;
            } else {
                $job['jobid'] = $res->fetchOneCell();
            }
        }
        $job['w_adr']['pub'] = $this->pub->value($page, 'adr_pub', @$job['w_adr']['pub'], $s);
        $job['w_adr']['checked'] = $this->bool->value($page, 'adr_checked', @$job['w_adr']['checked'], $s);
        if (!isset($job['w_tel'])) {
            $job['w_tel'] = array();
        }
        $profiletel = new ProfilePhones('pro', $jobid);
        $job['w_tel'] = $profiletel->value($page, 'tel', $job['w_tel'], $s);
        unset($job['removed']);
        unset($job['new']);
        unset($job['w_adr']['changed']);
        unset($job['w_adr']['parsevalid']);
        unset($job['w_adr']['display']);
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        require_once('validations.inc.php');
        $entreprise = Validate::get_typed_requests(S::i('uid'), 'entreprise');
        $entr_val = 0;

        $init = false;
        if (is_null($value)) {
            $value = $page->values['jobs'];
            $init = true;
        }
        $success = true;
        foreach ($value as $key=>&$job) {
            $job['name'] = trim($job['name']);
            if (!$job['name']) {
                $job['tmp_name'] = $entreprise[$entr_val]->name;
                $entr_val ++;
            }
            if (@$job['removed']) {
                unset($value[$key]);
            }
        }
        foreach ($value as $key=>&$job) {
            $ls = true;
            $this->geolocAddress($job['w_adr'], $s);
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
        require_once('profil.func.inc.php');
        require_once('validations.inc.php');

        XDB::execute("DELETE FROM  profile_job
                            WHERE  uid = {?}",
                     S::i('uid'));
        XDB::execute("DELETE FROM  profile_phones
                            WHERE  uid = {?} AND link_type = 'pro'",
                     S::i('uid'));
        foreach ($value as $id=>&$job) {
            if ($job['w_email'] == "new@example.org") {
                $job['w_email'] = $job['w_email_new'];
            }
            if ($job['jobid']) {
                XDB::execute("INSERT INTO  profile_job (uid, id, functionid, description, sectorid, subsectorid,
                                                        subsubsectorid, email, url, pub, email_pub, jobid)
                                   VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})",
                             S::i('uid'), $id, $job['fonction'], $job['description'], $job['secteur'], $job['ss_secteur'],
                             $job['sss_secteur'], $job['w_email'], $job['w_web'], $job['pub'], $job['w_email_pub'], $job['jobid']);
            } else {
                XDB::execute("INSERT INTO  profile_job (uid, id, functionid, description, sectorid, subsectorid,
                                                        subsubsectorid, email, url, pub, email_pub)
                                   VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})",
                             S::i('uid'), $id, $job['fonction'], $job['description'], $job['secteur'], $job['ss_secteur'],
                             $job['sss_secteur'], $job['w_email'], $job['w_web'], $job['pub'], $job['w_email_pub']);
            }
            $profiletel = new ProfilePhones('pro', $id);
            $profiletel->saveTels('tel', $job['w_tel']);
        }
    }
}

class ProfileJobs extends ProfilePage
{
    protected $pg_template = 'profile/jobs.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['cv'] = null;
        $this->settings['corps'] = null;
        $this->settings['jobs'] = new ProfileJob();
        $this->watched = array('cv' => true, 'jobs' => true, 'corps' => true);
    }

    protected function _fetchData()
    {
        // Checkout the CV
        $res = XDB::query("SELECT  cv
                             FROM  auth_user_md5
                            WHERE  user_id = {?}",
                          S::i('uid'));
        $this->values['cv'] = $res->fetchOneCell();

        // Checkout the corps
        $res = XDB::query("SELECT  original_corpsid AS original, current_corpsid AS current,
                                   rankid AS rank, corps_pub AS pub
                             FROM  profile_corps
                            WHERE  uid = {?}",
                          S::i('uid'));
        $this->values['corps'] = $res->fetchOneAssoc();

        // Build the jobs tree
        $res = XDB::iterRow("SELECT  j.id, je.name, j.functionid, j.sectorid, j.subsectorid,
                                     j.subsubsectorid, j.description, e.adr1, e.adr2, e.adr3,
                                     e.postcode, e.city, e.cityid, e.region, e.regiontxt,
                                     e.country, gp.pays, gp.display,
                                     FIND_IN_SET('geoloc', flags),
                                     j.email, j.url, j.pub,
                                     e.adr_pub, j.email_pub,
                                     e.glat, e.glng, s.name
                               FROM  profile_job                   AS j
                          LEFT JOIN  profile_job_enum              AS je ON (j.jobid = je.id)
                          LEFT JOIN  entreprises                   AS e  ON (j.uid = e.uid AND j.id = e.entrid)
                          LEFT JOIN  geoloc_pays                   AS gp ON (gp.a2 = e.country)
                          LEFT JOIN  profile_job_subsubsector_enum AS s  ON (s.id = j.subsubsectorid)
                              WHERE  j.uid = {?}
                           ORDER BY  entrid", S::i('uid'));
        $this->values['jobs'] = array();
        while (list($id, $name, $function, $secteur, $ss_secteur, $sss_secteur, $description,
                    $w_adr1, $w_adr2, $w_adr3, $w_postcode, $w_city, $w_cityid,
                    $w_region, $w_regiontxt, $w_country, $w_countrytxt, $w_display,
                    $w_checked, $w_email, $w_web,
                    $pub, $w_adr_pub, $w_email_pub, $w_glat, $w_glng, $sss_secteur_name
                   ) = $res->next()) {
            $this->values['jobs'][] = array('id'               => $id,
                                            'name'             => $name,
                                            'fonction'         => $function,
                                            'secteur'          => $secteur,
                                            'ss_secteur'       => $ss_secteur,
                                            'sss_secteur'      => $sss_secteur,
                                            'sss_secteur_name' => $sss_secteur_name,
                                            'description'      => $description,
                                            'w_adr'            => array('adr1'        => $w_adr1,
                                                                        'adr2'        => $w_adr2,
                                                                        'adr3'        => $w_adr3,
                                                                        'postcode'    => $w_postcode,
                                                                        'city'        => $w_city,
                                                                        'cityid'      => $w_cityid,
                                                                        'region'      => $w_region,
                                                                        'regiontxt'   => $w_regiontxt,
                                                                        'country'     => $w_country,
                                                                        'countrytxt'  => $w_countrytxt,
                                                                        'display'     => $w_display,
                                                                        'pub'         => $w_adr_pub,
                                                                        'checked'     => $w_checked,
                                                                        'precise_lat' => $w_glat,
                                                                        'precise_lon' => $w_glng),
                                            'w_email'          => $w_email,
                                            'w_web'            => $w_web,
                                            'pub'              => $pub,
                                            'w_email_pub'      => $w_email_pub);
        }

        $res = XDB::iterator("SELECT  link_id AS jobid, tel_type AS type, pub, display_tel AS tel, comment
                                FROM  profile_phones
                               WHERE  uid = {?} AND link_type = 'pro'
                            ORDER BY  link_id",
                             S::i('uid'));
        $i = 0;
        $jobNb = count($this->values['jobs']);
        while ($tel = $res->next()) {
            $jobid = $tel['jobid'];
            unset($tel['jobid']);
            while ($i < $jobNb && $this->values['jobs'][$i]['id'] < $jobid) {
                $i++;
            }
            if ($i >= $jobNb) {
                break;
            }
            $job =& $this->values['jobs'][$i];
            if (!isset($job['w_tel'])) {
                $job['w_tel'] = array();
            }
            if ($job['id'] == $jobid) {
                $job['w_tel'][] = $tel;
            }
        }
        foreach ($this->values['jobs'] as $id=>&$job) {
            if (!isset($job['w_tel'])) {
                $job['w_tel'] = array();
            }
            unset($job['id']);
        }
    }

    protected function _saveData()
    {
        if ($this->changed['cv']) {
            XDB::execute("UPDATE  auth_user_md5
                             SET  cv = {?}
                           WHERE  user_id = {?}",
                         $this->values['cv'], S::i('uid'));
        }

        if ($this->changed['corps']) {
            XDB::execute("UPDATE  profile_corps
                             SET  original_corpsid = {?}, current_corpsid = {?},
                                  rankid = {?}, corps_pub = {?}
                           WHERE  uid = {?}",
                          $this->values['corps']['original'], $this->values['corps']['current'],
                          $this->values['corps']['rank'], $this->values['corps']['pub'], S::i('uid'));
        }
    }

    public function _prepare(PlPage &$page, $id)
    {
        require_once "emails.combobox.inc.php";
        fill_email_combobox($page);

        $res = XDB::query("SELECT  id, name AS label
                             FROM  profile_job_sector_enum");
        $page->assign('secteurs', $res->fetchAllAssoc());
        $res = XDB::query("SELECT  id, fonction_fr, FIND_IN_SET('titre', flags) AS title
                             FROM  fonctions_def
                         ORDER BY  id");
        $page->assign('fonctions', $res->fetchAllAssoc());

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
                                FROM  profile_corps_rank_enum");
        $page->assign('corps_rank', $res->fetchAllAssoc());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
