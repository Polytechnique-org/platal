<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

    private function __construct($login)
    {
        if ($login instanceof PlUser) {
            $from  = 'account_profiles AS ap
                INNER JOIN profiles AS p ON (p.pid = ap.pid)';
            $where = XDB::format('ap.uid = {?} AND FIND_IN_SET(\'owner\', ap.perms)', $login->id());
        } else if (is_numeric($login)) {
            $from = 'profiles AS p';
            $where = XDB::format('p.pid = {?}', $login);
        } else {
            $from = 'profiles AS p';
            $where = XDB::format('p.hrpid = {?}', $login);
        }
        $res = XDB::query('SELECT  p.*, pe.entry_year, pe.grad_year,
                                   pns_f.name AS firstname, pns_l.name AS lastname, pns_n.name AS nickname,
                                   IF(pns_uf.name IS NULL, pns_f.name, pns_uf.name) AS firstname_usual,
                                   IF(pns_ul.name IS NULL, pns_l.name, pns_ul.name) AS lastname_usual,
                                   pd.promo AS promo, pd.short_name, pd.directory_name AS full_name
                             FROM  ' . $from . '
                       INNER JOIN  profile_display AS pd ON (pd.pid = p.pid)
                       INNER JOIN  profile_education AS pe ON (pe.uid = p.pid AND FIND_IN_SET(\'primary\', pe.flags))
                       INNER JOIN  profile_name_search AS pns_f ON (pns_f.pid = p.pid AND pns_f.typeid = ' . self::getNameTypeId('Nom patronymique', true) . ')
                       INNER JOIN  profile_name_search AS pns_l ON (pns_l.pid = p.pid AND pns_l.typeid = ' . self::getNameTypeId('Prénom', true) . ')
                        LEFT JOIN  profile_name_search AS pns_uf ON (pns_uf.pid = p.pid AND pns_uf.typeid = ' . self::getNameTypeId('Prénom usuel', true) . ')
                        LEFT JOIN  profile_name_search AS pns_ul ON (pns_ul.pid = p.pid AND pns_ul.typeid = ' . self::getNameTypeId('Nom usuel', true) . ')
                        LEFT JOIN  profile_name_search aS pns_n ON (pns_n.pid = p.pid AND pns_n.typeid = ' . self::getNameTypeId('Surnom', true) . ')
                            WHERE  ' . $where);
        if ($res->numRows() != 1) {
            throw new UserNotFoundException();
        }
        $this->data = $res->fetchOneAssoc();
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
        return $this->first_name;
    }

    public function lastName()
    {
        return $this->last_name;
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

    /** Return the profile associated with the given login.
     */
    public static function get($login)
    {
        try {
            return new Profile($login);
        } catch (UserNotFoundException $e) {
            /* Let say we can identify a profile using the identifiers of its owner.
             */
            $user = User::getSilent($login);
            if ($user && $user->hasProfile()) {
                return $user->profile();
            }
            return null;
        }
    }

    public static function getNameTypeId($type, $for_sql = false)
    {
        if (!S::has('name_types')) {
            $table = XDB::fetchAllAssoc('name', 'SELECT  id, name
                                                   FROM  profile_name_search_enum');
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
