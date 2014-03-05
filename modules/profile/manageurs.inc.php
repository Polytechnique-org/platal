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

class ProfileSettingManageursNetwork implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterRow("SELECT  m.country, gc.country
                                   FROM  profile_mentor_country AS m
                             INNER JOIN  geoloc_countries       AS gc ON (m.country = gc.iso_3166_1_a2)
                                  WHERE  m.pid = {?}",
                                $page->pid());
            while (list($id, $name) = $res->next()) {
                $value[$id] = $name;
            }
        } else if (!is_array($value)) {
            $value = array();
        } else if (count($value) > 10) {
            Platal::page()->trigError("Le nombre de secteurs d'expertise est limité à 10");
            $success = false;
        }
        ksort($value);
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("DELETE FROM  profile_mentor_country
                            WHERE  pid = {?}",
                     $page->pid());
        foreach ($value as $id=>&$name) {
            XDB::execute("INSERT INTO  profile_mentor_country (pid, country)
                               VALUES  ({?}, {?})",
                         $page->pid(), $id);
        }
    }

    public function getText($value) {
        return implode(', ', $value);
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
        $this->settings['manageurs_push'] = null;
        $this->settings['manageurs_anonymity'] = null;
        $this->settings['manageurs_novelty'] = null;
        $this->settings['manageurs_nl'] = null;
        $this->settings['manageurs_survey'] = null;
        $this->settings['manageurs_network'] = null;
    }

    protected function _fetchData()
    {
        $res = XDB::query("SELECT  title AS manageurs_title, entry_year AS manageurs_entry_year, project AS manageurs_project,
                                   anonymity AS manageurs_anonymity, visibility AS manageurs_visibility, email AS manageurs_email,
                                   communication AS manageurs_communication, push AS manageurs_push, network AS manageurs_network
                             FROM  profile_manageurs
                            WHERE  pid = {?}",
                          $this->pid());
        $this->values = $res->fetchOneAssoc();

        $this->values['manageurs_anonymity'] = $this->values['manageurs_anonymity'] == 1; 

        $communication = explode(',', $this->values['manageurs_communication']);

        $this->values['manageurs_novelty'] = in_array('novelties', $communication);
        $this->values['manageurs_nl'] = in_array('nl',$communication);
        $this->values['manageurs_survey'] = in_array('survey',$communication);

        $this->values['manageurs_network'] = $this->values['manageurs_network'] == 1;
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

        if ($this->changed['manageurs_push']) {
          XDB::execute('UPDATE profile_manageurs
                           SET push = {?}
                         WHERE pid = {?}',
                        $this->values['manageurs_push'], $this->pid());
        }

        if ($this->changed['manageurs_anonymity']) {
          XDB::execute('UPDATE profile_manageurs
                           SET anonymity = {?}
                         WHERE pid = {?}',
                        $this->values['manageurs_anonymity'], $this->pid());
        }

        if ($this->changed['manageurs_novelty'] || $this->changed['manageurs_nl'] || $this->changed['manageurs_survey']) {
            $communication = array();
            if ($this->values['manageurs_novelty']) {
                $communication[] = 'novelties';
            }
            if ($this->values['manageurs_nl']) {
                $communication[] = 'nl';
            }
            if ($this->values['manageurs_survey']) {
                $communication[] = 'survey';
            }
            $communicationStr = implode(',', $communication);
            
          XDB::execute('UPDATE profile_manageurs
                           SET communication = {?}
                         WHERE pid = {?}',
                        $communicationStr, $this->pid());
        }

        if ($this->changed['manageurs_network']) {
          XDB::execute('UPDATE profile_manageurs
                           SET network = {?}
                         WHERE pid = {?}',
                        $this->values['manageurs_network'], $this->pid());
        }
    }

    public function _prepare(PlPage $page, $id)
    {
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
