<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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

class ProfileSecteurs implements ProfileSetting
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
                                  WHERE  m.uid = {?}",
                                S::i('uid'));
            while (list($s, $ss, $ssname) = $res->next()) {
                if (!isset($value[$s])) {
                    $value[$s] = array($ss => $ssname);
                } else {
                    $value[$s][$ss] = $ssname;
                }
            }
        } else if (!is_array($value)) {
            $value = array();
        } else if (count($value) > 10) {
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
                            WHERE  uid = {?}",
                     S::i('uid'));
        if (!count($value)) {
            return;
        }
        foreach ($value as $id=>&$sect) {
            foreach ($sect as $sid=>&$name) {
                XDB::execute("INSERT INTO  profile_mentor_sector (uid, sectorid, subsectorid)
                                   VALUES  ({?}, {?}, {?})",
                             S::i('uid'), $id, $sid);
            }
        }
    }
}

class ProfileCountry implements ProfileSetting
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterRow("SELECT  m.country, p.pays
                                   FROM  profile_mentor_country AS m
                             INNER JOIN  geoloc_pays            AS p ON (m.country = p.a2)
                                  WHERE  m.uid = {?}",
                                S::i('uid'));
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
                            WHERE  uid = {?}",
                     S::i('uid'));
        foreach ($value as $id=>&$name) {
            XDB::execute("INSERT INTO  profile_mentor_country (uid, country)
                               VALUES  ({?}, {?})",
                         S::i('uid'), $id);
        }
    }
}


class ProfileMentor extends ProfilePage
{
    protected $pg_template = 'profile/mentor.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['expertise'] = null;
        $this->settings['secteurs'] = new ProfileSecteurs();
        $this->settings['countries'] = new ProfileCountry();
    }

    protected function _fetchData()
    {
        $res = XDB::query("SELECT  expertise
                             FROM  mentor
                            WHERE  uid = {?}",
                          S::i('uid'));
        $this->values['expertise'] = $res->fetchOneCell();
    }

    protected function _saveData()
    {
        if ($this->changed['expertise']) {
            $expertise = trim($this->values['expertise']);
            if (empty($expertise)) {
                XDB::execute("DELETE FROM  mentor
                                    WHERE  uid = {?}",
                             S::i('uid'));
                $this->values['expertise'] = null;
            } else {
                XDB::execute("REPLACE INTO  mentor (uid, expertise)
                                    VALUES  ({?}, {?})",
                             S::i('uid'), $expertise);
                $this->values['expertise'] = $expertise;
            }
        }
    }

    public function _prepare(PlPage &$page, $id)
    {
        $page->assign('secteurs_sel', XDB::iterator("SELECT  id, name AS label
                                                       FROM  profile_job_sector_enum"));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
