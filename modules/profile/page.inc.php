<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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
    public function value(ProfilePage &$page, $field, $value, &$success);

    /** Save the new value for the given field.
     */
    public function save(ProfilePage &$page, $field, $new_value);
}

abstract class ProfileNoSave implements ProfileSetting
{
    public function save(ProfilePage &$page, $field, $new_value) { }
}

class ProfileWeb extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        $success = !trim($value) || preg_match("{^(https?|ftp)://[a-zA-Z0-9._%#+/?=&~-]+$}i", $value);
        if (!$success) {
            global $page;
            $page->trig('URL Incorrecte : une url doit commencer par http:// ou https:// ou ftp://'
                      . ' et ne pas contenir de caractères interdits');
        }
        return $value;
    }
}

class ProfileTel extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        $success = strlen(strtok($value, '<>{}@&#~\/:;?,!§*_`[]|%$^=')) == strlen($value);
        if (!$success) {
            global $page;
            $page->trig('Le numéro de téléphone contient un caractère interdit.');
        }
        return $value;
    }
}

class ProfilePub extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        if (is_null($value) || !$value) {
            $value = 'private';
        } else if ($value == 'on') { // Checkbox
            $value = 'public';
        }
        return $value;
    }
}

class ProfileBool extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = @$page->values[$field];
        }
        return $value ? 1 : 0;
    }
}

abstract class ProfilePage implements PlWizardPage
{
    protected $wizard;
    protected $pg_template;
    protected $settings = array();  // A set ProfileSetting objects
    protected $errors   = array();  // A set of boolean with the value check errors
    protected $changed  = array();  // A set of boolean indicating wether the value has been changed

    public $orig     = array();
    public $values   = array();

    public function __construct(PlWizard &$wiz)
    {
        $this->wizard =& $wiz;
    }

    protected function fetchData()
    {
        if (count($this->orig) > 0) {
            $this->values = $this->orig;
            return;
        }
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

    protected function saveData()
    {
        foreach ($this->settings as $field=>&$setting) {
            if (!is_null($setting) && $this->changed[$field]) {
                $setting->save($this, $field, $this->values[$field]);
            }
        }

        // Update the last modification date
        XDB::execute('REPLACE INTO  user_changes
                               SET  user_id = {?}', S::v('uid'));
        global $platal;
        $log =& $_SESSION['log'];
        $log->log('profil', $platal->pl_self(1));
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

    public function prepare(PlatalPage &$page)
    {
        if (count($this->values) == 0) {
            $this->fetchData();
        }
        foreach ($this->values as $field=>&$value) {
            $page->assign($field, $value);
        }
        $page->assign('profile_page', $this->pg_template);
        $page->assign('errors', $this->errors);
    }

    public function process()
    {
        $global_success = true;
        $this->fetchData();
        foreach ($this->settings as $field=>&$setting) {
            $success = false;
            if (!is_null($setting)) {
                $this->values[$field] = $setting->value($this, $field, Post::v($field), $success);
            } else {
                $success = true;
                $this->values[$field] = Post::v($field);
            }
            $this->errors[$field] = !$success;
            $global_success = $global_success && $success;
        }
        if ($global_success) {
            if ($this->checkChanges()) {
                $this->saveData();
                $this->markChange();
            }
            return Post::has('next_page') ? PlWizard::NEXT_PAGE : PlWizard::CURRENT_PAGE;
        }
        global $page;
        $page->trig("Certains champs n'ont pas pu être validés, merci de corriger les informations "
                  . "de ton profil et de revalider ta demande");
        return PlWizard::CURRENT_PAGE;
    }
}

require_once dirname(__FILE__) . '/general.inc.php';
require_once dirname(__FILE__) . '/addresses.inc.php';
require_once dirname(__FILE__) . '/groups.inc.php';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
