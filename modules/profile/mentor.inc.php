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

class ProfileSettingSectors implements ProfileSetting
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterRow("SELECT  m.sectorid, m.subsectorid, ss.name
                                   FROM  profile_mentor_sector      AS m
                             INNER JOIN  profile_job_sector_enum    AS s  ON (m.sectorid = s.id)
                             INNER JOIN  profile_job_subsector_enum AS ss ON (s.id = ss.sectorid AND m.subsectorid = ss.id)
                                  WHERE  m.pid = {?}",
                                $page->pid());
            while (list($s, $ss, $ssname) = $res->next()) {
                if (!isset($value[$s])) {
                    $value[$s] = array($ss => $ssname);
                } else {
                    $value[$s][$ss] = $ssname;
                }
            }
        } elseif (!is_array($value)) {
            $value = array();
        } elseif (count($value) > 10) {
            Platal::page()->trigError("Le nombre de secteurs d'expertise est limité à 10.");
            $success = false;
        }
        ksort($value);
        foreach ($value as &$sss) {
            ksort($sss);
        }
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {

        XDB::execute("DELETE FROM  profile_mentor_sector
                            WHERE  pid = {?}",
                     $page->pid());
        if (!count($value)) {
            return;
        }
        foreach ($value as $id => $sect) {
            foreach ($sect as $sid => $name) {
                XDB::execute("INSERT INTO  profile_mentor_sector (pid, sectorid, subsectorid)
                                   VALUES  ({?}, {?}, {?})",
                             $page->pid(), $id, $sid);
            }
        }
    }

    public function getText($value) {
        $sectors = array();
        foreach ($value as $sector) {
            foreach ($sector as $subsector) {
                $sectors[] = $subsector;
            }
        }
        return implode(', ', $sectors);
    }
}

class ProfileSettingCountry implements ProfileSetting
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterRow("SELECT  m.country, gc.countryFR
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

    public function save(ProfilePage &$page, $field, $value)
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


class ProfileSettingMentor extends ProfilePage
{
    protected $pg_template = 'profile/mentor.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['expertise'] = null;
        $this->settings['sectors'] = new ProfileSettingSectors();
        $this->settings['countries'] = new ProfileSettingCountry();
    }

    protected function _fetchData()
    {
        $res = XDB::query("SELECT  expertise
                             FROM  profile_mentor
                            WHERE  pid = {?}",
                          $this->pid());
        $this->values['expertise'] = $res->fetchOneCell();
    }

    protected function _saveData()
    {
        if ($this->changed['expertise']) {
            $expertise = trim($this->values['expertise']);
            if (empty($expertise)) {
                XDB::execute("DELETE FROM  profile_mentor
                                    WHERE  pid = {?}",
                             $this->pid());
                $this->values['expertise'] = null;
            } else {
                XDB::execute("REPLACE INTO  profile_mentor (pid, expertise)
                                    VALUES  ({?}, {?})",
                             $this->pid(), $expertise);
                $this->values['expertise'] = $expertise;
            }
        }
    }

    public function _prepare(PlPage &$page, $id)
    {
        $page->assign('sectorList', XDB::iterator('SELECT  id, name
                                                     FROM  profile_job_sector_enum'));

        $page->assign('countryList', XDB::iterator("SELECT  iso_3166_1_a2, countryFR
                                                      FROM  geoloc_countries
                                                  ORDER BY  countryFR"));
        $page->assign('hrpid', $this->profile->hrpid);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
