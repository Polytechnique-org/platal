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

    public function getCondition(PlUser &$user)
    {
        return new UFC_And(new UFC_ProfileUpdated('>', $user->watch_last),
                           new UFC_WatchContacts($user->id()));
    }
}

class WatchRegistration
{
    const ID = 2;

    public function getCondition(PlUser &$user)
    {
        return new UFC_And(new UFC_Registered(false, '>', $user->watch_last),
                           new UFC_Or(new UFC_WatchContacts($user->id()),
                                      new UFC_WatchPromo($user->id())),
                           new UFC_WatchRegistration($user->id()));
    }
}

class WatchDeath
{
    const ID = 3;

    public function getCondition(PlUser &$user)
    {
        return new UFC_And(new UFC_Dead('>', $user->watch_last, true),
                           new UFC_Or(new UFC_WatchPromo($user->id()),
                                      new UFC_WatchContacts($user->id())));
    }
}

class WatchBirthday
{
    const ID = 4;

    public function getCondition(PlUser &$user)
    {
        return new UFC_And(new UFC_OR(new UFC_Birthday('=', time()),
                                      new UFC_And(new UFC_Birthday('<=', time() + 864000),
                                                  new UFC_Birthday('>', $user->watch_last + 864000))),
                           new UFC_Or(new UFC_WatchPromo($user->id()),
                                      new UFC_WatchContacts($user->id())));
    }
}

class Watch
{
    private static  $classes = array('WatchRegistration',
                                     'WatchProfileUpdate',
                                     'WatchDeath',
                                     'WatchBirthday');

    private static function fetchCount(PlUser &$user, $class)
    {
        $obj = new $class();
        $uf = new UserFilter($obj->getCondition($user));
        return $uf->getTotalCount();
    }

    public static function getCount(PlUser &$user)
    {
        $count = 0;
        foreach (self::$classes as $class) {
            $count += self::fetchCount($user, $class);
        }
        return $count;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
