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

class ProfileSection implements ProfileSetting
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            $res = XDB::query("SELECT  section
                                 FROM  auth_user_md5
                                WHERE  user_id = {?}",
                              S::i('uid'));
            return intval($res->fetchOneCell());
        }
        return intval($value);
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        XDB::execute("UPDATE  auth_user_md5
                         SET  section = {?}
                       WHERE  user_id = {?}",
                     $value, S::i('uid'));
    }
}

class ProfileGroup implements ProfileSetting
{
    private $table;
    private $user_field;
    private $group_field;

    public function __construct($table, $user, $group)
    {
        $this->table       = $table;
        $this->user_field  = $user;
        $this->group_field = $group;
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterRow("SELECT  g.id, g.text
                                   FROM  {$this->table}_def AS g
                             INNER JOIN  {$this->table}_ins AS i ON (i.{$this->group_field} = g.id)
                                  WHERE  i.{$this->user_field} = {?}",
                                S::i('uid'));
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

    public function save(ProfilePage &$page, $field, $value)
    {
        XDB::execute("DELETE FROM  {$this->table}_ins
                            WHERE  {$this->user_field} = {?}",
                     S::i('uid'));
        if (!count($value)) {
            return;
        }
        $insert = array();
        foreach ($value as $id=>$text) {
            $insert[] = '(' . S::i('uid') . ", $id)";
        }
        XDB::execute("INSERT INTO  {$this->table}_ins ({$this->user_field}, {$this->group_field})
                           VALUES  " . implode(',', $insert));
    }
}

class ProfileGroups extends ProfilePage
{
    protected $pg_template = 'profile/groups.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['section']  = new ProfileSection();
        $this->settings['binets']   = new ProfileGroup('binets', 'user_id', 'binet_id');
    }

    public function prepare(PlatalPage &$page, $id)
    {
        parent::prepare($page, $id);
        $page->assign('mygroups', XDB::iterator("SELECT  a.nom, a.site, a.diminutif, a.unsub_url, a.pub, m.perms
                                                   FROM  groupex.asso    AS a
                                             INNER JOIN  groupex.membres AS m ON (m.asso_id = a.id)
                                                  WHERE  m.uid = {?} AND (a.cat = 'GroupesX' OR a.cat = 'Institutions')",
                                                  S::i('uid')));
        $page->assign('listgroups', XDB::iterator("SELECT  a.nom, a.diminutif, a.sub_url,
                                                           IF (a.cat = 'Institutions', a.cat, d.nom) AS dom
                                                     FROM  groupex.asso  AS a
                                                LEFT JOIN  groupex.dom   AS d ON (d.id = a.dom)
                                                    WHERE  a.inscriptible != 0
                                                           AND (a.cat = 'GroupesX' OR a.cat = 'Institutions')
                                                 ORDER BY  a.cat, a.dom, a.nom"));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
