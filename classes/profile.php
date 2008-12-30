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
            $res = XDB::query('SELECT  p.pid, p.hrpid
                                 FROM  account_profiles AS ap
                           INNER JOIN  profiles AS p ON (p.pid = ap.pid)
                                WHERE  ap.uid = {?} AND FIND_IN_SET(\'owner\', ap.perms)',
                             $login->id());
        } else if (is_numeric($login)) {
            $res = XDB::query('SELECT  p.pid, p.hrpid
                                 FROM  profiles AS p
                                WHERE  p.pid = {?}',
                              $login);
        } else {
            $res = XDB::query('SELECT  p.pid, p.hrpid
                                 FROM  profiles AS p
                                WHERE  p.hrpid = {?}',
                              $login);
        }
        if ($res->numRows() != 1) {
            throw new UserNotFoundException();
        }
        list($this->pid, $this->hrpid) = $res->fetchOneRow();
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

        if (empty($this->data)) {
            // XXX: Temporary, use data from auth_user_md5 (waiting for data from newdirectory
            $this->data = XDB::fetchOneAssoc('SELECT  p.*, u.prenom AS first_name,
                                                      IF(u.nom_usage != "", u.nom_usage, u.nom) AS last_name,
                                                      u.promo AS promo,
                                                      CONCAT(u.prenom, " ", u.nom) AS short_name,
                                                      IF(u.nom_usage != "",
                                                         CONCAT(u.nom_usage, " (", u.nom, "),", u.prenom),
                                                         CONCAT(u.nom, ", ", u.prenom)) AS full_name
                                                FROM  profiles AS p
                                          INNER JOIN  auth_user_md5 AS u ON (u.user_id = p.pid)
                                               WHERE  p.pid = {?}',
                                             $this->id());
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
    public static function get($login) {
        try {
            return new Profile($login);
        } catch (UserNotFoundException $e) {
            return null;
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
