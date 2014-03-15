<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

class ProfileSettingManageurs implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
    }

    public function getText($value) {
    }
}

class ProfileSettingManageursPush implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT m.push
                                          FROM profile_manageurs AS m
                                         WHERE m.pid = {?}",
                                       $page->pid());
        }
        else {
            switch ($value) {
            case 'unique':
                break;
            case 'weekly':
                break;
            case 'never':
                break;
            case 0:
                $value = 'unique';
                break;
            case 1:
                $value = 'weekly';
                break;
            default:
                $value = 'never';
            }
        }
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("UPDATE profile_manageurs AS m
                         SET m.push = {?}
                       WHERE m.pid = {?}",
                     $value, $page->pid());
    }

    public function getText($value) {
        return $value;
    }
}

class ProfileSettingManageursCommunication implements ProfileSetting
{
    private $communication_field;

    public function __construct($communication_field)
    {
        $this->communication_field = $communication_field;
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT FIND_IN_SET({?}, m.communication)
                                          FROM profile_manageurs AS m
                                         WHERE m.pid = {?}",
                                       $this->communication_field, $page->pid());
        }

        $value = (bool) $value;
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
      $modified = false;  
      $res = XDB::fetchOneCell("SELECT m.communication
                                  FROM profile_manageurs AS m
                                 WHERE m.pid = {?}",
                                $page->pid());
      $res = explode(',', $res);
      $index = array_search($this->communication_field, $res);
      if ($index === false) {
        if ($value) {
            $res[] = $this->communication_field;
            $modified = true;
        }
      }
      else if (!$value) {
          unset($res[$index]);
          $modified = true;
      }

      if ($modified) {
          $res = implode(',', $res);
          XDB::execute("UPDATE profile_manageurs AS m
                           SET m.communication = {?}
                         WHERE m.pid = {?}",
                       $res, $page->pid());
      }
    }

    public function getText($value) {
        if ($value) {
            return "inscrit";
        }
        else {
            return "non inscrit";
        }
    }
}

class ProfileSettingManageursBool implements ProfileSetting
{
    private $dbfield;

    public function __construct($dbfield)
    {
        $this->dbfield = $dbfield;
    }
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT m." . $this->dbfield .
                                        " FROM profile_manageurs AS m
                                         WHERE m.pid = {?}",
                                       $page->pid());
            $value = $value == 1;
        }

        $value = (bool) $value;
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        if ($value) {
          XDB::execute("UPDATE profile_manageurs AS m
                           SET m." . $this->dbfield . " = 1
                         WHERE m.pid = {?}",
                       $page->pid());
        }
        else {
          XDB::execute("UPDATE profile_manageurs AS m
                           SET m." . $this->dbfield . " = 0
                         WHERE m.pid = {?}",
                       $page->pid());
        }
    }

    public function getText($value) {
        if ($value) {
            return "yes";
        }
        else {
            return "no";
        }
    }
}

class ProfilePageManageurs extends ProfilePage
{
    protected $pg_template = 'profile/manageurs.tpl';

    public function __construct(PlWizard $wiz)
    {
        parent::__construct($wiz);
        $this->settings['manageurs_title'] = null;
        $this->settings['manageurs_entry_year'] = null;
        $this->settings['manageurs_project'] = null;
        $this->settings['manageurs_visibility'] = null;
        $this->settings['manageurs_email'] = null;
        $this->settings['manageurs_push'] = new ProfileSettingManageursPush();
        $this->settings['manageurs_anonymity'] = new ProfileSettingManageursBool('anonymity');
        $this->settings['manageurs_novelty'] = new ProfileSettingManageursCommunication('novelty');
        $this->settings['manageurs_nl'] = new ProfileSettingManageursCommunication('nl');
        $this->settings['manageurs_survey'] = new ProfileSettingManageursCommunication('survey');
        $this->settings['manageurs_network'] = new ProfileSettingManageursBool('network');
    }

    protected function _fetchData()
    {
        $res = XDB::query("SELECT  title AS manageurs_title, entry_year AS manageurs_entry_year, project AS manageurs_project,
                                   visibility AS manageurs_visibility, email AS manageurs_email
                             FROM  profile_manageurs
                            WHERE  pid = {?}",
                          $this->pid());
        $this->values = $res->fetchOneAssoc();
    }

    protected function _saveData()
    {
        if ($this->changed['manageurs_title']) {
          XDB::execute('UPDATE profile_manageurs
                           SET title = {?}
                         WHERE pid = {?}',
                        $this->values['manageurs_title'], $this->pid());
        }

        if ($this->changed['manageurs_entry_year']) {
          XDB::execute('UPDATE profile_manageurs
                           SET entry_year = {?}
                         WHERE pid = {?}',
                        $this->values['manageurs_entry_year'], $this->pid());
        }

        if ($this->changed['manageurs_project']) {
          XDB::execute('UPDATE profile_manageurs
                           SET project = {?}
                         WHERE pid = {?}',
                        $this->values['manageurs_project'], $this->pid());
        }

        if ($this->changed['manageurs_visibility']) {
          XDB::execute('UPDATE profile_manageurs
                           SET visibility = {?}
                         WHERE pid = {?}',
                        $this->values['manageurs_visibility'], $this->pid());
        }

        if ($this->changed['manageurs_email']) {
          XDB::execute('UPDATE profile_manageurs
                           SET email = {?}
                         WHERE pid = {?}',
                        $this->values['manageurs_email'], $this->pid());
        }
    }

    public function _prepare(PlPage $page, $id)
    {
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
