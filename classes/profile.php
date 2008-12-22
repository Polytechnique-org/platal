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
            return false;
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
