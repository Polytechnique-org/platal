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

class Xnet extends Platal
{
    public function __construct()
    {
        parent::__construct(
            'xnet',

            'bandeau',
            'geoloc',
            'payment',
            'xnetevents',
            'xnetgrp',
            'xnetlists',
            'xnetnl'
        );
    }

    public function hook_map($name)
    {
        if ($name == 'grp') {
            global $globals;
            if ($globals->asso()) {
                return $globals->asso('shortname');
            }
        }
        return null;
    }

    protected function find_hook()
    {
        $ans = parent::find_hook();
        $this->https = false;
        return $ans;
    }

    public function force_login(PlPage $page)
    {
        $redirect = S::v('loginX');
        if (!$redirect) {
            $page->trigError('Impossible de s\'authentifier. Problème de configuration de plat/al.');
            return;
        }
        http_redirect($redirect);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
