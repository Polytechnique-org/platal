<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

class Xorg extends Platal
{
    public function __construct()
    {
        parent::__construct('auth', 'carnet', 'email', 'events', 'forums',
                            'lists', 'marketing', 'payment', 'platal',
                            'profile', 'register', 'search', 'stats', 'admin',
                            'newsletter', 'axletter', 'epletter', 'bandeau', 'survey',
                            'fusionax', 'gadgets', 'googleapps', 'poison',
                            'openid', 'reminder', 'api', 'urlshortener', 'deltaten', 'geoloc');
    }

    public function find_hook()
    {
        if ($this->path{0} >= 'A' && $this->path{0} <= 'Z') {
            return self::wiki_hook();
        }
        return parent::find_hook();
    }

    public function force_login(PlPage $page)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
        if (S::logged()) {
            $page->changeTpl('core/password_prompt_logged.tpl');
        } else {
            $page->changeTpl('core/password_prompt.tpl');
        }
        $page->assign_by_ref('platal', $this);
        $page->run();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
