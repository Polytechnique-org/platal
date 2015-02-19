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

require_once 'notifs.inc.php';
@require_once 'Date.php';

class CarnetFeedIterator implements PlIterator
{
    private $notifs;
    private $it;

    public function __construct(PlUser $owner)
    {
        $notifs = Watch::getEvents($owner);
        $infos  = array();
        foreach ($notifs as $n) {
            foreach ($n['users'] as $user) {
                $op   = $n['operation'];
                $date = $op->getDate($user);
                @$datetext = new Date($date);
                @$datetext = $datetext->format('%e %B %Y');
                $profile = $user->profile();
                $infos[] = array('operation'   => $op,
                                 'title'       => '[' . $op->getTitle(1) . ']  - ' . $user->fullName() . ' le ' . $datetext,
                                 'author'      => $user->fullName(),
                                 'publication' => $op->publicationDate($user),
                                 'date'        => strtotime($date),
                                 'id'          => $op->flag . '-' . $user->id() . '-' . strtotime($date),
                                 'data'        => $op->getData($user),
                                 'hruid'       => $user->login(),
                                 'dead'        => $user->deathdate,
                                 'profile'     => $user->profile()->hrid(),
                                 'link'        => Platal::globals()->baseurl . '/profile/' . $profile->hrid(),
                                 'user'        => $user,
                                 'contact'     => $owner->isContact($profile));
            }
        }
        $this->it = PlIteratorUtils::fromArray($infos);
    }

    public function next()
    {
        $data = $this->it->next();
        return $data['value'];
    }

    public function total()
    {
        return $this->it->total();
    }

    public function first()
    {
        return $this->it->first();
    }

    public function last()
    {
        return $this->it->last();
    }
}

class CarnetFeed extends PlFeed
{
    public function __construct()
    {
        global $globals;
        parent::__construct($globals->core->sitename . ' :: Carnet',
                            $globals->baseurl . '/carnet/panel',
                            'Ton carnet polytechnicien',
                            $globals->baseurl . '/images/logo.png',
                            'carnet/rss.tpl');
    }

    protected function fetch(PlUser $user)
    {
        return new CarnetFeedIterator($user);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
