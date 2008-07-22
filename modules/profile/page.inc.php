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
        $value = trim($value);
        $success = empty($value) || preg_match("{^(https?|ftp)://[a-zA-Z0-9._%#+/?=&~-]+$}i", $value);
        if (!$success) {
            Platal::page()->trigError('URL Incorrecte : une url doit commencer par http:// ou https:// ou ftp://'
                                    . ' et ne pas contenir de caractères interdits');
        }
        return $value;
    }
}

class ProfileEmail extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
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

class ProfileNumber extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        if (is_null($value)) {
            return isset($page->values[$field]) ? $page->values[$field] : S::v($field);
        }
        $value = trim($value);
        $success = empty($value) || is_numeric($value);
        if (!$success) {
            global $page;
            $page->trigError('Numéro invalide');
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
        require_once('profil.func.inc.php');
        $value = format_phone_number($value);
        if($value == '') {
            $success = true;
            return $value;
        }
        $value = format_display_number($value,$error);
        $success = !$error;
        if (!$success) {
            global $page;
            $page->trigError('Le préfixe international du numéro de téléphone est inconnu. ');
        }
        return $value;
    }
}

class ProfilePhones implements ProfileSetting
{
    private $tel;
    private $pub;
    protected $link_type;
    protected $link_id;

    public function __construct($type, $id)
    {
        $this->tel = new ProfileTel();
        $this->pub = new ProfilePub();
        $this->link_type = $type;
        $this->link_id = $id;
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = isset($page->values[$field]) ? $page->values[$field] : array();
        }
        if (!is_array($value)) {
            $value = array();
        }
        foreach ($value as $key=>&$phone) {
            if (@$phone['removed']) {
                unset($value[$key]);
            } else {
                $phone['pub'] = $this->pub->value($page, 'pub', $phone['pub'], $s);
                $phone['tel'] = $this->tel->value($page, 'tel', $phone['tel'], $s);
                if(!isset($phone['type']) || ($phone['type'] != 'fixed' && $phone['type'] != 'mobile' && $phone['type'] != 'fax')) {
                    $phone['type'] = 'fixed';
                    $s = false;
                }
                if (!$s) {
                    $phone['error'] = true;
                    $success = false;
                }
                if (!isset($phone['comment'])) {
                    $phone['comment'] = '';
                }
            }
        }
        return $value;
    }

    private function saveTel($telid, array &$phone)
    {
        if ($phone['tel'] != '') {
            XDB::execute("INSERT INTO  profile_phones (uid, link_type, link_id, tel_id, tel_type,
                                       search_tel, display_tel, pub, comment)
                               VALUES  ({?}, {?}, {?}, {?}, {?},
                                       {?}, {?}, {?}, {?})",
                         S::i('uid'), $this->link_type, $this->link_id, $telid, $phone['type'],
                        format_phone_number($phone['tel']), $phone['tel'], $phone['pub'], $phone['comment']);
        }
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        XDB::execute("DELETE FROM  profile_phones
                            WHERE  uid = {?} AND link_type = {?} AND link_id = {?}",
                            S::i('uid'), $this->link_type, $this->link_id);
        $this->saveTels($field, $value);
    }

    //Only saves phones without a delete operation
    public function saveTels($field, $value)
    {
        foreach ($value as $telid=>&$phone) {
            $this->saveTel($telid, $phone);
        }
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
        return $value ? "1" : "";
    }
}

class ProfileDate extends ProfileNoSave
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '\3/\2/\1', @$page->values[$field]);
        } else {
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
}

abstract class ProfileGeoloc implements ProfileSetting
{
    protected function geolocAddress(array &$address, &$success)
    {
        require_once 'geoloc.inc.php';
        $success = true;
        unset($address['geoloc']);
        unset($address['geoloc_cityid']);
        if (@$address['parsevalid']
            || (@$address['text'] && @$address['changed'])
            || (@$address['text'] && !@$address['cityid'])) {
            $address = array_merge($address, empty_address());
            $new = get_address_infos(@$address['text']);
            if (compare_addresses_text(@$address['text'], $geotxt = get_address_text($new))
                || (@$address['parsevalid'] && @$address['cityid'])) {
                $address = array_merge($address, $new);
                $address['checked'] = true;
            } else if (@$address['parsevalid']) {
                $address = array_merge($address, cut_address(@$address['text']));
                $address['checked'] = true;
                $mailer = new PlMailer('geoloc/geoloc.mail.tpl');
                $mailer->assign('text', get_address_text($address));
                $mailer->assign('geoloc', $geotxt);
                $mailer->send();
            } else if (@$address['changed'] || !@$address['checked']) {
                $success = false;
                $address = array_merge($address, cut_address(@$address['text']));
                $address['checked'] = false;
                $address['geoloc'] = $geotxt;
                $address['geoloc_cityid'] = $new['cityid'];
            } else {
                $address = array_merge($address, cut_address(@$address['text']));
                $address['checked'] = true;
            }
        }
        $address['precise_lat'] = rtrim($address['precise_lat'], '.0');
        $address['precise_lon'] = rtrim($address['precise_lon'], '.0'); 
        $address['text'] = get_address_text($address);
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

    public function __construct(PlWizard &$wiz)
    {
        $this->wizard =& $wiz;
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

    protected function saveData()
    {
        require_once 'notifs.inc.php';
        foreach ($this->settings as $field=>&$setting) {
            if (!is_null($setting) && $this->changed[$field]) {
                $setting->save($this, $field, $this->values[$field]);
            }
            if ($this->changed[$field] && @$this->watched[$field]) {
                register_profile_update(S::i('uid'), $field);
            }
        }
        $this->_saveData();

        // Update the last modification date
        XDB::execute('REPLACE INTO  user_changes
                               SET  user_id = {?}', S::v('uid'));
        if (!S::has('suid')) {
            register_watch_op(S::i('uid'), WATCH_FICHE);
        }
        global $platal;
        $log =& $_SESSION['log'];
        S::logger()->log('profil', $platal->pl_self(1));
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

    protected function _prepare(PlPage &$page, $id)
    {
    }

    public function prepare(PlPage &$page, $id)
    {
        if (count($this->values) == 0) {
            $this->fetchData();
        }
        foreach ($this->values as $field=>&$value) {
            $page->assign($field, $value);
        }
        $this->_prepare($page, $id);
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
                $this->saveData();
                $this->markChange();
            }
            return Post::has('next_page') ? PlWizard::NEXT_PAGE : PlWizard::CURRENT_PAGE;
        }
        Platal::page()->trigError("Certains champs n'ont pas pu être validés, merci de corriger les informations "
                                . "de ton profil et de revalider ta demande");
        return PlWizard::CURRENT_PAGE;
    }
}

require_once dirname(__FILE__) . '/general.inc.php';
require_once dirname(__FILE__) . '/addresses.inc.php';
require_once dirname(__FILE__) . '/groups.inc.php';
require_once dirname(__FILE__) . '/decos.inc.php';
require_once dirname(__FILE__) . '/jobs.inc.php';
require_once dirname(__FILE__) . '/skills.inc.php';
require_once dirname(__FILE__) . '/mentor.inc.php';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
