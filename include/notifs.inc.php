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
    private static $false = null;

    public function getTitle($count = 0)
    {
        if ($count == 1) {
            return str_replace(array('$x', '$s'), '', $this->title);
        } else {
            return str_replace(array('$x', '$s'), array('x', 's'), $this->title);
        }
    }

    public function getCondition(Watch $watch)
    {
        if (!$watch->user()->watchType($this->flag)) {
            if (!self::$false) {
                self::$false = new PFC_False();
            }
            return self::$false;
        } else {
            return $this->buildCondition($watch);
        }
    }

    abstract protected function buildCondition(Watch $watch);
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
    private static $order = null;

    public $flag  = 'profile';
    public $title = 'Mise$s à jour de fiche';

    public static function register(Profile &$profile, $field)
    {
        XDB::execute('REPLACE INTO  watch_profile (pid, ts, field)
                            VALUES  ({?}, NOW(), {?})',
                     $profile->id(), $field);
    }

    protected function buildCondition(Watch $watch)
    {
        return new PFC_And(new UFC_ProfileUpdated('>', $watch->date()),
                           $watch->contactCondition());
    }

    public function getOrder()
    {
        if (!self::$order) {
            self::$order = new UFO_ProfileUpdate();
        }
        return self::$order;
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
        $data = XDB::fetchColumn("SELECT  field
                                    FROM  watch_profile
                                   WHERE  pid = {?} AND ts > FROM_UNIXTIME({?}) AND field != ''
                                ORDER BY  ts",
                                 $user->profile()->id(), $this->date);
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
    private static $order = null;

    public $flag  = 'registration';
    public $title = 'Inscription$s';

    protected function buildCondition(Watch $watch)
    {
        return new PFC_And(new UFC_Registered(false, '>', $watch->date()),
                           new PFC_Or($watch->contactCondition(),
                                      $watch->promoCondition()));
    }

    public function getOrder()
    {
        if (!self::$order) {
            self::$order = new UFO_Registration();
        }
        return self::$order;
    }

    public function getDate(PlUser &$user)
    {
        return $user->registration_date;
    }
}

class WatchDeath extends WatchOperation
{
    private static $order = null;

    public $flag  = 'death';
    public $title = 'Décès';

    protected function buildCondition(Watch $watch)
    {
        return new PFC_And(new UFC_Dead('>', $watch->date(), true),
                           new PFC_Or($watch->contactCondition(),
                                      $watch->promoCondition()));
    }

    public function getOrder()
    {
        if (!self::$order) {
            self::$order = new UFO_Death();
        }
        return self::$order;
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

    private static $order = null;

    public $flag  = 'birthday';
    public $title = 'Anniversaire$s';

    protected function buildCondition(Watch $watch)
    {
        $select_date = new PFC_OR(new UFC_Birthday('=', time()),
                                  new PFC_And(new UFC_Birthday('<=', time() + self::WATCH_LIMIT),
                                              new UFC_Birthday('>', $watch->date() + self::WATCH_LIMIT)));
        $profile = $watch->profile();
        $cond = $watch->contactCondition();
        if ($profile) {
            $cond = new PFC_Or($cond,
                               new PFC_And($watch->promoCondition(),
                                           new UFC_Promo('>=', $profile->mainGrade(), $profile->yearpromo() - 1),
                                           new UFC_Promo('<=', $profile->mainGrade(), $profile->yearpromo() + 1)));
        }
        return new PFC_And($select_date, $cond);
    }

    public function getOrder()
    {
        if (!self::$order) {
            self::$order = new UFO_Birthday();
        }
        return self::$order;
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
    private static $events = array();

    private $user = null;
    private $date = null;
    private $contactCond = null;
    private $promoCond = null;

    private $filters = array();

    public function __construct(PlUser $user, $date = null)
    {
        $this->user = $user;
        $this->date = self::getDate($user, $date);
    }

    public function user()
    {
        return $this->user;
    }

    public function profile()
    {
        return $this->user->profile();
    }

    public function date()
    {
        return $this->date;
    }

    public function contactCondition()
    {
        if (!$this->contactCond) {
            $this->contactCond = new UFC_WatchContact($this->user);
        }
        return $this->contactCond;
    }

    public function promoCondition()
    {
        if (!$this->promoCond) {
            $this->promoCond = new UFC_WatchPromo($this->user);
        }
        return $this->promoCond;
    }

    private function fetchEventWatch($class)
    {
        if (!isset(self::$events[$class])) {
            self::$events[$class] = new $class();
        }
        return self::$events[$class];
    }

    private function fetchFilter($class)
    {

        if (!isset($this->filters[$class])) {
            $event = $this->fetchEventWatch($class);
            $this->filters[$class] = new UserFilter($event->getCondition($this),
                                                    array($event->getOrder(), new UFO_Name(Profile::DN_SORT)));
        }
        return $this->filters[$class];
    }

    public function count()
    {
        $count = 0;
        foreach (self::$classes as $class) {
            $uf = $this->fetchFilter($class);
            $count += $uf->getTotalCount();
        }
        return $count;
    }


    private function fetchEvents($class)
    {
        $obj = $this->fetchEventWatch($class);
        $uf = $this->fetchFilter($class);
        $users = $uf->getUsers();
        if (count($users) == 0) {
            return null;
        } else {
            return array('type'      => $obj->flag,
                         'operation' => $obj,
                         'title'     => $obj->getTitle(count($users)),
                         'users'     => $users);
        }
    }

    public function events()
    {
        $events = array();
        foreach (self::$classes as $class) {
            $e = $this->fetchEvents($class);
            if (!is_null($e)) {
                $events[] = $e;
            }
        }
        return $events;
    }


    private static function getDate(PlUser &$user, $date)
    {
        if (is_null($date)) {
            $date = $user->watchLast();
            $limit = time() - (7 * 86400);
            if ($date < $limit) {
                $date = $limit;
            }
        }
        return $date;
    }

    public static function getCount(PlUser &$user, $date = null)
    {
        $watch = new Watch($user, $date);
        return $watch->count();
    }

    public static function getEvents(PlUser &$user, $date = null)
    {
        $watch = new Watch($user, $date);
        return $watch->events();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
