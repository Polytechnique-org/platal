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
    private $tel;
    private $bool;
    private $checks;

    public function __construct()
    {
        $this->pub  = new ProfilePub();
        $this->mail
                    = $this->mail_new
                    = new ProfileEmail();
        $this->web  = new ProfileWeb();
        $this->tel  = new ProfileTel();
        $this->bool = new ProfileBool();
        $this->checks = array('web' => array('web'),
                              'mail_new' => array('email_new'),
                              'mail' => array('email'),
                              'tel' => array('tel', 'fax', 'mobile'),
                              'pub' => array('pub', 'tel_pub', 'email_pub'));
    }

    private function cleanJob(ProfilePage &$page, array &$job, &$success)
    {
        $success = true;
        foreach ($this->checks as $obj=>&$fields) {
            $chk =& $this->$obj;
            foreach ($fields as $field) {
                if ($field == "email_new") {
                    if ($job['email'] == "new@new.new") {
                        $job['email'] = $job[$field];
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
        $job['adr']['pub'] = $this->pub->value($page, 'adr_pub', @$job['adr']['pub'], $s);
        $job['adr']['checked'] = $this->bool->value($page, 'adr_checked', @$job['adr']['checked'], $s);
        unset($job['removed']);
        unset($job['new']);
        unset($job['adr']['changed']);
        unset($job['adr']['parsevalid']);
        unset($job['adr']['display']);
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $init = false;
        if (is_null($value)) {
            $value = $page->values['jobs'];
            $init = true;
        }
        $success = true;
        foreach ($value as $key=>&$job) {
            if (@$job['removed'] || !trim($job['name'])) {
                unset($value[$key]);
            }
        }
        foreach ($value as $key=>&$job) {
            $ls = true;
            $this->geolocAddress($job['adr'], $s);
            $ls = ($ls && $s);
            $this->cleanJob($page, $job, $s);
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
        XDB::execute("DELETE FROM  entreprises
                            WHERE  uid = {?}",
                     S::i('uid'));
        XDB::execute("DELETE FROM  profile_phones
                            WHERE  uid = {?} AND link_type = 'pro'",
                     S::i('uid'));
        $i = 0;
        foreach ($value as &$job) {
            if ($job['email'] == "new@new.new") {
                $job['email'] = $job['email_new'];
            }

            XDB::execute("INSERT INTO  entreprises (uid, entrid, entreprise, secteur, ss_secteur,
                                                    fonction, poste, adr1, adr2, adr3, postcode,
                                                    city, cityid, country, region, regiontxt,
                                                    email, web,
                                                    pub, adr_pub, email_pub, flags,
                                                    glat, glng)
                               VALUES  ({?}, {?}, {?}, {?}, {?},
                                        {?}, {?}, {?}, {?}, {?}, {?},
                                        {?}, {?}, {?}, {?}, {?},
                                        {?}, {?},
                                        {?}, {?}, {?}, {?},
                                        {?}, {?})",
                         S::i('uid'), $i, $job['name'], $job['secteur'], $job['ss_secteur'],
                         $job['fonction'], $job['poste'], $job['adr']['adr1'], $job['adr']['adr2'], $job['adr']['adr3'],
                         $job['adr']['postcode'],
                         $job['adr']['city'], $job['adr']['cityid'], $job['adr']['country'], $job['adr']['region'], 
                         $job['adr']['regiontxt'],
                         $job['email'], $job['web'],
                         $job['pub'], $job['adr']['pub'], $job['email_pub'],
                         $job['adr']['checked'] ? 'geoloc' : '', $job['adr']['precise_lat'],
                         $job['adr']['precise_lon']);
            if ($job['tel'] != '') {
                XDB::execute("INSERT INTO  profile_phones (uid, link_type, link_id, tel_id,
                                                      tel_type, search_tel, display_tel, pub)
                                   VALUES  ({?}, 'pro', {?}, 0,
                                            'fixed', {?}, {?}, {?})",
                             S::i('uid'), $i, format_phone_number($job['tel']), $job['tel'], $job['tel_pub']);
            }
            if ($job['fax'] != '') {
                XDB::execute("INSERT INTO  profile_phones (uid, link_type, link_id, tel_id,
                                                      tel_type, search_tel, display_tel, pub)
                                   VALUES  ({?}, 'pro', {?}, 1,
                                            'fax', {?}, {?}, {?})",
                             S::i('uid'), $i, format_phone_number($job['fax']), $job['fax'], $job['tel_pub']);
            }
            if ($job['mobile'] != '') {
                XDB::execute("INSERT INTO  profile_phones (uid, link_type, link_id, tel_id,
                                                      tel_type, search_tel, display_tel, pub)
                                   VALUES  ({?}, 'pro', {?}, 2,
                                            'mobile', {?}, {?}, {?})",
                             S::i('uid'), $i, format_phone_number($job['mobile']), $job['mobile'], $job['tel_pub']);
            }
            $i++;
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
        $this->settings['jobs'] = new ProfileJob();
        $this->watched['cv'] = $this->watched['jobs'] = true;
    }

    protected function _fetchData()
    {
        // Checkout the CV
        $res = XDB::query("SELECT  cv
                             FROM  auth_user_md5
                            WHERE  user_id = {?}",
                          S::i('uid'));
        $this->values['cv'] = $res->fetchOneCell();

        // Build the jobs tree
        $res = XDB::iterRow("SELECT  e.entreprise, e.secteur, e.ss_secteur,
                                     e.fonction, e.poste, e.adr1, e.adr2, e.adr3,
                                     e.postcode, e.city, e.cityid, e.region, e.regiontxt,
                                     e.country, gp.pays, gp.display,
                                     FIND_IN_SET('geoloc', flags),
                                     e.email, e.web, e.pub,
                                     e.adr_pub, e.email_pub,
                                     e.glat AS precise_lat, e.glng AS precise_lon,
                                     tt.display_tel AS tel, tt.pub AS tel_pub,
                                     tf.display_tel AS fax, tm.display_tel AS mobile
                               FROM  entreprises AS e
                          LEFT JOIN  geoloc_pays AS gp ON(gp.a2 = e.country)
                          LEFT JOIN  profile_phones AS tt ON(tt.uid = e.uid AND tt.link_type = 'pro' AND tt.link_id = entrid AND tt.tel_id = 0)
                          LEFT JOIN  profile_phones AS tf ON(tf.uid = e.uid AND tf.link_type = 'pro' AND tf.link_id = entrid AND tf.tel_id = 1)
                          LEFT JOIN  profile_phones AS tm ON(tm.uid = e.uid AND tm.link_type = 'pro' AND tm.link_id = entrid AND tm.tel_id = 2)
                              WHERE  e.uid = {?} AND entreprise != ''
                           ORDER BY  entrid", S::i('uid'));
        $this->values['jobs'] = array();
        while (list($name, $secteur, $ss_secteur, $fonction, $poste,
                    $adr1, $adr2, $adr3, $postcode, $city, $cityid,
                    $region, $regiontxt, $country, $countrytxt, $display,
                    $checked, $email, $web,
                    $pub, $adr_pub, $email_pub, $glat, $glng,
                    $tel, $tel_pub, $fax, $mobile) = $res->next()) {
            $this->values['jobs'][] = array('name'       => $name,
                                            'secteur'    => $secteur,
                                            'ss_secteur' => $ss_secteur,
                                            'fonction'   => $fonction,
                                            'poste'      => $poste,
                                            'adr'        => array('adr1'       => $adr1,
                                                                  'adr2'       => $adr2,
                                                                  'adr3'       => $adr3,
                                                                  'postcode'   => $postcode,
                                                                  'city'       => $city,
                                                                  'cityid'     => $cityid,
                                                                  'region'     => $region,
                                                                  'regiontxt'  => $regiontxt,
                                                                  'country'    => $country,
                                                                  'countrytxt' => $countrytxt,
                                                                  'display'    => $display,
                                                                  'pub'        => $adr_pub,
                                                                  'checked'    => $checked,
                                                                  'precise_lat'=> $glat,
                                                                  'precise_lon'=> $glng),
                                            'tel'        => $tel,
                                            'fax'        => $fax,
                                            'mobile'     => $mobile,
                                            'email'      => $email,
                                            'web'        => $web,
                                            'pub'        => $pub,
                                            'tel_pub'    => $tel_pub,
                                            'email_pub'  => $email_pub);
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
    }

    public function _prepare(PlatalPage &$page, $id)
    {
        require_once "emails.combobox.inc.php";
        fill_email_combobox($page);

        $page->assign('secteurs', XDB::iterator("SELECT  id, label
                                                   FROM  emploi_secteur"));
        $page->assign('fonctions', XDB::iterator("SELECT  id, fonction_fr, FIND_IN_SET('titre', flags) AS title
                                                    FROM  fonctions_def
                                                ORDER BY  id"));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
