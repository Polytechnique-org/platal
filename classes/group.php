<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

class Group
{
    const CAT_GROUPESX     = "GroupesX";
    const CAT_BINETS       = "Binets";
    const CAT_PROMOTIONS   = "Promotions";
    const CAT_INSTITUTIONS = "Institutions";

    public $id;
    public $shortname;
    private $data = array();

    private function __construct(array $data)
    {
        foreach ($data as $key=>$value) {
            $this->data[$key] = $value;
        }
        $this->id = intval($this->data['id']);
        $this->shortname = $this->data['diminutif'];
        if (!is_null($this->axDate)) {
            $this->axDate = format_datetime($this->axDate, '%d/%m/%Y');
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    public function __isset($name)
    {
        return property_exists($this, $name) || isset($this->data[$name]);
    }

    private function getUF($admin = false, $extra_cond = null, $sort = null)
    {
        $cond = new PFC_And(new UFC_Group($this->id, $admin), new PFC_Not(new UFC_Dead()));
        if (!is_null($extra_cond)) {
            $cond->addChild($extra_cond);
        }
        if ($this->cat == self::CAT_PROMOTIONS) {
            $cond->addChild(new UFC_Registered());
        }
        return new UserFilter($cond, $sort);
    }

    public function getMembersFilter($extra_cond = null, $sort = null)
    {
        return $this->getUF(false, $extra_cond, $sort);
    }

    public function getAdminsFilter($extra_cond = null, $sort = null)
    {
        return $this->getUF(true, $extra_cond, $sort);
    }

    public function iterMembers($extra_cond = null, $sort = null, $limit = null)
    {
        $uf = $this->getMembersFilter($extra_cond, $sort);
        return $uf->iterUsers($limit);
    }

    public function iterAdmins($extra_cond = null, $sort = null, $limit = null)
    {
        $uf = $this->getAdminsFilter($extra_cond, $sort);
        return $uf->iterUsers($limit);
    }

    public function iterToNotify()
    {
        if ($this->data['notify_all']) {
            $condition = UFC_Group::BOTH;
        } else {
            $condition = UFC_Group::NOTIFIED;
        }
        $uf = New UserFilter(New UFC_Group($this->id, true, $condition));
        return $uf->iterUsers();
    }

    public function getLogo($fallback = true)
    {
        if (!empty($this->logo)) {
            return PlImage::fromData($this->logo, $this->logo_mime);
        } else if ($fallback) {
            return PlImage::fromFile(dirname(__FILE__).'/../htdocs/images/dflt_carre.jpg', 'image/jpeg');
        }
        return null;
    }

    static public function get($id, $can_be_shortname = true)
    {
        if (!$id) {
            return null;
        }
        if (!$can_be_shortname) {
            $where = XDB::format('a.id = {?}', $id);
        } else {
            $where = XDB::format('a.diminutif = {?}', $id);
        }
        $res = XDB::query('SELECT  a.*, d.nom AS domnom,
                                   FIND_IN_SET(\'wiki_desc\', a.flags) AS wiki_desc,
                                   FIND_IN_SET(\'notif_unsub\', a.flags) AS notif_unsub,
                                   FIND_IN_SET(\'notify_all\', a.flags) AS notify_all,
                                   (nls.id IS NOT NULL) AS has_nl, ad.text AS address,
                                   p.display_tel AS phone, f.display_tel AS fax
                             FROM  groups AS a
                        LEFT JOIN  group_dom  AS d ON d.id = a.dom
                        LEFT JOIN  newsletters AS nls ON (nls.group_id = a.id)
                        LEFT JOIN  profile_phones AS p ON (p.link_type = \'group\' AND p.link_id = a.id AND p.tel_id = 0)
                        LEFT JOIN  profile_phones AS f ON (f.link_type = \'group\' AND f.link_id = a.id AND f.tel_id = 1)
                        LEFT JOIN  profile_addresses AS ad ON (ad.type = \'group\' AND ad.groupid = a.id)
                            WHERE  ' . $where);
        if ($res->numRows() != 1) {
            if ($can_be_shortname && (is_int($id) || ctype_digit($id))) {
                return Group::get($id, false);
            }
            return null;
        }
        $data = $res->fetchOneAssoc();
        $positions = XDB::fetchAllAssoc('SELECT  position, uid
                                           FROM  group_members
                                          WHERE  asso_id = {?} AND position IS NOT NULL
                                       ORDER BY  position',
                                        $data['id']);
        return new Group(array_merge($data, array('positions' => $positions)));
    }

    static public function subscribe($group_id, $uid)
    {
        XDB::execute('DELETE FROM  group_former_members
                            WHERE  uid = {?} AND asso_id = {?}',
                     $uid, $group_id);
        XDB::execute('INSERT IGNORE INTO  group_members (asso_id, uid)
                                  VALUES  ({?}, {?})',
                     $group_id, $uid);
    }

    static public function unsubscribe($group_id, $uid, $remember)
    {
        XDB::execute('INSERT INTO  group_former_members (asso_id, uid, remember, unsubsciption_date)
                           VALUES  ({?}, {?}, {?}, NOW())
          ON DUPLICATE KEY UPDATE  remember = {?}, unsubsciption_date = NOW()',
                     $group_id, $uid, $remember, $remember);
        XDB::execute('DELETE FROM  group_members
                            WHERE  uid = {?} AND asso_id = {?}',
                     $uid, $group_id);
        self::fix_notification($group_id);
    }

    static private function fix_notification($group_id)
    {
        $count = XDB::fetchOneCell("SELECT  COUNT(uid)
                                      FROM  group_members
                                     WHERE  asso_id = {?} AND perms = 'admin' AND FIND_IN_SET('notify', flags)",
                                   $group_id);
        if ($count == 0) {
            XDB::execute("UPDATE  groups
                             SET  flags = IF(flags = '', 'notify_all', CONCAT(flags, ',', 'notify_all'))
                           WHERE  id = {?}",
                         $group_id);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
