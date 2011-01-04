<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

class ProfileSettingJob implements ProfileSetting
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

    private function emptyJob()
    {
        $address = new Address();
        $phone = new Phone();
        return array(
            'id'               => '0',
            'jobid'            => '',
            'pub'              => 'ax',
            'name'             => '',
            'description'      => '',
            'w_url'            => '',
            'w_address'        => $address->toFormArray(),
            'w_email'          => '',
            'w_email_pub'      => 'ax',
            'w_email_new'      => '',
            'w_phone'          => array(0 => $phone->toFormArray()),
            'terms'            => array()
        );
    }

    private function fetchJobs(ProfilePage $page)
    {
        // Build the jobs tree
        $jobs  = XDB::fetchAllAssoc('SELECT  j.id, j.jobid, je.name,
                                             j.description, j.email AS w_email,
                                             j.email_pub AS w_email_pub,
                                             j.url AS w_url, j.pub
                                       FROM  profile_job      AS j
                                  LEFT JOIN  profile_job_enum AS je ON (j.jobid = je.id)
                                      WHERE  j.pid = {?}
                                   ORDER BY  j.id',
                                    $page->pid());

        if (empty($jobs)) {
            return array($this->emptyJob());
        }

        $compagnies = array();
        $backtrack = array();
        foreach ($jobs as $key=>$job) {
            $compagnies[] = $job['jobid'];
            $backtrack[$job['id']] = $key;
        }

        $it = Address::iterate(array($page->pid()), array(Address::LINK_JOB));
        while ($address = $it->next()) {
            $jobs[$address->id]['w_address'] = $address->toFormArray();
        }
        $it = Phone::iterate(array($page->pid()), array(Phone::LINK_JOB));
        while ($phone = $it->next()) {
            $jobs[$phone->linkId()]['w_phone'][$phone->id()] = $phone->toFormArray();
        }
        $res = XDB::iterator("SELECT  e.jtid, e.full_name, j.jid
                                FROM  profile_job_term_enum AS e
                          INNER JOIN  profile_job_term AS j USING(jtid)
                               WHERE  pid = {?}
                            ORDER BY  j.jid",
                             $page->pid());
        while ($term = $res->next()) {
            // $jid is the ID of the job among this user's jobs
            $jid = $term['jid'];
            if (!isset($backtrack[$jid])) {
                continue;
            }
            $job =& $jobs[$backtrack[$jid]];
            if (!isset($job['terms'])) {
                $job['terms'] = array();
            }
            $job['terms'][] = $term;
        }

        $phone = new Phone();
        $address = new Address();
        foreach ($jobs as $id => &$job) {
            if (!isset($job['w_phone'])) {
                $job['w_phone'] = array(0 => $phone->toFormArray());
            }
            if (!isset($job['w_address'])) {
                $job['w_address'] = $address->toFormArray();
            }

            $job['w_email_new'] = '';
            if (!isset($job['w_email_pub'])) {
                $job['w_email_pub'] = 'private';
            }
        }
        return $jobs;
    }

    private function cleanJob(ProfilePage &$page, $jobid, array &$job, &$success, $maxPublicity)
    {
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
        if (count($job['terms'])) {
            $termsid = array();
            foreach ($job['terms'] as $term) {
                if (!isset($term['full_name'])) {
                    $termsid[] = $term['jtid'];
                }
            }
            if (count($termsid)) {
                $res = XDB::query("SELECT  jtid, full_name
                                    FROM  profile_job_term_enum
                                   WHERE  jtid IN {?}",
                                 $termsid);
                $term_id_to_name = $res->fetchAllAssoc('jtid', false);
                foreach ($job['terms'] as &$term) {
                    if (!isset($term['full_name'])) {
                        $term['full_name'] = $term_id_to_name[$term['jtid']];
                    }
                }
            }
        }
        if ($job['name']) {
            $res = XDB::query("SELECT  id
                                 FROM  profile_job_enum
                                WHERE  name = {?}",
                              $job['name']);
            if ($res->numRows() != 1) {
                $req = new EntrReq(S::user(), $page->profile, $jobid, $job['name'], $job['hq_acronym'], $job['hq_url'],
                                   $job['hq_email'], $job['hq_fixed'], $job['hq_fax'], $job['hq_address']);
                $req->submit();
                $job['jobid'] = null;
                sleep(1);
            } else {
                $job['jobid'] = $res->fetchOneCell();
            }
        }

        if ($maxPublicity->isVisible($job['w_email_pub'])) {
            $job['w_email_pub'] = $maxPublicity->level();
        }
        $job['w_phone'] = Phone::formatFormArray($job['w_phone'], $s, $maxPublicity);

        unset($job['removed']);
        unset($job['new']);
    }



    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $entreprise = ProfileValidate::get_typed_requests($page->pid(), 'entreprise');
        $entr_val = 0;

        $init = false;
        if (is_null($value)) {
            $value = $this->fetchJobs($page);
            $init = true;
        }
        $success = true;
        foreach ($value as $key => $job) {
            $job['name'] = trim($job['name']);
            if ($job['name'] == '' && $entreprise[$entr_val]->id == $key) {
                $job['tmp_name'] = $entreprise[$entr_val]->name;
                ++$entr_val;
            } else if ($job['name'] == '') {
                if ($job['description'] == '' && $job['w_url'] == ''
                    && $job['w_address']['text'] == '' && $job['w_email'] == ''
                    && count($job['w_phone']) >= 1 && $job['w_phone'][0]['display'] == '') {
                    unset($value[$key]);
                    continue;
                }

                if (!$init) {
                    $job['name_error'] = true;
                    $success = false;
                }
            }

            if (isset($job['removed']) && $job['removed']) {
                if ($job['name'] == '' && $entreprise && isset($entreprise[$entr_val - 1])) {
                    $entreprise[$entr_val - 1]->clean();
                }
                unset($value[$key]);
                continue;
            }
            if (!isset($job['pub']) || !$job['pub']) {
                $job['pub'] = 'private';
            }
            $value[$key] = $job;
        }
        foreach ($value as $key => &$job) {
            $address = new Address($job['w_address']);
            $s = $address->format();
            $maxPublicity = new ProfileVisibility($job['pub']);
            if ($maxPublicity->isVisible($address->pub)) {
                $address->pub = $maxPublicity->level();
            }
            $job['w_address'] = $address->toFormArray();
            $this->cleanJob($page, $key, $job, $s, $maxPublicity);
            if (!$init) {
                $success = ($success && $s);
            }
        }
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        $deletePrivate = S::user()->isMe($page->owner) || S::admin();
        XDB::execute('DELETE FROM  pj, pjt
                            USING  profile_job      AS pj
                        LEFT JOIN  profile_job_term AS pjt ON (pj.pid = pjt.pid AND pj.id = pjt.jid)
                            WHERE  pj.pid = {?}' . (($deletePrivate) ? '' : ' AND pj.pub IN (\'public\', \'ax\')'),
                     $page->pid());
        Address::deleteAddresses($page->pid(), Address::LINK_JOB, null, $deletePrivate);
        Phone::deletePhones($page->pid(), Phone::LINK_JOB, null, $deletePrivate);
        $terms_values = array();
        foreach ($value as $id => &$job) {
            if (isset($job['name']) && $job['name']) {
                if (isset($job['jobid']) && $job['jobid']) {
                    XDB::execute('INSERT INTO  profile_job (pid, id, description, email,
                                                            url, pub, email_pub, jobid)
                                       VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                                 $page->pid(), $id, $job['description'], $job['w_email'],
                                 $job['w_url'], $job['pub'], $job['w_email_pub'], $job['jobid']);
                } else {
                    XDB::execute('INSERT INTO  profile_job (pid, id, description, email,
                                                            url, pub, email_pub)
                                       VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})',
                                 $page->pid(), $id, $job['description'], $job['w_email'],
                                 $job['w_url'], $job['pub'], $job['w_email_pub']);
                }
                $address = new Address(array_merge($job['w_address'],
                                                   array('pid' => $page->pid(),
                                                         'id' => $id,
                                                         'type' => Address::LINK_JOB)));
                $address->save();
                Phone::savePhones($job['w_phone'], $page->pid(), Phone::LINK_JOB, $id);
                if (isset($job['terms'])) {
                    foreach ($job['terms'] as $term) {
                        $terms_values[] = XDB::format('({?}, {?}, {?}, {?})',
                                                      $page->pid(), $id, $term['jtid'], "original");
                    }
                }
            }
        }
        if (count($terms_values) > 0) {
            XDB::rawExecute('INSERT INTO  profile_job_term (pid, jid, jtid, computed)
                                  VALUES  ' . implode(', ', $terms_values) . '
                 ON DUPLICATE KEY UPDATE  computed = VALUES(computed)');
        }
        if (S::user()->isMe($page->owner) && count($value) > 1) {
            Platal::page()->trigWarning('Attention, tu as plusieurs emplois sur ton profil. Pense à supprimer ceux qui sont obsolètes.');
        }
    }

    public function getText($value)
    {
        static $pubs = array('public' => 'publique', 'ax' => 'annuaire AX', 'private' => 'privé');
        $jobs = array();
        foreach ($value as $id => $job) {
            $address = Address::formArrayToString(array($job['w_address']));
            $phones = Phone::formArrayToString($job['w_phone']);
            $jobs[$id] = $job['name'];
            $jobs[$id] .= ($job['description'] ? (', ' . $job['description']) : '');
            $jobs[$id] .= ' (affichage ' . $pubs[$job['pub']];
            if (count($job['terms'])) {
                $terms = array();
                foreach ($job['terms'] as $term) {
                    $terms[] = $term['full_name'];
                }
                $jobs[$id] .= ', mots-clefs : ' . implode(', ', $terms);
            }
            if ($job['w_url']) {
                $jobs[$id] .= ', page perso : ' . $job['w_url'];
            }
            if ($address) {
                $jobs[$id] .= ', adresse : ' . $address;
            }
            if ($job['w_email']) {
                $jobs[$id] .= ', email : ' . $job['w_email'];
            }
            if ($phones) {
                $jobs[$id] .= ', téléphones : ' . $phones;
            }
            $jobs[$id] .= ')';
        }
        return implode(' ; ' , $jobs);
    }
}

class ProfileSettingCorps implements ProfileSetting
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $res = XDB::query('SELECT  c.original_corpsid AS original, e.name AS originalText,
                                       c.current_corpsid AS current, c.rankid AS rank, c.corps_pub AS pub
                                 FROM  profile_corps      AS c
                           INNER JOIN  profile_corps_enum AS e ON (c.original_corpsid = e.id)
                                WHERE  c.pid = {?}',
                            $page->pid());
            return $res->fetchOneAssoc();
        }
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        if (!S::user()->isMe($page->owner)) {
            XDB::execute('INSERT INTO  profile_corps (original_corpsid, current_corpsid, rankid, corps_pub, pid)
                               VALUES  ({?}, {?}, {?}, {?}, {?})
              ON DUPLICATE KEY UPDATE  original_corpsid = VALUES(original_corpsid), current_corpsid = VALUES(current_corpsid),
                                       rankid = VALUES(rankid), corps_pub = VALUES(corps_pub)',
                          $value['original'], $value['current'], $value['rank'], $value['pub'], $page->pid());
        } else {
            XDB::execute('INSERT INTO  profile_corps (current_corpsid, rankid, corps_pub, pid)
                               VALUES  ({?}, {?}, {?}, {?})
              ON DUPLICATE KEY UPDATE  current_corpsid = VALUES(current_corpsid),
                                       rankid = VALUES(rankid), corps_pub = VALUES(corps_pub)',
                          $value['current'], $value['rank'], $value['pub'], $page->pid());
        }
    }

    public function getText($value)
    {
        static $pubs = array('public' => 'publique', 'ax' => 'annuaire AX', 'private' => 'privé');
        $corpsList = DirEnum::getOptions(DirEnum::CORPS);
        $rankList  = DirEnum::getOptions(DirEnum::CORPSRANKS);
        return $corpsList[$value['current']] . ', ' . $corpsList[$value['rank']] . ' ('
            . 'corps d\'origine : ' . $corpsList[$value['original']] . ', affichage ' . $pubs[$value['pub']] . ')';
    }
}

class ProfilePageJobs extends ProfilePage
{
    protected $pg_template = 'profile/jobs.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        if (S::user()->checkPerms(User::PERM_DIRECTORY_PRIVATE)) {
            $this->settings['cv'] = null;
        }
        $this->settings['corps'] = new ProfileSettingCorps();
        $this->settings['jobs'] = new ProfileSettingJob();
        $this->watched = array('cv' => true, 'jobs' => true, 'corps' => true);
    }

    protected function _fetchData()
    {
        if (S::user()->checkPerms(User::PERM_DIRECTORY_PRIVATE)) {
            // Checkout the CV
            $res = XDB::query("SELECT  cv
                                 FROM  profiles
                                WHERE  pid = {?}",
                              $this->pid());
            $this->values['cv'] = $res->fetchOneCell();
        }
    }

    protected function _saveData()
    {
        if (S::user()->checkPerms(User::PERM_DIRECTORY_PRIVATE)) {
            if ($this->changed['cv']) {
                XDB::execute("UPDATE  profiles
                                 SET  cv = {?}
                               WHERE  pid = {?}",
                             $this->values['cv'], $this->pid());
            }
        }
    }

    public function _prepare(PlPage &$page, $id)
    {
        require_once 'emails.combobox.inc.php';
        fill_email_combobox($page, $this->owner);

        if (!S::user()->isMe($this->owner)) {
            $res = XDB::iterator('SELECT  id, name
                                    FROM  profile_corps_enum
                                ORDER BY  id = 1 DESC, name');
            $page->assign('original_corps', $res->fetchAllAssoc());
        }

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
