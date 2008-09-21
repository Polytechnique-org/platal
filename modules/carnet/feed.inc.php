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
    private $it;

    public function __construct(Notifs& $notifs)
    {
        $this->notifs =& $notifs;
        $this->it = PlIteratorUtils::fromArray($notifs->_data, 3);
    }

    public function next()
    {
        $data = $this->it->next();
        if (is_null($data)) {
            return null;
        }
        $cid  = $data['keys'][0];
        $x    = $data['value'];

        global $globals;
        @require_once 'Date.php';
        @$date = new Date($x['date']);
        @$date = $date->format('%e %B %Y');
        $author = $x['prenom'] . ' ' . $x['nom'] . ' (X' . $x['promo'] . ')';
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

    protected function fetch($user)
    {
        return new CarnetFeedIterator(new Notifs($user, false));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
