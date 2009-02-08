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

class WatchProfileUpdate
{
    const ID = 1;

    public static function register(Profile &$profile, $field)
    {
        XDB::execute('REPLACE INTO  watch_profile (uid, ts, field)
                            VALUES  ({?}, NOW(), {?})',
                     $profile->id(), $field);
    }

    public static function getCondition(PlUser &$user)
    {
        return new UFC_And(new UFC_ProfileUpdated('>=', $user->watch_last),
                           new UFC_WatchContacts($user->id()));
    }
}

class WatchRegistration
{
    const ID = 2;

    public static function getCondition(PlUser &$user)
    {
        return new UFC_And(new UFC_Registered(false, '>=', $user->watch_last),
                           new UFC_WatchRegistration($user->id()));
    }
}

class WatchDeath
{
    const ID = 3;

    public static function getCondition(PlUser &$user)
    {
        return new UFC_And(new UFC_Dead('>=', $user->watch_last, true),
                           new UFC_Or(new UFC_WatchPromo($user->id()),
                                      new UFC_WatchContacts($user->id())));
    }
}

class WatchBirthday
{
    const ID = 4;

    public static function getCondition(PlUser &$user)
    {
        return new UFC_And(new UFC_Birthday(),
                           new UFC_Or(new UFC_WatchPromo($user->id()),
                                      new UFC_WatchContacts($user->id())));
    }
}

?>
