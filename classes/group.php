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

class Group
{
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
        $cond = new UFC_Group($this->id, $admin);
        if (!is_null($extra_cond)) {
            $cond = new UFC_And($cond, $extra_cond);
        }
        return new UserFilter($cond, $sort);
    }

    public function getMembers($extra_cond = null, $sort = null)
    {
        return $this->getUF(false, $extra_cond, $sort);
    }

    public function getAdmins($extra_cond = null, $sort = null)
    {
        return $this->getUF(true, $extra_cond, $sort);
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

    static public function get($id)
    {
        if (!$id) {
            return null;
        }
        if (ctype_digit($id)) {
            $where = XDB::format('id = {?}', $id);
        } else {
            $where = XDB::format('diminutif = {?}', $id);
        }
        $res = XDB::query('SELECT  a.*, d.nom AS domnom,
                                   FIND_IN_SET(\'wiki_desc\', a.flags) AS wiki_desc,
                                   FIND_IN_SET(\'notif_unsub\', a.flags) AS notif_unsub
                             FROM  groups AS a
                        LEFT JOIN  group_dom  AS d ON d.id = a.dom
                            WHERE  ' . $where);
        if ($res->numRows() != 1) {
            return null;
        }
        return new Group($res->fetchOneAssoc());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
