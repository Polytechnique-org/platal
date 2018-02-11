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

Platal::load('newsletter');

class AXLetterModule extends NewsletterModule
{
    function handlers()
    {
        return array(
            'ax'                   => $this->make_hook('nl',              AUTH_COOKIE, 'user'),
            'ax/out'               => $this->make_hook('out',             AUTH_COOKIE, 'user'),
            'ax/show'              => $this->make_hook('nl_show',         AUTH_COOKIE, 'user'),
            'ax/search'            => $this->make_hook('nl_search',       AUTH_COOKIE, 'user'),
            'ax/admin'             => $this->make_hook('admin_nl',        AUTH_PASSWD, 'user'),
            'ax/admin/edit'        => $this->make_hook('admin_nl_edit',   AUTH_PASSWD, 'user'),
            'ax/admin/edit/valid'  => $this->make_hook('admin_nl_valid',  AUTH_PASSWD, 'user'),
            'ax/admin/edit/cancel' => $this->make_hook('admin_nl_cancel', AUTH_PASSWD, 'user'),
            'ax/admin/edit/delete' => $this->make_hook('admin_nl_delete', AUTH_PASSWD, 'user'),
            'ax/admin/categories'  => $this->make_hook('admin_nl_cat',    AUTH_PASSWD, 'user'),
            'ax/stat'              => $this->make_hook('stat_nl',         AUTH_PASSWD, 'user')
        );
    }

    protected function getNl()
    {
        require_once 'newsletter.inc.php';
        return NewsLetter::forGroup(NewsLetter::GROUP_AX);
    }

    function handler_out($page, $hash = null, $issue_id = null)
    {
        $hash = ($hash == 'nohash') ? null : $hash;
        if (!$hash) {
            if (!S::logged()) {
                return PL_DO_AUTH;
            }
        }
        return $this->handler_nl($page, 'out', $hash, $issue_id);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
