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

class ProfileSettingSearchNames implements ProfileSetting
{
    private function diff($pid, array $old, array $new)
    {
        $diff = false;
        foreach ($old as $field => $name) {
            $diff = $diff || ($name != $new[$field]);
        }

        return $diff;
    }

    private function matchWord($old, $new, $newLen)
    {
        return ($i = strpos($old, $new)) !== false
            && ($i == 0 || $old{$i-1} == ' ')
            && ($i + $newLen == strlen($old) || $old{$i + $newLen} == ' ');
    }

    private function prepare(ProfilePage $page, array &$new_value)
    {
        $initial_value = XDB::fetchOneAssoc('SELECT  lastname_main, firstname_main
                                               FROM  profile_public_names
                                              WHERE  pid = {?}',
                                            $page->pid());

        $success = true;
        foreach ($initial_value as $field => $name) {
            $initial = name_to_basename($name);
            $new = name_to_basename($new_value[$field]);

            if (!($this->matchWord($initial, $new, strlen($new))
                || ($field == 'lastname_main' && $new == 'DE ' . $initial))) {
                $new_value[$field . '_error'] = true;
                $success = false;
                Platal::page()->trigError('Le nom choisi (' . $new . ') est trop loin de sa valeur initiale (' . $initial . ').');
            }
        }

        return $success;
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;

        if (is_null($value)) {
            $request = NamesReq::getPublicNames($page->pid());

            if (!$request) {
                $value['public_names'] = XDB::fetchOneAssoc('SELECT  particles, lastname_main, lastname_marital, lastname_ordinary,
                                                                     firstname_main, firstname_ordinary, pseudonym
                                                               FROM  profile_public_names
                                                              WHERE  pid = {?}',
                                                            $page->pid());

                $flags = new PlFlagSet($value['public_names']['particles']);
                unset($value['public_names']['particles']);
                static $suffixes = array('main', 'marital', 'ordinary');

                foreach ($suffixes as $suffix) {
                    $value['public_names']['particle_' . $suffix] = $flags->hasFlag($suffix);
                }
            } else {
                $value['public_names'] = $request;
                Platal::page()->assign('validation', true);
            }

            $value['private_names'] = XDB::fetchAllAssoc('SELECT  type, name
                                                            FROM  profile_private_names
                                                           WHERE  pid = {?}
                                                        ORDER BY  type, id',
                                                         $page->pid());
        } else {
            foreach ($value['public_names'] as $key => $name) {
                $value['public_names'][$key] = trim($name);
            }
            foreach ($value['private_names'] as $key => $name) {
                $value['private_names'][$key]['name'] = trim($name['name']);
                if ($value['private_names'][$key]['name'] == '') {
                    unset($value['private_names'][$key]);
                }
            }

            if (S::user()->isMe($page->owner)) {
                $success = $this->prepare($page, $value['public_names']);
            }
        }

        require_once 'name.func.inc.php';
        $public_name = build_first_name($value['public_names']) . ' ' . build_full_last_name($value['public_names'], $page->profile->isFemale());
        $private_name_end = build_private_name($value['private_names']);
        $private_name = $public_name . $private_name_end;

        Platal::page()->assign('public_name', $public_name);
        Platal::page()->assign('private_name', $private_name);

        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        require_once 'name.func.inc.php';

        $old = XDB::fetchOneAssoc('SELECT  lastname_main, lastname_marital, lastname_ordinary,
                                           firstname_main, firstname_ordinary, pseudonym
                                     FROM  profile_public_names
                                    WHERE  pid = {?}',
                                  $page->pid());

        if ($has_diff = $this->diff($page->pid(), $old, $value['public_names'])) {
            $new_names = new NamesReq(S::user(), $page->profile, $value['public_names'], $old);
            $new_names->submit();
            Platal::page()->assign('validation', true);
            Platal::page()->trigWarning('La demande de modification des noms a bien été prise en compte.' .
                                        ' Un email sera envoyé dès que ces changements auront été effectués.');
        }

        XDB::execute('DELETE FROM  profile_private_names
                            WHERE  pid = {?}',
                     $page->pid());
        $values = array();
        $nickname = $lastname = $firstname = 0;
        foreach ($value['private_names'] as $name) {
            $values[] = XDB::format('({?}, {?}, {?}, {?})', $page->pid(), $name['type'], $$name['type']++, $name['name']);
        }
        if (count($values)) {
            XDB::rawExecute('INSERT INTO  profile_private_names (pid, type, id, name)
                                  VALUES  ' . implode(',', $values));
        }

        if ($has_diff) {
            update_display_names($page->profile, $old, $value['private_names']);
        } else {
            update_display_names($page->profile, $value['public_names'], $value['private_names']);
        }
    }

    public function getText($value) {
        $public_names = array();
        foreach ($value['public_names'] as $name) {
            if ($name != '') {
                $public_names[] = $name;
            }
        }

        if (count($value['private_names'])) {
            $private_names = array();
            foreach ($value['private_names'] as $name) {
                $private_names[] = $name['name'];
            }
            return 'noms publics : ' . implode(', ' , $public_names) . ', noms privés : ' . implode(', ' , $private_names);;
        }

        return 'noms publics : ' . implode(', ' , $public_names);
    }
}

class ProfileSettingEdu implements ProfileSetting
{
    public function __construct() {
    }

    static function sortByGradYear($line1, $line2) {
        $a = (isset($line1['grad_year'])) ? (int) $line1['grad_year'] : 0;
        $b = (isset($line2['grad_year'])) ? (int) $line2['grad_year'] : 0;
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = array();
            $value = XDB::fetchAllAssoc("SELECT  eduid, degreeid, fieldid, grad_year, program
                                           FROM  profile_education
                                          WHERE  pid = {?} AND !FIND_IN_SET('primary', flags)
                                       ORDER BY  id",
                                        $page->pid());
        } else if (!is_array($value)) {
            $value = null;
        } else {
            $i = 0;
            foreach ($value as $key=>&$edu) {
                if ($edu['eduid'] < 1 || !isset($edu['degreeid']) || $edu['degreeid'] < 1) {
                    Platal::page()->trigError('L\'université ou le diplôme d\'une formation manque.');
                    $success = false;
                }
                if (($edu['grad_year'] < 1921) || ($edu['grad_year'] > (date('Y') + 4))) {
                    Platal::page()->trigWarning('L\'année d\'obtention du diplôme est mal ou non renseignée, elle doit être du type : 2004.');
                    $edu['grad_year'] = null;
                    $edu['warning'] = true;
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

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("DELETE FROM  profile_education
                            WHERE  pid = {?} AND !FIND_IN_SET('primary', flags)",
                     $page->pid());
        foreach ($value as $eduid=>&$edu) {
            if ($edu['eduid'] != '') {
                $fieldId = ($edu['fieldid'] == 0) ? null : $edu['fieldid'];
                XDB::execute("INSERT INTO  profile_education
                                      SET  id = {?}, pid = {?}, eduid = {?}, degreeid = {?},
                                           fieldid = {?}, grad_year = {?}, program = {?}",
                             $eduid, $page->pid(), $edu['eduid'], $edu['degreeid'],
                             $fieldId, $edu['grad_year'], $edu['program']);
            }
        }
    }

    public function getText($value) {
        $schoolsList = DirEnum::getOptions(DirEnum::EDUSCHOOLS);
        $degreesList = DirEnum::getOptions(DirEnum::EDUDEGREES);
        $fieldsList = DirEnum::getOptions(DirEnum::EDUFIELDS);
        $educations = array();
        foreach ($value as $id => $education) {
            // XXX: the following condition should be removed once there are no more incomplete educations.
            if (is_null($education['eduid']) || is_null($education['degreeid'])) {
                if (is_null($education['eduid']) && is_null($education['degreeid'])) {
                    $educations[$id] = 'formation manquante';
                } else {
                    $educations[$id] = (is_null($education['eduid']) ? 'université manquante' : $schoolsList[$education['eduid']]) . ', '
                                     . (is_null($education['degreeid']) ? 'diplôme manquant' : $degreesList[$education['degreeid']]);
                }
            } else {
                $educations[$id] = $schoolsList[$education['eduid']] . ', ' . $degreesList[$education['degreeid']];
            }

            $details = array();
            if ($education['grad_year']) {
                $details[] = $education['grad_year'];
            }
            if ($education['program']) {
                $details[] = '« ' . $education['program'] . ' »';
            }
            if ($education['fieldid']) {
                $details[] = $fieldsList[$education['fieldid']];
            }
            if (count($details)) {
                $educations[$id] .= ' (' . implode(', ', $details) . ')';
            }
        }
        return implode(', ', $educations);
    }
}

class ProfileSettingMainEdu implements ProfileSetting
{
    private $cycles;

    public function __construct()
    {
        $eduDegrees = DirEnum::getOptions(DirEnum::EDUDEGREES);
        $eduDegrees = array_flip($eduDegrees);
        $this->cycles = array(
            $eduDegrees[Profile::DEGREE_X] => 'Cycle polytechnicien',
            $eduDegrees[Profile::DEGREE_M] => 'Cycle master',
            $eduDegrees[Profile::DEGREE_D] => 'Cycle doctoral'
        );
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = array();
            $value = XDB::fetchAllAssoc("SELECT  degreeid, fieldid, promo_year, program
                                           FROM  profile_education
                                          WHERE  pid = {?} AND FIND_IN_SET('primary', flags)
                                       ORDER BY  degreeid",
                                        $page->pid());

            foreach ($value as &$item) {
                $item['cycle'] = $this->cycles[$item['degreeid']];
            }
        } elseif (!is_array($value)) {
            $value = array();
        } else {
            foreach ($value as $key => $item) {
                if (!isset($item['degreeid'])) {
                    unset($value[$key]);
                }
            }
        }

        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        foreach ($value as $item) {
            $fieldId = ($item['fieldid'] == 0) ? null : $item['fieldid'];
            XDB::execute("UPDATE  profile_education
                             SET  fieldid = {?}, program = {?}
                           WHERE  pid = {?} AND FIND_IN_SET('primary', flags) AND degreeid = {?}",
                         $fieldId, $item['program'], $page->pid(), $item['degreeid']);
        }
    }

    public function getText($value) {
        $fieldsList = DirEnum::getOptions(DirEnum::EDUFIELDS);
        $educations = array();
        foreach ($value as $item) {
            $details = array($this->cycles[$item['degreeid']]);
            if ($education['program']) {
                $details[] = '« ' . $education['program'] . ' »';
            }
            if ($education['fieldid']) {
                $details[] = $fieldsList[$education['fieldid']];
            }
        }
        return implode(', ', $educations);
    }
}

class ProfileSettingEmailDirectory implements ProfileSetting
{
    public function __construct(){}
    public function save(ProfilePage $page, $field, $value){}

    public function value(ProfilePage $page, $field, $value, &$success)
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

    public function getText($value) {
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

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = XDB::fetchAllAssoc("SELECT  n.address, n.pub, n.nwid AS type
                                           FROM  profile_networking AS n
                                          WHERE  n.pid = {?}",
                                         $page->pid());
        }
        if (!is_array($value)) {
            $value = array();
        }
        $filters = XDB::fetchAllAssoc('type', 'SELECT  filter, nwid AS type, name
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
                $network['name'] = $filters[$network['type']]['name'];
                if ($filters[$network['type']]['filter'] == 'web') {
                    $network['address'] = $this->web->value($page, 'address', $network['address'], $s);
                } elseif ($filters[$network['type']]['filter'] == 'email') {
                    $network['address'] = $this->email->value($page, 'address', $network['address'], $s);
                } elseif ($filters[$network['type']]['filter'] == 'number') {
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

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("DELETE FROM profile_networking
                            WHERE pid = {?}",
                     $page->pid());
        if (!count($value)) {
            return;
        }
        $insert = array();
        foreach ($value as $id=>$network) {
            XDB::execute("INSERT INTO  profile_networking (pid, id, nwid, address, pub)
                               VALUES  ({?}, {?}, {?}, {?}, {?})",
                         $page->pid(), $id, $network['type'], $network['address'], $network['pub']);
        }
    }

    public function getText($value) {
        static $pubs = array('public' => 'publique', 'ax' => 'annuaire AX', 'private' => 'privé');
        $networkings = array();
        foreach ($value as $network) {
            $networkings[] = $network['name'] . ' : ' . $network['address'] . ' (affichage ' . $pubs[$network['pub']] . ')';
        }
        return implode(', ' , $networkings);
    }
}

class ProfileSettingPromo implements ProfileSetting
{
    public function __construct(){}

    public function save(ProfilePage $page, $field, $value)
    {
        $gradYearNew = $value;
        if ($page->profile->mainEducation() == 'X') {
            $gradYearNew += $page->profile->mainEducationDuration();
        }
        if (($page->profile->mainEducation() != 'X'
             && $value == $page->profile->entry_year + $page->profile->mainEducationDuration())
           || ($page->profile->mainEducation() == 'X' && $value == $page->profile->entry_year)) {
            XDB::execute('UPDATE  profile_display
                             SET  promo = {?}
                           WHERE  pid = {?}',
                         $page->profile->mainEducation() . strval($value), $page->profile->id());
            XDB::execute('UPDATE  profile_education
                             SET  grad_year = {?}
                           WHERE  pid = {?} AND FIND_IN_SET(\'primary\', flags)',
                         $gradYearNew, $page->profile->id());
            Platal::page()->trigSuccess('Ton statut « orange » a été supprimé.');
        } else {
            $myorange = new OrangeReq(S::user(), $page->profile, $gradYearNew);
            $myorange->submit();
            Platal::page()->trigSuccess('Tu pourras changer l\'affichage de ta promotion dès que ta nouvelle promotion aura été validée.');
        }
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $entryYear = $page->profile->entry_year;
        $gradYear  = $page->profile->grad_year;
        $yearpromo = $page->profile->yearpromo();
        $success   = true;
        if (is_null($value) || $value == $yearpromo) {
            if ($gradYear != $entryYear + $page->profile->mainEducationDuration()) {
                $promoChoice = array();
                for ($i = $entryYear; $i <= $gradYear - $page->profile->mainEducationDuration(); ++$i) {
                    if ($page->profile->mainEducation() == 'X') {
                        $promoChoice[] = $page->profile->mainEducation() . strval($i);
                    } else {
                        $promoChoice[] = $page->profile->mainEducation() . strval($i + $page->profile->mainEducationDuration());
                    }
                }
                Platal::page()->assign('promo_choice', $promoChoice);
            }
            return $yearpromo;
        }

        // If this profile belongs to an X, $promoNew needs to be changed to
        // the graduation year.
        $gradYearNew = $value;
        if ($page->profile->mainEducation() == 'X') {
            $gradYearNew += $page->profile->mainEducationDuration();
        }

        if ($value < 1000 || $value > 9999) {
            Platal::page()->trigError('L\'année de sortie doit être un nombre de quatre chiffres.');
            $success = false;
        } elseif ($gradYearNew < $entryYear + $page->profile->mainEducationDuration()) {
            Platal::page()->trigError('Trop tôt&nbsp;!');
            $success = false;
        }
        return intval($value);
    }

    public function getText($value) {
        return $value;
    }
}


class ProfilePageGeneral extends ProfilePage
{
    protected $pg_template = 'profile/general.tpl';

    public function __construct(PlWizard $wiz)
    {
        parent::__construct($wiz);
        $this->settings['search_names']
                                  = new ProfileSettingSearchNames();
        $this->settings['nationality1']
                                  = $this->settings['nationality2']
                                  = $this->settings['nationality3']
                                  = $this->settings['promo_display']
                                  = null;
        $this->settings['email_directory']
                                  = new ProfileSettingEmail();
        $this->settings['email_directory_new']
                                  = new ProfileSettingEmailDirectory();
        $this->settings['networking'] = new ProfileSettingNetworking();
        $this->settings['tels']   = new ProfileSettingPhones();
        $this->settings['edus']   = new ProfileSettingEdu();
        $this->settings['main_edus'] = new ProfileSettingMainEdu();
        $this->settings['promo']  = new ProfileSettingPromo();
        $this->watched= array('tels' => true,
                              'networking' => true, 'edus' => true,
                              'nationality1' => true, 'nationality2' => true,
                              'nationality3' => true, 'search_names' => true);

        /* Some fields editable under condition */
        if (!S::user()->isMe($this->owner)) {
            $this->settings['deathdate'] = new ProfileSettingDate(true);
            $this->settings['birthdate'] = new ProfileSettingDate(true);
        } else {
            $this->settings['yourself'] = null;
            $this->settings['birthdate'] = new ProfileSettingDate();
        }
        if (S::user()->checkPerms('directory_private')
            || S::user()->isMyProfile($this->owner)) {
            $this->settings['freetext'] = null;
            $this->settings['freetext_pub']
                                      = $this->settings['photo_pub']
                                      = new ProfileSettingPub();
            $this->watched['freetext'] = true;
        }

    }

    protected function _fetchData()
    {
        // Checkout all data...
        $res = XDB::query("SELECT  p.nationality1, p.nationality2, p.nationality3, IF(p.birthdate = 0, '', p.birthdate) AS birthdate,
                                   p.email_directory as email_directory, pd.promo AS promo_display,
                                   p.freetext, p.freetext_pub, p.ax_id AS matricule_ax, pd.yourself,
                                   p.deathdate
                             FROM  profiles              AS p
                       INNER JOIN  profile_display       AS pd ON (pd.pid = p.pid)
                            WHERE  p.pid = {?}", $this->pid());
        $this->values = $res->fetchOneAssoc();

        // Retreive photo informations
        $res = XDB::query("SELECT  pub
                             FROM  profile_photos
                            WHERE  pid = {?}", $this->pid());
        if ($res->numRows() == 0) {
            $this->values['photo_pub'] = 'private';
        } else {
            $this->values['photo_pub'] = $res->fetchOneCell();
        }

        if ($this->owner) {
            $res = XDB::query("SELECT  COUNT(*)
                                 FROM  requests
                                WHERE  type = 'photo' AND pid = {?}",
                              $this->owner->id());
            $this->values['nouvellephoto'] = $res->fetchOneCell();
        } else {
            $this->values['nouvellephoto'] = 0;
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
                $this->values['nationality1'] = $this->values['nationality2'];
                $this->values['nationality2'] = $this->values['nationality3'];
                $this->values['nationality3'] = NULL;
            }
            if ($this->values['nationality1'] == $this->values['nationality2']
                && $this->values['nationality2'] == $this->values['nationality3']) {
                $this->values['nationality2'] = NULL;
                $this->values['nationality3'] = NULL;
            } else if ($this->values['nationality1'] == $this->values['nationality2']) {
                $this->values['nationality2'] = $this->values['nationality3'];
                $this->values['nationality3'] = NULL;
            } else if ($this->values['nationality2'] == $this->values['nationality3']
                    || $this->values['nationality1'] == $this->values['nationality3']) {
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
                          ProfileSettingDate::toSQLDate($this->values['birthdate']),
                          $this->values['freetext'], $this->values['freetext_pub'], $new_email, $this->pid());
        }
        if ($this->changed['photo_pub']) {
            XDB::execute("UPDATE  profile_photos
                             SET  pub = {?}
                           WHERE  pid = {?}",
                         $this->values['photo_pub'], $this->pid());
        }
        if (S::user()->isMe($this->owner) && $this->changed['yourself']) {
            if ($this->owner) {
                XDB::execute('UPDATE  accounts
                                 SET  display_name = {?}
                               WHERE  uid = {?}',
                             $this->values['yourself'], $this->owner->id());
            }
            XDB::execute('UPDATE  profile_display
                             SET  yourself = {?}
                           WHERE  pid = {?}', $this->values['yourself'],
                         $this->pid());
        }
        if ($this->changed['promo_display']) {
            if ($this->values['promo_display']{0} == $this->profile->mainEducation()) {
                $yearpromo = intval(substr($this->values['promo_display'], 1, 4));
                if (($this->profile->mainEducation() == 'X' && $yearpromo >= $this->profile->entry_year)
                    || ($this->profile->mainEducation() != 'X'
                        && $yearpromo >= $this->profile->entry_year + $this->profile->mainEducationDuration())) {
                    XDB::execute('UPDATE  profile_display
                                     SET  promo = {?}
                                   WHERE  pid = {?}',
                                 $this->values['promo_display'], $this->pid());
                    XDB::execute('UPDATE  profile_education
                                     SET  promo_year = {?}
                                   WHERE  pid = {?} AND FIND_IN_SET(\'primary\', flags)',
                                 $yearpromo, $this->pid());
                }
            }
        }
        if (!S::user()->isMe($this->owner) && $this->changed['deathdate']) {
            XDB::execute('UPDATE  profiles
                             SET  deathdate = {?}, deathdate_rec = NOW()
                           WHERE  pid = {?} AND deathdate_rec IS NULL',
                         ProfileSettingDate::toSQLDate($this->values['deathdate']), $this->pid());
            if (XDB::affectedRows() > 0) {
                $this->profile->clear();
                if ($this->owner) {
                    $this->owner->clear(true);
                }
            } else {
                /* deathdate_rec was not NULL, this is just an update of the death date
                 */
                XDB::execute('UPDATE  profiles
                                 SET  deathdate = {?}
                               WHERE  pid = {?}',
                             ProfileSettingDate::toSQLDate($this->values['deathdate']), $this->pid());
            }
        }
    }

    public function _prepare(PlPage $page, $id)
    {
        require_once "education.func.inc.php";

        $res = XDB::query("SELECT  id, field
                             FROM  profile_education_field_enum
                         ORDER BY  field");
        $page->assign('edu_fields', $res->fetchAllAssoc());

        require_once "emails.combobox.inc.php";
        fill_email_combobox($page, array('source', 'redirect', 'job', 'directory'), $this->owner);

        $res = XDB::query("SELECT  nw.nwid AS type, nw.name
                             FROM  profile_networking_enum AS nw
                         ORDER BY  name");
        $page->assign('network_list', $res->fetchAllAssoc());

        $page->assign('lastnames', array('main' => 'Nom patronymique', 'marital' => 'Nom marital', 'ordinary' => 'Nom usuel'));
        $page->assign('firstnames', array('firstname_main' => 'Prénom', 'firstname_ordinary' => 'Prénom usuel', 'pseudonym' => 'Pseudonyme (nom de plume)'));
        $page->assign('other_names', array('nickname' => 'Surnom', 'firstname' => 'Autre prénom', 'lastname' => 'Autre nom'));
        $page->assign('isFemale', $this->profile->isFemale() ? 1 : 0);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
