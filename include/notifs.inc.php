<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

abstract class WatchOperation
{
    protected $date;

    public function getTitle($count = 0)
    {
        if ($count == 1) {
            return str_replace(array('$x', '$s'), '', $this->title);
        } else {
            return str_replace(array('$x', '$s'), array('x', 's'), $this->title);
        }
    }

    public function getCondition(PlUser &$user, $date)
    {
        $this->date = $date;
        if (!$user->watch($this->flag)) {
            return new PFC_False();
        } else {
            return $this->buildCondition($user);
        }
    }

    abstract protected function buildCondition(PlUser &$user);
    abstract public function getOrder();
    abstract public function getDate(PlUser &$user);

    public function publicationDate(PlUser &$user)
    {
        return $this->getDate($user);
    }

    public function seen(PlUser &$user, $last)
    {
        return strtotime($this->getDate($user)) > $last;
    }

    public function getData(PlUser &$user)
    {
        return null;
    }
}

class WatchProfileUpdate extends WatchOperation
{
    public $flag  = 'profile';
    public $title = 'Mise$s à jour de fiche';

    public static function register(Profile &$profile, $field)
    {
        XDB::execute('REPLACE INTO  watch_profile (uid, ts, field)
                            VALUES  ({?}, NOW(), {?})',
                     $profile->id(), $field);
    }

    protected function buildCondition(PlUser &$user)
    {
        return new PFC_And(new UFC_ProfileUpdated('>', $this->date),
                           new UFC_WatchContact($user));
    }

    public function getOrder()
    {
        return new UFO_ProfileUpdate();
    }

    public function getDate(PlUser &$user)
    {
        return $user->profile()->last_change;
    }

    static private $descriptions = array('search_names' => 'L\'un de ses noms',
                                         'freetext'     => 'Le texte libre',
                                         'mobile'       => 'Son numéro de téléphone portable',
                                         'nationalite'  => 'Sa nationalité',
                                         'nationalite2' => 'Sa seconde nationalité',
                                         'nationalite3' => 'Sa troisième nationalité',
                                         'nick'         => 'Son surnom',
                                         'networking'   => 'La liste de ses adresses de networking',
                                         'edus'         => 'Ses formations',
                                         'addresses'    => 'Ses adresses',
                                         'section'      => 'Sa section sportive',
                                         'binets'       => 'La liste de ses binets',
                                         'medals'       => 'Ses décorations',
                                         'cv'           => 'Son Curriculum Vitae',
                                         'corps'        => 'Son Corps d\'État',
                                         'jobs'         => 'Ses informations professionnelles',
                                         'photo'        => 'Sa photographie');
    public function getData(PlUser &$user)
    {
        $data = XDB::fetchColumn('SELECT  field
                                    FROM  watch_profile
                                   WHERE  uid = {?} AND ts > FROM_UNIXTIME({?}) AND field != \'\'
                                ORDER BY  ts',
                                 $user->id(), $this->date);
        if (count($data) == 0) {
            return null;
        } else {
            $text = array();
            foreach ($data as $f) {
                $text[] = self::$descriptions[$f];
            }
            return $text;
        }
    }
}

class WatchRegistration extends WatchOperation
{
    public $flag  = 'registration';
    public $title = 'Inscription$s';

    protected function buildCondition(PlUser &$user)
    {
        return new PFC_And(new UFC_Registered(false, '>', $this->date),
                           new PFC_Or(new UFC_WatchContact($user),
                                      new UFC_WatchPromo($user)));
    }

    public function getOrder()
    {
        return new UFO_Registration();
    }

    public function getDate(PlUser &$user)
    {
        return $user->registration_date;
    }
}

class WatchDeath extends WatchOperation
{
    public $flag  = 'death';
    public $title = 'Décès';

    protected function buildCondition(PlUser &$user)
    {
        return new PFC_And(new UFC_Dead('>', $this->date, true),
                           new PFC_Or(new UFC_WatchPromo($user),
                                      new UFC_WatchContact($user)));
    }

    public function getOrder()
    {
        return new UFO_Death();
    }

    public function getDate(PlUser &$user)
    {
        return $user->profile()->deathdate;
    }

    public function publicationDate(PlUser &$user)
    {
        return $user->profile()->deathdate_rec;
    }

    public function seen(PlUser &$user, $last)
    {
        return strtotime($user->profile()->deathdate_rec) > $last;
    }
}

class WatchBirthday extends WatchOperation
{
    const WATCH_LIMIT = 604800; // 1 week

    public $flag  = 'birthday';
    public $title = 'Anniversaire$s';

    protected function buildCondition(PlUser &$user)
    {
        return new PFC_And(new PFC_OR(new UFC_Birthday('=', time()),
                                      new PFC_And(new UFC_Birthday('<=', time() + self::WATCH_LIMIT),
                                                  new UFC_Birthday('>', $this->date + self::WATCH_LIMIT))),
                           new PFC_Or(new UFC_WatchPromo($user),
                                      new UFC_WatchContact($user)));
    }

    public function getOrder()
    {
        return new UFO_Birthday();
    }

    public function getDate(PlUser &$user)
    {
        return $user->profile()->next_birthday;
    }

    public function publicationDate(PlUser &$user)
    {
        return date('Y-m-d', strtotime($user->profile()->next_birthday) - self::WATCH_LIMIT);
    }

    public function seen(PlUser &$user, $last)
    {
        $birthday = strtotime($user->profile()->next_birthday);
        return $birthday >  $last + self::WATCH_LIMIT
            || date('Ymd', $birthday) == date('Ymd');
    }
}

class Watch
{
    private static  $classes = array('WatchRegistration',
                                     'WatchProfileUpdate',
                                     'WatchDeath',
                                     'WatchBirthday');

    private static function fetchCount(PlUser &$user, $date, $class)
    {
        $obj = new $class();
        $uf = new UserFilter($obj->getCondition($user, $date));
        return $uf->getTotalCount();
    }

    public static function getCount(PlUser &$user, $date = null)
    {
        $count = 0;
        if (is_null($date)) {
            $date = $user->watchLast();
        }
        foreach (self::$classes as $class) {
            $count += self::fetchCount($user, $date, $class);
        }
        return $count;
    }


    private static function fetchEvents(PlUser &$user, $date, $class)
    {
        $obj = new $class();
        $uf = new UserFilter($obj->getCondition($user, $date),
                             array($obj->getOrder(), new UFO_Name(UserFilter::DN_SORT)));
        $users = $uf->getUsers();
        if (count($users) == 0) {
            return null;
        } else {
            return array('operation' => $obj,
                         'title'     => $obj->getTitle(count($users)),
                         'users'     => $users);
        }
    }

    public static function getEvents(PlUser &$user, $date = null)
    {
        if (is_null($date)) {
            $date = $user->watchLast();
        }
        $events = array();
        foreach (self::$classes as $class) {
            $e = self::fetchEvents($user, $date, $class);
            if (!is_null($e)) {
                $events[] = $e;
            }
        }
        return $events;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
