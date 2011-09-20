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

class ProfileSettingSkill implements ProfileSetting
{
    private $table;
    private $id;
    private $skill_field;
    private $text_field;

    public function __construct($table, $id, $skill, $text)
    {
        $this->table = $table;
        $this->id = $id;
        $this->skill_field = $skill;
        $this->text_field = $text;
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterRow("SELECT  s.{$this->id}, s.{$this->text_field}, i.level
                                   FROM  profile_{$this->table}_enum AS s
                             INNER JOIN  profile_{$this->table}s     AS i ON (s.{$this->id} = i.{$this->skill_field})
                                  WHERE  i.pid = {?}",
                                $page->pid());
            while (list($sid, $text, $level) = $res->next()) {
                $value[$sid] = array('text' => $text, 'level' => $level);
            }
        }
        if (!is_array($value)) {
            $value = array();
        } else {
            foreach ($value as $id=>&$skill) {
                if (!isset($skill['text']) || empty($skill['text'])) {
                    $res = XDB::query("SELECT  {$this->text_field}
                                         FROM  profile_{$this->table}_enum
                                        WHERE  {$this->id} = {?}", $id);
                    $skill['text'] = $res->fetchOneCell();
                }
            }
        }
        ksort($value);
        $success = true;
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("DELETE FROM  profile_{$this->table}s
                            WHERE  pid = {?}",
                     $page->pid());
        if (!count($value)) {
            return;
        }
        foreach ($value as $id=>&$skill) {
            XDB::execute("INSERT INTO  profile_{$this->table}s (pid, {$this->skill_field}, level)
                               VALUES  ({?}, {?}, {?})",
                         $page->pid(), $id, $skill['level']);
        }
    }

    public function getText($value) {
        $skills = array();

        if ($this->table == 'langskill') {
            static $levels = array(
                1 => 'connaissance basique',
                2 => 'maîtrise des bases',
                3 => 'maîtrise limitée',
                4 => 'maîtrise générale',
                5 => 'bonne maîtrise',
                6 => 'maîtrise complète'
            );
            foreach ($value as $skill) {
                $skills[] = $skill['text'] . ' (' . $levels[$skill['level']] . ')';
            }
        } else {
            foreach ($value as $skill) {
                $skills[] = $skill['text'] . ' (' . $skill['level'] . ')';
            }
        }

        return implode(', ' , $skills);
    }
}

/** Terms associated to profile mentoring */
class ProfileSettingTerms implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $value = array();
            $res = XDB::query('SELECT  e.jtid, e.full_name
                                 FROM  profile_mentor_term   AS m
                           INNER JOIN  profile_job_term_enum AS e  ON (m.jtid = e.jtid)
                                WHERE  m.pid = {?}',
                                $page->pid());
            $value = $res->fetchAllAssoc();
        } elseif (!is_array($value)) {
            $value = array();
        } elseif (count($value) > 20) {
            Platal::page()->trigError("Le nombre de mots clefs d'expertise est limité à 20.");
            $success = false;
        } else {
            $missing_full_names = array();
            foreach ($value as &$term) if (empty($term['full_name'])) {
                $missing_full_names[] = $term['jtid'];
            }
            if (count($missing_full_names)) {
                $res = XDB::query('SELECT  jtid, full_name
                                     FROM  profile_job_term_enum
                                    WHERE  jtid IN {?}',
                                    $missing_full_names);
                $term_id_to_name = $res->fetchAllAssoc('jtid', false);
                foreach ($value as &$term) {
                    if (empty($term['full_name'])) {
                        $term['full_name'] = $term_id_to_name[$term['jtid']];
                    }
                }
            }
        }
        ksort($value);
        foreach ($value as &$sss) {
            ksort($sss);
        }
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {

        XDB::execute("DELETE FROM  profile_mentor_term
                            WHERE  pid = {?}",
                     $page->pid());
        if (!count($value)) {
            return;
        }
        $mentor_term_values = array();
        foreach ($value as &$term) {
            $mentor_term_values[] = '('.XDB::escape($page->pid()).', '.XDB::escape($term['jtid']).')';
        }
        XDB::execute('INSERT INTO  profile_mentor_term (pid, jtid)
                           VALUES  '.implode(',', $mentor_term_values));

    }

    public function getText($value) {
        $terms = array();
        foreach ($value as &$term) {
            $terms[] = $term['full_name'];
        }
        return implode(', ', $terms);
    }
}

class ProfileSettingCountry implements ProfileSetting
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


class ProfilePageMentor extends ProfilePage
{
    protected $pg_template = 'profile/mentor.tpl';

    public function __construct(PlWizard $wiz)
    {
        parent::__construct($wiz);
        $this->settings['expertise'] = null;
        $this->settings['terms'] = new ProfileSettingTerms();
        $this->settings['countries'] = new ProfileSettingCountry();
        $this->settings['competences'] = new ProfileSettingSkill('skill', 'id', 'cid', 'text_fr');
        $this->settings['langues'] = new ProfileSettingSkill('langskill', 'iso_639_2b', 'lid', 'language');
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
                XDB::execute('INSERT INTO  profile_mentor (pid, expertise)
                                   VALUES  ({?}, {?})
                  ON DUPLICATE KEY UPDATE  expertise = VALUES(expertise)',
                             $this->pid(), $expertise);
                $this->values['expertise'] = $expertise;
            }
        }
    }

    public function _prepare(PlPage $page, $id)
    {
        $page->assign('countryList', XDB::iterator("SELECT  iso_3166_1_a2, country
                                                      FROM  geoloc_countries
                                                  ORDER BY  country"));
        $page->assign('hrpid', $this->profile->hrpid);
        $page->assign('comp_list', XDB::iterator("SELECT  id, text_fr, FIND_IN_SET('titre',flags) AS title
                                                    FROM  profile_skill_enum"));
        $page->assign('comp_level', array('initié' => 'initié',
                                          'bonne connaissance' => 'bonne connaissance',
                                          'expert' => 'expert'));
        $page->assign('lang_list', XDB::iterator('SELECT  iso_639_2b, language
                                                    FROM  profile_langskill_enum
                                                ORDER BY  language'));
        $page->assign('lang_level', array(1 => 'connaissance basique',
                                          2 => 'maîtrise des bases',
                                          3 => 'maîtrise limitée',
                                          4 => 'maîtrise générale',
                                          5 => 'bonne maîtrise',
                                          6 => 'maîtrise complète'));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
