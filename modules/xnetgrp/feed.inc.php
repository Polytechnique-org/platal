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

    protected function fetch($user)
    {
        global $globals;
        if (!is_null($user)) {
            return XDB::iterator("SELECT a.id, a.titre AS title, a.texte, a.contacts,
                                         a.create_date AS publication,
                                         CONCAT(u2.prenom, ' ', IF(u2.nom_usage != '', u2.nom_usage, u2.nom), ' (X',  u2.promo, ')') AS author,
                                         FIND_IN_SET('photo', a.flags) AS photo,
                                         CONCAT({?}, '/#art', a.id) AS link
                                   FROM auth_user_md5 AS u
                             INNER JOIN groupex.announces AS a ON ( (a.promo_min = 0 OR a.promo_min <= u.promo)
                                                                  AND (a.promo_max = 0 OR a.promo_max <= u.promo))
                             INNER JOIN auth_user_md5 AS u2 ON (u2.user_id = a.user_id)
                             WHERE u.user_id = {?} AND peremption >= NOW() AND a.asso_id = {?}",
                                   $this->link, $user, $globals->asso('id'));
        } else {
            return  XDB::iterator("SELECT a.id, a.titre AS title, a.texte, a.create_date AS publication,
                                         CONCAT(u.prenom, ' ', IF(u.nom_usage != '', u.nom_usage, u.nom), ' (X',  u.promo, ')') AS author,
                                         CONCAT({?}, '/#art', a.id) AS link,
                                         NULL AS photo, NULL AS contacts
                                    FROM groupex.announces AS a
                              INNER JOIN auth_user_md5 AS u USING(user_id)
                                   WHERE FIND_IN_SET('public', a.flags) AND peremption >= NOW() AND a.asso_id = {?}",
                                  $this->link, $globals->asso('id'));
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:

?>
