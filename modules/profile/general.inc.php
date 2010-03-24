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

class ProfileSettingSearchNames implements ProfileSetting
{
    private $private_name_end;
    private $search_names;

    private function matchWord($old, $new, $newLen)
    {
        return ($i = strpos($old, $new)) !== false
            && ($i == 0 || $old{$i-1} == ' ')
            && ($i + $newLen == strlen($old) || $old{$i + $newLen} == ' ');
    }

    private function prepareField($value)
    {
        $value = mb_strtoupper($value);
        return preg_replace('/[^A-Z]/', ' ', $value);
    }

    private function prepare(ProfilePage &$page, $field, $value, $init, &$success)
    {
        $success = true;
        $ini     = $this->prepareField($init);
        $new     = $this->prepareField($value);
        $newLen  = strlen($new);
        $success = $this->matchWord($ini, $new, $newLen)
                   || ($field == 'lastname' && $new == 'DE ' . $ini);
        if (!$success) {
            $field = strtolower($field);
            Platal::page()->trigError("Le " . $field . " que tu as choisi (" . $value .
                                      ") est trop loin de ton " . $field . " initial (" . $init . ").");
        }
        return $success ? $value : $init;
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success     = true;
        $success_tmp = true;

        if (is_null($value)) {
            $sn_all = XDB::iterator("SELECT  CONCAT(sn.particle, sn.name) AS name,
                                             sn.particle, sn.typeid, e.type, e.name AS type_name,
                                             FIND_IN_SET('has_particle', e.flags) AS has_particle,
                                             FIND_IN_SET('always_displayed', e.flags) AS always_displayed,
                                             FIND_IN_SET('public', e.flags) AS pub
                                       FROM  profile_name      AS sn
                                 INNER JOIN  profile_name_enum AS e  ON (e.id = sn.typeid)
                                      WHERE  sn.pid = {?} AND NOT FIND_IN_SET('not_displayed', e.flags)
                                   ORDER BY  NOT FIND_IN_SET('always_displayed', e.flags), e.id, sn.name",
                                     $page->pid());

            $sn_types = XDB::iterator("SELECT  id, type, name,
                                               FIND_IN_SET('has_particle', flags) AS has_particle
                                         FROM  profile_name_enum
                                        WHERE  NOT FIND_IN_SET('not_displayed', flags)
                                               AND FIND_IN_SET('always_displayed', flags)
                                     ORDER BY  id");

            $value = array();
            $sn = $sn_all->next();
            while ($sn_type = $sn_types->next()) {
                if ($sn_type['id'] == $sn['typeid']) {
                    $value[] = $sn;
                    if ($sn) {
                        $sn = $sn_all->next();
                    }
                } else {
                    $value[] = array('name'             => '',
                                     'particle'         => '',
                                     'typeid'           => $sn_type['id'],
                                     'type'             => $sn_type['type'],
                                     'type_name'        => $sn_type['name'],
                                     'has_particle'     => $sn_type['has_particle'],
                                     'always_displayed' => 1,
                                     'pub'              => 1);
                }
            }
            if ($sn) {
                do {
                    $value[] = $sn;
                } while ($sn = $sn_all->next());
            }
        } else {
            require_once 'name.func.inc.php';

            $res = XDB::query("SELECT  s.particle, s.name
                                 FROM  profile_name      AS s
                           INNER JOIN  profile_name_enum AS e ON (e.id = s.typeid)
                                WHERE  s.pid = {?} AND e.type LIKE '%ini'
                             ORDER BY  e.type = 'firstname_ini'",
                             $page->pid());
            $res = $res->fetchAllAssoc();
            $initial = array();
            $initial['lastname'] = $res[0]['particle'] . $res[0]['name'];
            $initial['firstname'] = $res[1]['name'];
            $sn_types = build_types();
            $this->search_names = array();
            foreach ($value as &$sn) {
                $sn['name'] = trim($sn['name']);
                if ($sn['type'] == 'firstname' || $sn['type'] == 'lastname') {
                    $sn['name'] = $this->prepare($page, $sn['type'], $sn['name'],
                                                 $initial[$sn['type']], $success_tmp);
                    $success = $success && $success_tmp;
                }
                if ($sn['pub']) {
                    if (isset($sn['particle']) && ($sn['particle'] != '')) {
                        list($particle, $name) = explode(' ', $sn['name'], 2);
                        $particle = trim($particle) . ' ';
                        if (!$name) {
                            list($particle, $name) = explode('\'', $sn['name'], 2);
                            $particle = trim($particle);
                        }
                    } else {
                        $particle = '';
                        $name     = $sn['name'];
                    }
                }
                if ($sn['name'] != '') {
                    if ($sn['pub']) {
                        $this->search_names[$sn['typeid']] = array('fullname' => $sn['name'],
                                                                   'name'     => $name,
                                                                   'particle' => $particle,
                                                                   'pub'      => $sn['pub']);
                    } else {
                        if (isset($this->search_names[$sn['typeid']])) {
                            $this->search_names[$sn['typeid']][] = $sn['name'];
                        } else {
                            $this->search_names[$sn['typeid']] = array('fullname' => $sn['name']);
                        }
                        $sn['type_name'] = $sn_types[$sn['typeid']];
                    }
                }
            }
            $res = XDB::query("SELECT  public_name, private_name
                                 FROM  profile_display
                                WHERE  pid = {?}",
                              S::v('uid'));
            list($public_name, $private_name) = $res->fetchOneRow();
            if ($success) {
                $sn_types_private       = build_types('private');
                $this->private_name_end = build_private_name($this->search_names, $sn_types_private);
                $private_name           = $public_name . $this->private_name_end;
            }
            Platal::page()->assign('public_name', $public_name);
            Platal::page()->assign('private_name', $private_name);
        }

        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        require_once 'name.func.inc.php';
        $sn_old = build_sn_pub();
        XDB::execute("DELETE FROM  s
                            USING  profile_name      AS s
                       INNER JOIN  profile_name_enum AS e ON (s.typeid = e.id)
                            WHERE  s.pid = {?} AND NOT FIND_IN_SET('not_displayed', e.flags)",
                     $page->pid());
        $has_new = set_alias_names($this->search_names, $sn_old);

        // Only requires validation if modification in public names
        if ($has_new) {
            $new_names = new NamesReq(S::user(), $this->search_names, $this->private_name_end);
            $new_names->submit();
            Platal::page()->trigWarning("La demande de modification de tes noms a bien été prise en compte." .
                                        " Tu recevras un email dès que ces changements auront été effectués.");
        } else {
            $display_names = array();
            build_display_names($display_names, $this->search_names, $this->private_name_end);
            set_profile_display($display_names);
        }
    }
}

class ProfileSettingEdu implements ProfileSetting
{
    public function __construct() {
    }

    static function sortByGradYear($line1, $line2) {
        $a = (int) $line1['grad_year'];
        $b = (int) $line2['grad_year'];
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value) || !is_array($value)) {
            $value = array();
            $value = XDB::fetchAllAssoc("SELECT  eduid, degreeid, fieldid, grad_year, program
                                           FROM  profile_education
                                          WHERE  pid = {?} AND !FIND_IN_SET('primary', flags)
                                       ORDER BY  id",
                                        $page->pid());
        } else {
            $i = 0;
            foreach ($value as $key=>&$edu) {
                if (($edu['grad_year'] < 1921) || ($edu['grad_year'] > (date('Y') + 4))) {
                    Platal::page()->trigError('L\'année d\'obtention du diplôme est mal renseignée, elle doit être du type : 2004.');
                    $edu['error'] = true;
                    $success = false;
                }
                if ($key != $i) {
                    $value[$i] = $edu;
                    unset($value[$key]);
                }
                $i++;
            }
            usort($value, array("ProfileSettingEdu", "sortByGradYear"));
        }
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        XDB::execute("DELETE FROM  profile_education
                            WHERE  pid = {?} AND !FIND_IN_SET('primary', flags)",
                     $page->pid());
        foreach ($value as $eduid=>&$edu) {
            if ($edu['eduid'] != '') {
                XDB::execute("INSERT INTO  profile_education
                                      SET  id = {?}, pid = {?}, eduid = {?}, degreeid = {?},
                                           fieldid = {?}, grad_year = {?}, program = {?}",
                             $eduid, $page->pid(), $edu['eduid'], $edu['degreeid'],
                             $edu['fieldid'], $edu['grad_year'], $edu['program']);
            }
        }
    }
}

class ProfileSettingEmailDirectory implements ProfileSetting
{
    public function __construct(){}
    public function save(ProfilePage &$page, $field, $value){}

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $p = Platal::page();

        $success = true;
        if (!is_null($value)) {
            $email_stripped = strtolower(trim($value));
            if ((!isvalid_email($email_stripped)) && ($email_stripped) && ($page->values['email_directory'] == "new@example.org")) {
                $p->assign('email_error', '1');
                $p->assign('email_directory_error', $email_stripped);
                $p->trigError('Adresse Email invalide');
                $success = false;
            } else {
                $p->assign('email_error', '0');
            }
        }
        return $value;
    }
}

class ProfileSettingNetworking implements ProfileSetting
{
    private $email;
    private $pub;
    private $web;
    private $number;

    public function __construct()
    {
        $this->email  = new ProfileSettingEmail();
        $this->pub    = new ProfileSettingPub();
        $this->web    = new ProfileSettingWeb();
        $this->number = new ProfileSettingNumber();
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = XDB::fetchAllAssoc("SELECT  n.address, n.network_type AS type, n.pub, m.name
                                           FROM  profile_networking AS n
                                     INNER JOIN  profile_networking_enum AS m ON (n.network_type = m.network_type)
                                          WHERE  n.pid = {?}",
                                         $page->pid());
        }
        if (!is_array($value)) {
            $value = array();
        }
        $filters = XDB::fetchAllAssoc('type', 'SELECT  filter, network_type AS type
                                                 FROM  profile_networking_enum;');
        $success = true;
        foreach($value as $i=>&$network) {
            if (!trim($network['address'])) {
                unset($value[$i]);
            } else {
                if (!isset($network['pub'])) {
                    $network['pub'] = 'private';
                }
                $network['error'] = false;
                $network['pub'] = $this->pub->value($page, 'pub', $network['pub'], $s);
                $s = true;
                if ($filters[$network['type']] == 'web') {
                    $network['address'] = $this->web->value($page, 'address', $network['address'], $s);
                } elseif ($filters[$network['type']] == 'email') {
                    $network['address'] = $this->email->value($page, 'address', $network['address'], $s);
                } elseif ($filters[$network['type']] == 'number') {
                    $network['address'] = $this->number->value($page, 'address', $network['address'], $s);
                }
                if (!$s) {
                    $success = false;
                    $network['error'] = true;
                }
            }
        }
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        XDB::execute("DELETE FROM profile_networking
                            WHERE pid = {?}",
                     $page->pid());
        if (!count($value)) {
            return;
        }
        $insert = array();
        foreach ($value as $id=>$network) {
            XDB::execute("INSERT INTO  profile_networking (pid, nwid, network_type, address, pub)
                               VALUES  ({?}, {?}, {?}, {?}, {?})",
                         $page->pid(), $id, $network['type'], $network['address'], $network['pub']);
        }
    }
}

class ProfileSettingGeneral extends ProfilePage
{
    protected $pg_template = 'profile/general.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['search_names']
                                  = new ProfileSettingSearchNames();
        $this->settings['birthdate'] = new ProfileSettingDate();
        $this->settings['freetext_pub']
                                  = $this->settings['photo_pub']
                                  = new ProfileSettingPub();
        $this->settings['freetext']
                                  = $this->settings['nationality1']
                                  = $this->settings['nationality2']
                                  = $this->settings['nationality3']
                                  = $this->settings['yourself']
                                  = $this->settings['promo']
                                  = null;
        $this->settings['email_directory']
                                  = new ProfileSettingEmail();
        $this->settings['email_directory_new']
                                  = new ProfileSettingEmailDirectory();
        $this->settings['networking'] = new ProfileSettingNetworking();
        $this->settings['tels']   = new ProfileSettingPhones('user', 0);
        $this->settings['edus']   = new ProfileSettingEdu();
        $this->watched= array('freetext' => true, 'tels' => true,
                              'networking' => true, 'edus' => true,
                              'nationality1' => true, 'nationality2' => true,
                              'nationality3' => true, 'search_names' => true);
    }

    protected function _fetchData()
    {
        // Checkout all data...
        $res = XDB::query("SELECT  p.promo, e.entry_year AS entry_year, e.grad_year AS grad_year,
                                   pr.nationality1, pr.nationality2, pr.nationality3, pr.birthdate,
                                   t.display_tel as mobile, t.pub as mobile_pub,
                                   pr.email_directory as email_directory,
                                   pr.freetext, pr.freetext_pub, pr.ax_id AS matricule_ax, p.yourself
                             FROM  profiles              AS pr
                       INNER JOIN  profile_display       AS p ON (p.pid = pr.pid)
                       INNER JOIN  profile_education     AS e ON (e.pid = pr.pid AND FIND_IN_SET('primary', e.flags))
                        LEFT JOIN  profile_phones        AS t ON (t.pid = pr.pid AND link_type = 'user')
                            WHERE  pr.pid = {?}", $this->pid());
        $this->values = $res->fetchOneAssoc();
        if ($this->owner) {
            $this->values['yourself'] = $this->owner->displayName();
        }

        // Retreive photo informations
        $res = XDB::query("SELECT  pub
                             FROM  profile_photos
                            WHERE  pid = {?}", $this->pid());
        $this->values['photo_pub'] = $res->fetchOneCell();

        if ($this->owner) {
            $res = XDB::query("SELECT  COUNT(*)
                                 FROM  requests
                                WHERE  type = 'photo' AND uid = {?}",
                              $this->owner->id());
            $this->values['nouvellephoto'] = $res->fetchOneCell();
        } else {
            $this->values['nouvellephoto'] = 0;
        }

        // Proposes choice for promotion
        if ($this->values['entry_year'] != $this->values['grad_year'] - 3) {
            for ($i = $this->values['entry_year']; $i < $this->values['grad_year'] - 2; $i++) {
                $this->values['promo_choice'][] = "X" . $i;
            }
        }
    }

    protected function _saveData()
    {
        if ($this->changed['nationality1'] || $this->changed['nationality2'] || $this->changed['nationality3']
            || $this->changed['birthdate'] || $this->changed['freetext'] || $this->changed['freetext_pub']
            || $this->changed['email_directory']) {
            if ($this->values['nationality3'] == "") {
                $this->values['nationality3'] = NULL;
            }
            if ($this->values['nationality2'] == "") {
                $this->values['nationality2'] = $this->values['nationality3'];
                $this->values['nationality3'] = NULL;
            }
            if ($this->values['nationality1'] == "") {
                $this->values['nationality1']  = $this->values['nationality2'];
                $this->values['nationality2'] = $this->values['nationality3'];
                $this->values['nationality3'] = NULL;
            }
            $new_email = ($this->values['email_directory'] == "new@example.org") ?
                $this->values['email_directory_new'] : $this->values['email_directory'];
            if ($new_email == "") {
                $new_email = NULL;
            }

            XDB::execute("UPDATE  profiles
                             SET  nationality1 = {?}, nationality2 = {?}, nationality3 = {?}, birthdate = {?},
                                  freetext = {?}, freetext_pub = {?}, email_directory = {?}
                           WHERE  pid = {?}",
                          $this->values['nationality1'], $this->values['nationality2'], $this->values['nationality3'],
                          preg_replace('@(\d{2})/(\d{2})/(\d{4})@', '\3-\2-\1', $this->values['birthdate']),
                          $this->values['freetext'], $this->values['freetext_pub'], $new_email, $this->pid());
        }
        if ($this->changed['photo_pub']) {
            XDB::execute("UPDATE  profile_photos
                             SET  pub = {?}
                           WHERE  pid = {?}",
                         $this->values['photo_pub'], $this->pid());
        }
        if ($this->changed['yourself']) {
            XDB::execute('UPDATE  accounts
                             SET  display_name = {?}
                           WHERE  pid = {?}', $this->pid());
        }
        if ($this->changed['promo']) {
            XDB::execute("UPDATE  profile_display
                             SET  promo = {?}
                           WHERE  pid = {?}",
                         $this->values['promo'], $this->pid());
        }
    }

    public function _prepare(PlPage &$page, $id)
    {
        require_once "education.func.inc.php";

        $res = XDB::query("SELECT  id, field
                             FROM  profile_education_field_enum
                         ORDER BY  field");
        $page->assign('edu_fields', $res->fetchAllAssoc());

        require_once "emails.combobox.inc.php";
        fill_email_combobox($page, $this->owner, $this->profile);

        $res = XDB::query("SELECT  nw.network_type AS type, nw.name
                             FROM  profile_networking_enum AS nw
                         ORDER BY  name");
        $page->assign('network_list', $res->fetchAllAssoc());

        $res = XDB::query("SELECT  public_name, private_name
                             FROM  profile_display
                            WHERE  pid = {?}",
                          $this->pid());
        $res = $res->fetchOneRow();
        $page->assign('public_name', $res[0]);
        $page->assign('private_name', $res[1]);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
