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

    protected function fetch($user)
    {
        global $globals;
        return XDB::iterator(
                'SELECT  e.id, e.titre AS title, e.texte, e.creation_date AS publication, e.post_id, p.attachmime IS NOT NULL AS photo,
                         CONCAT(u2.prenom, " ", IF(u2.nom_usage = "", u2.nom, u2.nom_usage), " (X", u2.promo, ")") AS author,
                         FIND_IN_SET(\'wiki\', e.flags) AS wiki,
                         CONCAT({?}, "/events#newsid", e.id) AS link
                   FROM  auth_user_md5   AS u
             INNER JOIN  evenements      AS e ON ( (e.promo_min = 0 || e.promo_min <= u.promo)
                                                 AND (e.promo_max = 0 || e.promo_max >= u.promo) )
              LEFT JOIN  evenements_photo AS p ON (p.eid = e.id)
             INNER JOIN  auth_user_md5   AS u2 ON (u2.user_id = e.user_id)
                  WHERE  u.user_id = {?} AND FIND_IN_SET("valide", e.flags)
                                         AND peremption >= NOW()', $globals->baseurl, $user);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
