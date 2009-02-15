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

class Profile
{
    private $pid;
    private $hrpid;
    private $data = array();

    private function __construct(array $data)
    {
        $this->data = $data;
        $this->pid = $this->data['pid'];
        $this->hrpid = $this->data['hrpid'];
    }

    public function id()
    {
        return $this->pid;
    }

    public function hrid()
    {
        return $this->hrpid;
    }

    public function promo()
    {
        return $this->promo;
    }

    /** Print a name with the given formatting:
     * %s = • for women
     * %f = firstname
     * %l = lastname
     * %F = fullname
     * %S = shortname
     * %p = promo
     */
    public function name($format)
    {
        return str_replace(array('%s', '%f', '%l', '%F', '%S', '%p'),
                           array($this->isFemale() ? '•' : '',
                                 $this->first_name, $this->last_name,
                                 $this->full_name, $this->short_name,
                                 $this->promo), $format);
    }

    public function fullName($with_promo = false)
    {
        if ($with_promo) {
            return $this->full_name . ' (' . $this->promo . ')';
        }
        return $this->full_name;
    }

    public function shortName($with_promo = false)
    {
        if ($with_promo) {
            return $this->short_name . ' (' . $this->promo . ')';
        }
        return $this->short_name;
    }

    public function firstName()
    {
        return $this->firstname;
    }

    public function lastName()
    {
        return $this->lastname;
    }

    public function isFemale()
    {
        return $this->sex == PlUser::GENDER_FEMALE;
    }

    public function data()
    {
        $this->first_name;
        return $this->data;
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


    public function owner()
    {
        return User::getSilent($this);
    }

    private static function fetchProfileData(array $pids)
    {
        if (count($pids) == 0) {
            return array();
        }
        return XDB::fetchAllAssoc('SELECT  p.*, p.sex = \'female\' AS sex, pe.entry_year, pe.grad_year,
                                           pn_f.name AS firstname, pn_l.name AS lastname, pn_n.name AS nickname,
                                           IF(pn_uf.name IS NULL, pn_f.name, pn_uf.name) AS firstname_usual,
                                           IF(pn_ul.name IS NULL, pn_l.name, pn_ul.name) AS lastname_usual,
                                           pd.promo AS promo, pd.short_name, pd.directory_name AS full_name
                                     FROM  profiles AS p
                               INNER JOIN  profile_display AS pd ON (pd.pid = p.pid)
                               INNER JOIN  profile_education AS pe ON (pe.uid = p.pid AND FIND_IN_SET(\'primary\', pe.flags))
                               INNER JOIN  profile_name AS pn_f ON (pn_f.pid = p.pid
                                                                    AND pn_f.typeid = ' . self::getNameTypeId('lastname', true) . ')
                               INNER JOIN  profile_name AS pn_l ON (pn_l.pid = p.pid
                                                                    AND pn_l.typeid = ' . self::getNameTypeId('firstname', true) . ')
                                LEFT JOIN  profile_name AS pn_uf ON (pn_uf.pid = p.pid
                                                                     AND pn_uf.typeid = ' . self::getNameTypeId('lastname_ordinary', true) . ')
                                LEFT JOIN  profile_name AS pn_ul ON (pn_ul.pid = p.pid
                                                                     AND pn_ul.typeid = ' . self::getNameTypeId('firstname_ordinary', true) . ')
                                LEFT JOIN  profile_name aS pn_n ON (pn_n.pid = p.pid 
                                                                    AND pn_n.typeid = ' . self::getNameTypeId('nickname', true) . ')
                                    WHERE  p.pid IN ' . XDB::formatArray($pids) . '
                                 GROUP BY  p.pid');
    }

    public static function getPID($login)
    {
        if ($login instanceof PlUser) {
            return XDB::fetchOneCell('SELECT  pid
                                        FROM  account_profiles
                                       WHERE  uid = {?} AND FIND_IN_SET(\'owner\', perms)',
                                     $login->id());
        } else if (ctype_digit($login)) {
            return XDB::fetchOneCell('SELECT  pid
                                        FROM  profiles
                                       WHERE  pid = {?}', $login);
        } else {
            return XDB::fetchOneCell('SELECT  pid
                                        FROM  profiles
                                       WHERE  hrpid = {?}', $login);
        }
    }


    /** Return the profile associated with the given login.
     */
    public static function get($login)
    {
        $pid = self::getPID($login);
        if (!is_null($pid)) {
            $data = self::fetchProfileData(array($pid));
            return new Profile(array_pop($data));
        } else {
            /* Let say we can identify a profile using the identifiers of its owner.
             */
            if (!($login instanceof PlUser)) {
                $user = User::getSilent($login);
                if ($user && $user->hasProfile()) {
                    return $user->profile();
                }
            }
            return null;
        }
    }

    /** Return profiles for the list of pids.
     */
    public static function getBulkProfilesWithPIDs(array $pids)
    {
        if (count($pids) == 0) {
            return array();
        }
        $data = self::fetchProfileData($pids);
        $inv = array_flip($pids);
        $profiles = array();
        foreach ($data AS $p) {
            $p = new Profile($p);
            $key = $inv[$p->id()];
            $profiles[$key] = $p;
        }
        return $profiles;
    }

    /** Return profiles for uids.
     */
    public static function getBulkProfilesWithUIDS(array $uids)
    {
        if (count($uids) == 0) {
            return array();
        }
        $table = XDB::fetchAllAssoc('uid', 'SELECT  ap.uid, ap.pid
                                              FROM  account_profiles AS ap
                                             WHERE  FIND_IN_SET(\'owner\', ap.perms)
                                                    AND ap.uid IN ' . XDB::formatArray($uids));
        return self::getBulkProfilesWithPIDs($table);
    }

    public static function getNameTypeId($type, $for_sql = false)
    {
        if (!S::has('name_types')) {
            $table = XDB::fetchAllAssoc('type', 'SELECT  id, type
                                                   FROM  profile_name_enum');
            S::set('name_types', $table);
        } else {
            $table = S::v('name_types');
        }
        if ($for_sql) {
            return XDB::escape($table[$type]);
        } else {
            return $table[$type];
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
