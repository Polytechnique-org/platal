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

class ProfileSettingSection implements ProfileSetting
{
    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $res = XDB::query("SELECT  section
                                 FROM  profiles
                                WHERE  pid = {?}",
                              $page->pid());
            return intval($res->fetchOneCell());
        }
        return intval($value);
    }

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("UPDATE  profiles
                         SET  section = {?}
                       WHERE  pid = {?}",
                     ($value == 0) ? null : $value, $page->pid());
    }

    public function getText($value) {
        $sectionsList = DirEnum::getOptions(DirEnum::SECTIONS);
        return $sectionsList[$value];
    }
}

class ProfileSettingBinets implements ProfileSetting
{
    public function __construct()
    {
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterRow("SELECT  g.id, g.text
                                   FROM  profile_binet_enum AS g
                             INNER JOIN  profile_binets AS i ON (i.binet_id = g.id)
                                  WHERE  i.pid = {?}",
                                $page->pid());
            while (list($gid, $text) = $res->next()) {
                $value[intval($gid)] = $text;
            }
        }
        if (!is_array($value)) {
            $value = array();
        }
        ksort($value);
        $success = true;
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        XDB::execute("DELETE FROM  profile_binets
                            WHERE  pid = {?}",
                     $page->pid());
        if (!count($value)) {
            return;
        }
        $insert = array();
        foreach ($value as $id=>$text) {
            $insert[] = XDB::format('({?}, {?})', $page->pid(), $id);
        }
        XDB::execute("INSERT INTO  profile_binets (pid, binet_id)
                           VALUES  " . implode(',', $insert));
    }

    public function getText($value) {
        return implode(', ', $value);
    }
}

class ProfilePageGroups extends ProfilePage
{
    protected $pg_template = 'profile/groups.tpl';

    public function __construct(PlWizard $wiz)
    {
        parent::__construct($wiz);
        $this->settings['section']  = new ProfileSettingSection();
        $this->settings['binets']   = new ProfileSettingBinets();
        $this->watched['section'] = $this->watched['binets'] = true;
    }

    public function _prepare(PlPage $page, $id)
    {
        $page->assign('mygroups', XDB::iterator("SELECT  a.nom, a.site, a.diminutif, a.unsub_url, a.pub, m.perms
                                                   FROM  groups    AS a
                                             INNER JOIN  group_members AS m ON (m.asso_id = a.id)
                                                  WHERE  m.uid = {?} AND (a.cat = 'GroupesX' OR a.cat = 'Institutions')",
                                                $this->owner->id()));
        $page->assign('listgroups', XDB::iterator("SELECT  a.nom, a.diminutif, a.sub_url,
                                                           IF (a.cat = 'Institutions', a.cat, d.nom) AS dom
                                                     FROM  groups  AS a
                                                LEFT JOIN  group_dom   AS d ON (d.id = a.dom)
                                                    WHERE  a.inscriptible != 0
                                                           AND (a.cat = 'GroupesX' OR a.cat = 'Institutions')
                                                 ORDER BY  a.cat, a.dom, a.nom"));
        $page->assign('old', (int) date('Y') >= $this->profile->grad_year);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
