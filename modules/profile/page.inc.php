<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

interface ProfileSetting
{
    /** Get a field and a value, check that the given value is
     * valid, if not, return a corrected value. If no valid value can be
     * computed from the input data, the success flag is set to false.
     *
     * If value is null, the default value should be returned.
     * TODO: check this does not conflict with some possible values.
     *
     * Whatever happen, this function must always returns the function to
     * show on the page to the user.
     */
    public function value(ProfilePage $page, $field, $value, &$success);

    /** Save the new value for the given field.
     */
    public function save(ProfilePage $page, $field, $new_value);

    /** Get text from the value.
     */
    public function getText($value);
}

abstract class ProfileNoSave implements ProfileSetting
{
    public function save(ProfilePage $page, $field, $new_value) { }

    public function getText($value) {
        return $value;
    }
}

class ProfileSettingWeb extends ProfileNoSave
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        $value = trim($value);
        $success = empty($value) || preg_match("{^(https?|ftp)://[a-zA-Z0-9._%#+/?=&~-]+$}i", $value);
        if (!$success) {
            Platal::page()->trigError('URL Incorrecte : une url doit commencer par http:// ou https:// ou ftp://'
                                    . ' et ne pas contenir de caractères interdits');
        }
        return $value;
    }
}

class ProfileSettingEmail extends ProfileNoSave
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        $value = trim($value);
        $success = empty($value) || isvalid_email($value);
        if (!$success) {
            Platal::page()->trigError('Adresse Email invalide');
        }
        return $value;
    }
}

class ProfileSettingNumber extends ProfileNoSave
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        $value = trim($value);
        $success = empty($value) || is_numeric($value);
        if (!$success) {
            Platal::page()->trigError('Numéro invalide');
        }
        return $value;
    }
}

class ProfileSettingPhones implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        $phones = array();

        if (is_null($value)) {
            $it = Phone::iterate(array($page->pid()), array(Phone::LINK_PROFILE), array(0), Visibility::get(Visibility::VIEW_ADMIN));
            while ($phone = $it->next()) {
                $success = ($phone->format() && $success);
                $phones[] = $phone->toFormArray();
            }
            if (count($phones) == 0) {
                $phone = new Phone();
                $phones[] = $phone->toFormArray();
            }
            return $phones;
        } else {
            $phones = Phone::formatFormArray($value, $success);
            if (!$success) {
                Platal::page()->trigError('Numéro de téléphone invalide');
            }
            return $phones;
        }
    }

    public function save(ProfilePage $page, $field, $value)
    {
        Phone::deletePhones($page->pid(), Phone::LINK_PROFILE, null, S::user()->isMe($page->owner) || S::admin());
        Phone::savePhones($value, $page->pid(), Phone::LINK_PROFILE);
    }

    public function getText($value)
    {
        return Phone::formArrayToString($value);
    }
}

class ProfileSettingPub extends ProfileNoSave
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        if (!$value) {
            $value = 'private';
        } elseif ($value == 'on') { // Checkbox
            $value = 'public';
        }
        return $value;
    }

    public function getText($value) {
        static $pubs = array('public' => 'publique', 'ax' => 'annuaire papier', 'private' => 'privé', 'hidden' => 'administrateurs');
        return $pubs[$value];
    }
}

class ProfileSettingBool extends ProfileNoSave
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = isset($page->values[$field]) ? $page->values[$field] : null;
        }
        return $value ? "1" : "";
    }
}

class ProfileSettingDate extends ProfileNoSave
{
    private $allowEmpty;

    public function __construct($allowEmpty = false)
    {
        $this->allowEmpty = $allowEmpty;
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '\3/\2/\1', @$page->values[$field]);
        } else {
            $value = trim($value);
            if (empty($value) && $this->allowEmpty) {
                return null;
            }
            $success = preg_match('@(\d{2})/(\d{2})/(\d{4})@', $value, $matches);
            if (!$success) {
                Platal::page()->trigError("Les dates doivent être au format jj/mm/aaaa");
            } else {
                $day   = (int)$matches[1];
                $month = (int)$matches[2];
                $year  = (int)$matches[3];
                $success = ($day > 0 && $day <= 31) && ($month > 0 && $month <= 12) && ($year > 1900 && $year <= 2020);
                if (!$success) {
                    Platal::page()->trigError("La date n'a pas une valeur valide");
                }
            }
        }
        return $value;
    }

    public static function toSQLDate($value)
    {
        return preg_replace('@(\d{2})/(\d{2})/(\d{4})@', '\3-\2-\1', $value);
    }
}

abstract class ProfilePage implements PlWizardPage
{
    protected $wizard;
    protected $pg_template;
    protected $settings = array();  // A set ProfileSetting objects
    protected $errors   = array();  // A set of boolean with the value check errors
    protected $changed  = array();  // A set of boolean indicating wether the value has been changed
    protected $watched  = array();  // A set of boolean indicating the fields that are watched

    public $orig     = array();
    public $values   = array();
    public $profile  = null;
    public $owner    = null;

    public function __construct(PlWizard $wiz)
    {
        $this->wizard =& $wiz;
        $this->profile = $this->wizard->getUserData('profile');
        $this->owner   = $this->wizard->getUserData('owner');
    }

    protected function _fetchData()
    {
    }

    protected function fetchData()
    {
        if (count($this->orig) > 0) {
            $this->values = $this->orig;
            return;
        }

        $this->_fetchData();
        foreach ($this->settings as $field=>&$setting) {
            $success = false;
            if (!is_null($setting)) {
                $this->values[$field] = $setting->value($this, $field, null, $success);
            } else if (!isset($this->values[$field])) {
                $this->values[$field] = S::v($field);
            }
            $this->errors[$field] = false;
        }
        $this->orig = $this->values;
    }

    protected function _saveData()
    {
    }

    public function saveData()
    {
        require_once 'notifs.inc.php';
        $changedFields = array();
        foreach ($this->settings as $field=>&$setting) {
            if ($this->changed[$field]) {
                if (!is_null($setting)) {
                    $changedFields[$field] = array(
                        preg_replace('/(\r\n|\n|\r)/', ' - ', $setting->getText($this->orig[$field])),
                        preg_replace('/(\r\n|\n|\r)/', ' - ', $setting->getText($this->values[$field])),
                    );
                } else {
                    $changedFields[$field] = array(
                        preg_replace('/(\r\n|\n|\r)/', ' - ', $this->orig[$field]),
                        preg_replace('/(\r\n|\n|\r)/', ' - ', $this->values[$field]),
                    );
                }
                if (!is_null($setting)) {
                    $setting->save($this, $field, $this->values[$field]);
                }
                if (isset($this->watched[$field]) && $this->watched[$field]) {
                    WatchProfileUpdate::register($this->profile, $field);
                }
            }
        }
        $this->_saveData();

        // Update the last modification date
        XDB::execute('UPDATE  profiles
                         SET  last_change = NOW()
                       WHERE  pid = {?}', $this->pid());
        global $platal;
        S::logger()->log('profil', $platal->pl_self(2));

        /** Stores all profile modifications for active users in order to:
         *  -daily notify the user in case of third party edition,
         *  -display the modification to the secretaries for verification in
         *  case of an edition made by the user.
         */
        $owner = $this->profile->owner();
        $user = S::user();
        if ($owner->isActive()) {
            foreach ($changedFields as $field => $values) {
                if (array_key_exists($field, Profile::$descriptions)) {
                    XDB::execute('INSERT INTO  profile_modifications (pid, uid, field, oldText, newText, type, timestamp)
                                       VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, NOW())
                      ON DUPLICATE KEY UPDATE  uid = VALUES(uid), oldText = IF(VALUES(type) != type, VALUES(oldText), oldText),
                                               newText = VALUES(newText), type = VALUES(type), timestamp = NOW()',
                                 $this->pid(), $user->id(), Profile::$descriptions[$field], $values[0], $values[1],
                                 ($owner->id() == $user->id()) ? 'self' : 'third_party');
                }
            }
        }
        return true;
    }

    protected function checkChanges()
    {
        $newvalues = $this->values;
        $this->values = array();
        $this->fetchData();
        $this->values = $newvalues;
        $changes = false;
        foreach ($this->settings as $field=>&$setting) {
            if ($this->orig[$field] != $this->values[$field]) {
                $this->changed[$field] = true;
                $changes = true;
            } else {
                $this->changed[$field] = false;
            }
        }
        return $changes;
    }

    protected function markChange()
    {
    }

    public function template()
    {
        return 'profile/base.tpl';
    }

    public function pid()
    {
        return $this->profile->id();
    }

    public function hrpid()
    {
        return $this->profile->hrpid();
    }

    protected function _prepare(PlPage $page, $id)
    {
    }

    public function prepare(PlPage $page, $id)
    {
        if (count($this->values) == 0) {
            $this->fetchData();
        }
        foreach ($this->values as $field=>&$value) {
            $page->assign($field, $value);
        }
        $this->_prepare($page, $id);
        $page->assign('profile', $this->profile);
        $page->assign('owner', $this->owner);
        $page->assign('profile_page', $this->pg_template);
        $page->assign('errors', $this->errors);
    }

    public function process(&$global_success)
    {
        $global_success = true;
        $this->fetchData();
        foreach ($this->settings as $field=>&$setting) {
            $success = false;
            if (!is_null($setting)) {
                $this->values[$field] = $setting->value($this, $field, Post::v($field, ''), $success);
            } else {
                $success = true;
                $this->values[$field] = Post::v($field, '');
            }
            $this->errors[$field] = !$success;
            $global_success = $global_success && $success;
        }
        if ($global_success) {
            if ($this->checkChanges()) {
                /* Save changes atomically to avoid inconsistent state
                 * in case of error.
                 */
                if (!XDB::runTransaction(array($this, 'saveData'))) {
                    $global_success = false;
                    return PlWizard::CURRENT_PAGE;
                }
                $this->markChange();
            }
            // XXX: removes this code once all merge related issues have been fixed.
            static $issues = array(0 => array('name', 'promo', 'phone', 'education'), 1 => array('address'), 2 => array('job'));
            if (isset($issues[Post::i('valid_page')])) {
                foreach ($issues[Post::i('valid_page')] as $issue) {
                    XDB::execute("UPDATE  profile_merge_issues
                                     SET  issues = REPLACE(issues, {?}, '')
                                   WHERE  pid = {?}",
                                 $issue, $this->pid());
                }
            }
            return Post::has('next_page') ? PlWizard::NEXT_PAGE : PlWizard::CURRENT_PAGE;
        }
        $text = "Certains champs n'ont pas pu être validés, merci de corriger les informations "
              . (S::user()->isMe($this->owner) ? "de ton profil et de revalider ta demande."
                                               : "du profil et de revalider ta demande.");
        Platal::page()->trigError($text);
        return PlWizard::CURRENT_PAGE;
    }

    public function success()
    {
        if (S::user()->isMe($this->owner)) {
            return 'Ton profil a bien été mis à jour.';
        } else {
            return 'Le profil a bien été mis à jour.';
        }
    }
}

require_once dirname(__FILE__) . '/general.inc.php';
require_once dirname(__FILE__) . '/addresses.inc.php';
require_once dirname(__FILE__) . '/groups.inc.php';
require_once dirname(__FILE__) . '/decos.inc.php';
require_once dirname(__FILE__) . '/jobs.inc.php';
require_once dirname(__FILE__) . '/mentor.inc.php';
require_once dirname(__FILE__) . '/deltaten.inc.php';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
