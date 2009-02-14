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

class EventFeed extends PlFeed
{
    public function __construct()
    {
        global $globals;
        parent::__construct($globals->core->sitename . ' :: News',
                            $globals->baseurl,
                            'Informations et Activités polytechniciennes',
                            $globals->baseurl . '/images/logo.png',
                            'events/rss.tpl');
    }

    public static function nextEvent(PlIterator &$it, PlUser &$user)
    {
        while ($body = $it->next()) {
            $uf = UserFilter::getLegacy($body['promo_min'], $body['promo_max']);
            if ($uf->checkUser($user)) {
                return $body;
            }
        }
        return null;
    }

    protected function fetch(PlUser &$user)
    {
        global $globals;
        $events = XDB::iterator('SELECT  e.id, e.titre AS title, e.texte, e.creation_date AS publication, e.post_id,
                                         p.attachmime IS NOT NULL AS photo, FIND_IN_SET(\'wiki\', e.flags) AS wiki,
                                         e.user_id, e.promo_min, e.promo_max
                                   FROM  evenements       AS e
                              LEFT JOIN  evenements_photo AS p ON (p.eid = e.id)
                                  WHERE  FIND_IN_SET("valide", e.flags) AND peremption >= NOW()');
        $data = array();
        while ($e = self::nextEvent($events, $user)) {
            $author = User::getWithUID($e['user_id']);
            $promo  = $author->promo();
            $e['author'] = $author->fullName() . ($promo ? ' (' . $promo . ')' : '');
            $e['link'] = $globals->baseurl . '/events#newsid' . $e['id'];
            $data[] = $e;
        }
        return PlIteratorUtils::fromArray($data, 1, true);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
