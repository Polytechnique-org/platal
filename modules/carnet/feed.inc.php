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

require_once 'notifs.inc.php';

class CarnetFeedIterator implements PlIterator
{
    private $notifs;

    private $start;
    private $stop;
    private $pos;
    private $count;

    private $p1;
    private $p2;

    public function __construct(Notifs& $notifs)
    {
        $this->notifs =& $notifs;
        foreach ($notifs->_data as $c) {
            foreach ($c as $promo) {
                $this->count += count($promo);
            }
        }
        $this->pos = 0;
        reset($notifs->_data);
        if ($this->count > 0) {
            $this->p1 = current($notifs->_data);
            reset($this->p1);
            $this->p2 = current($this->p1);
            reset($this->p2);
        }
    }

    public function next()
    {
        $this->pos++;
        $this->first = ($this->count > 0 && $this->pos == 1);
        $this->last  = ($this->count > 0 && $this->pos == $this->count);
        if ($this->count == 0) {
            return null;
        }

        $x = current($this->p2);
        if ($x === false) {
            $this->p2 = next($this->p1);
            if ($this->p2 === false) {
                $this->p1 = next($this->notifs->_data);
                if ($this->p1 === false) {
                    return null;
                }
                reset($this->p1);
                $this->p2 = current($this->p1);
            }
            reset($this->p2);
            $x = current($this->p2);
        }
        $cid = key($this->notifs->_data);
        next($this->p2);

        global $globals;
        $author = $x['prenom'] . ' ' . $x['nom'] . ' (X' . $x['promo'] . ')';

        @require_once 'Date.php';
        @$date = new Date($x['date']);
        @$date = $date->format('%e %B %Y');
        return array_merge($x, 
                    array('author' => $author,
                          'publication' => $x['known'],
                          'id' => 'carnet' . $x['known'] . $cid . $x['bestalias'],
                          'link' => $globals->baseurl . '/profile/private/'
                                    . $x['bestalias'],
                          'title' => '[' . $this->notifs->_cats[$cid]['short'] . '] '
                                     . $author . ' - le ' . $date));
    }

    public function total()
    {
        return $this->count;
    }

    public function first()
    {
        return $this->start;
    }

    public function last()
    {
        return $this->stop;
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

    protected function fetch($user)
    {
        return new CarnetFeedIterator(new Notifs($user, false));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
