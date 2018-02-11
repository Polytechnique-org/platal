<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

class UserFilterIterator implements PlIterator
{
    private $it;
    private $user;
    public function __construct(PlIterator $it, PlUser $user)
    {
        $this->it =& $it;
        $this->user =& $user;
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

    public function next()
    {
        while ($n = $this->it->next()) {
            $uf = UserFilter::getLegacy($n['promo_min'], $n['promo_max']);
            if ($uf->checkUser($this->user)) {
                return $n;
            }
        }
        return null;
    }
}

class XnetGrpEventFeed extends PlFeed
{
    public function __construct()
    {
        global $globals;
        $name = $globals->asso('nom');
        $url = $globals->baseurl . '/' . $globals->asso('diminutif');
        parent::__construct('Polytechnique.net :: ' . $name . ' :: News',
                            $url,
                            'L\'actualité du groupe ' . $name,
                            $url . '/logo',
                            'xnetgrp/announce-rss.tpl');
    }

    protected function fetch(PlUser $user)
    {
        global $globals;
        if (!is_null($user)) {
            return new UserFilterIterator(
                   XDB::iterator("SELECT  id, titre AS title, texte, contacts,
                                          create_date AS publication,
                                          FIND_IN_SET('photo', flags) AS photo,
                                          CONCAT({?}, '/#art', id) AS link
                                    FROM  group_announces
                                   WHERE  expiration >= NOW() AND asso_id = {?}",
                                   $this->link, $globals->asso('id'), $user));
        } else {
            return  XDB::iterator("SELECT  id, titre AS title, texte, create_date AS publication,
                                           CONCAT({?}, '/#art', id) AS link,
                                           NULL AS photo, NULL AS contacts
                                     FROM  group_announces
                                    WHERE  FIND_IN_SET('public', flags) AND expiration >= NOW() AND asso_id = {?}",
                                  $this->link, $globals->asso('id'));
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:

?>
