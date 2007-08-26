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

class ProfileFixed extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
    }
}

class ProfileWeb extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        $success = preg_match("{^(https?|ftp)://[a-zA-Z0-9._%#+/?=&~-]+$}i", $value);
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
        $success = strlen(strtok($value, '<>{}@&#~\/:;?,!§*_`[]|%$^=')) < strlen($value);
        if (!$success) {
            global $page;
            $page->trig('Le numéro de téléphone contient un caractère interdit.');
        }
        return $value;
    }
}

abstract class ProfilePage implements PlWizardPage
{
    protected $wizard;
    protected $pg_template;
    protected $settings = array();  // A set ProfileSetting objects

    public $values   = array();

    public function __construct(PlWizard &$wiz)
    {
        $this->wizard =& $wiz;
    }

    protected function fetchData()
    {
    }

    protected function saveData()
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
            foreach ($this->settings as $field=>&$setting) {
                $success = false;
                $this->values[$field] = $setting->value($this, $field, null, $success);
            }
        }
        foreach ($this->values as $field=>&$value) {
            $page->assign($field, $value);
        }
        $page->assign('profile_page', $this->pg_template);
    }

    public function process()
    {
        $global_success = true;
        $this->fetchData();
        foreach ($this->settings as $field=>&$setting) {
            $success = false;
            $this->values[$field] = $setting->value($this, $field, Post::v($field), $success);
            $global_success = $global_success && $success;
        }
        if ($global_success) {
            foreach ($this->settings as $field=>&$setting) {
                $setting->save($this, $field, $this->values[$field]);
            }
            $this->saveData();
            return Post::has('valid_and_next') ? PlWizard::NEXT_PAGE : PlWizard::CURRENT_PAGE;
        }
        global $page;
        $page->trig("Certains champs n'ont pas pu être validés, merci de corriger les infos "
                  . "de ton profil et de revalider ta demande");
        return PlWizard::CURRENT_PAGE;
    }
}

require_once dirname(__FILE__) . '/general.inc.php';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
