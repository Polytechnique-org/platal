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

class ProfileSettingManageursYear implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT m.entry_year
                                          FROM profile_manageurs AS m
                                         WHERE pid = {?}",
                                        $page->pid());
        } else {
            if (($value < 1921) || ($value > (date('Y') + 4))) {
                    Platal::page()->trigWarning('L\'année de début d\'activité professionnelle est mal ou non renseignée, elle doit être du type : 2004.');
                    $success = (is_null($value) || ($value == '')); // Even if it triggers a warning, the null value is valid.
                    $value = null;
            }
        }
        
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("INSERT INTO profile_manageurs (pid, title, entry_year, project, anonymity, visibility, email, communication, push, network)
                           VALUES ({?},{?},{?},{?},{?},{?},{?},{?},{?},{?})
          ON DUPLICATE KEY UPDATE entry_year = VALUES(entry_year)",
                     $page->pid(), '', $value, '', 0, 'blocked', '', '', 'never', 0);
    }

    public function getText($value) {
    }
}

// Handles title, project and email fields
class ProfileSettingManageursText implements ProfileSetting
{
    private $dbfield;

    public function __construct($dbfield)
    {
        $this->dbfield = $dbfield;
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT m." . $this->dbfield .
                                        " FROM profile_manageurs AS m
                                         WHERE m.pid = {?}",
                                       $page->pid());
        }
        else if ($this->dbfield == 'email') {
            $success = (($value == '') || isvalid_email($value));
            if (!isvalid_email($value)) {
                Platal::page()->trigWarning('Le champ email n\'est pas correctement renseigné.');
            }
        } else {
            $success = true;
        }
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        $default = array('title' => '', 'project' => '', 'email' => '');
        $default[$this->dbfield] = $value;
        XDB::execute("INSERT INTO profile_manageurs (pid, title, entry_year, project, anonymity, visibility, email, communication, push, network)
                           VALUES ({?},{?},{?},{?},{?},{?},{?},{?},{?},{?})
          ON DUPLICATE KEY UPDATE " . $this-> dbfield . " = VALUES(" . $this->dbfield .")",
                     $page->pid(), $default['title'], null, $default['project'], 0, 'blocked', $default['email'], '', 'never', 0);
    }

    public function getText($value) {
        return $value;
    }
}

class ProfileSettingManageursVisibility implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT m.visibility
                                          FROM profile_manageurs AS m
                                         WHERE m.pid = {?}",
                                       $page->pid());
            if (is_null($value)) {
                $value = 'blocked';
            }
        }
        $success = in_array($value, array('visible', 'visible_exceptions', 'blocked'));
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("INSERT INTO profile_manageurs (pid, title, entry_year, project, anonymity, visibility, email, communication, push, network)
                           VALUES ({?},{?},{?},{?},{?},{?},{?},{?},{?},{?})
          ON DUPLICATE KEY UPDATE visibility = VALUES(visibility)",
                     $page->pid(), '', null, '', 0, $value, '', '', 'never', 0);
    }

    public function getText($value) {
        return $value;
    }
}

class ProfileSettingManageursPush implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT m.push
                                          FROM profile_manageurs AS m
                                         WHERE m.pid = {?}",
                                       $page->pid());
            if (is_null($value)) {
                $value = 'never';
            }
        }
        $success = in_array($value, array('unique', 'weekly', 'never'));
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("INSERT INTO profile_manageurs (pid, title, entry_year, project, anonymity, visibility, email, communication, push, network)
                           VALUES ({?},{?},{?},{?},{?},{?},{?},{?},{?},{?})
          ON DUPLICATE KEY UPDATE push = VALUES(push)",
                     $page->pid(), '', null, '', 0, 'blocked', '', '', $value, 0);
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
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT FIND_IN_SET({?}, m.communication)
                                          FROM profile_manageurs AS m
                                         WHERE m.pid = {?}",
                                       $this->communication_field, $page->pid());
        }
        $value = (bool) $value;
        $success = true;
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
          XDB::execute("INSERT INTO profile_manageurs (pid, title, entry_year, project, anonymity, visibility, email, communication, push, network)
                             VALUES ({?},{?},{?},{?},{?},{?},{?},{?},{?},{?})
            ON DUPLICATE KEY UPDATE communication = VALUES(communication)",
                        $page->pid(), '', null, '', 0, 'blocked', '', $res, 'never', 0);
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

// Handles anonymity and network fields
class ProfileSettingManageursBool implements ProfileSetting
{
    private $dbfield;

    public function __construct($dbfield)
    {
        $this->dbfield = $dbfield;
    }
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = XDB::fetchOneCell("SELECT m." . $this->dbfield .
                                        " FROM profile_manageurs AS m
                                         WHERE m.pid = {?}",
                                       $page->pid());
            $value = $value == 1;
        }

        $value = (bool) $value;
        $success = true;
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        $default = array('anonymity' => 0, 'network' => 0);
        $default[$this->dbfield] = (($value) ? 1 : 0);
        XDB::execute("INSERT INTO profile_manageurs (pid, title, entry_year, project, anonymity, visibility, email, communication, push, network)
                           VALUES ({?},{?},{?},{?},{?},{?},{?},{?},{?},{?})
          ON DUPLICATE KEY UPDATE " . $this->dbfield . " = VALUES(" . $this->dbfield . ")",
                     $page->pid(), '', null, '', $default['anonymity'], 'blocked', '', '', 'never', $default['network']);
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
        $this->settings['manageurs_title'] = new ProfileSettingManageursText('title');
        $this->settings['manageurs_entry_year'] = new ProfileSettingManageursYear();
        $this->settings['manageurs_project'] = new ProfileSettingManageursText('project');
        $this->settings['manageurs_visibility'] = new ProfileSettingManageursVisibility();
        $this->settings['manageurs_email'] = new ProfileSettingManageursText('email');
        $this->settings['manageurs_push'] = new ProfileSettingManageursPush();
        $this->settings['manageurs_anonymity'] = new ProfileSettingManageursBool('anonymity');
        $this->settings['manageurs_novelty'] = new ProfileSettingManageursCommunication('novelty');
        $this->settings['manageurs_nl'] = new ProfileSettingManageursCommunication('nl');
        $this->settings['manageurs_survey'] = new ProfileSettingManageursCommunication('survey');
        $this->settings['manageurs_network'] = new ProfileSettingManageursBool('network');
    }

    protected function _fetchData()
    {
    }

    protected function _saveData()
    {
    }

    public function _prepare(PlPage $page, $id)
    {
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
