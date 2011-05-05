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

Platal::load('newsletter');

class XnetNlModule extends NewsletterModule
{
    function handlers()
    {
        return array(
            '%grp/nl'                   => $this->make_hook('nl',              AUTH_MDP),
            '%grp/nl/show'              => $this->make_hook('nl_show',         AUTH_MDP),
            '%grp/nl/search'            => $this->make_hook('nl_search',       AUTH_MDP),
            '%grp/admin/nl'             => $this->make_hook('admin_nl',        AUTH_MDP, 'groupadmin'),
            '%grp/admin/nl/enable'      => $this->make_hook('admin_nl_enable', AUTH_MDP, 'groupadmin'),
            '%grp/admin/nl/edit'        => $this->make_hook('admin_nl_edit',   AUTH_MDP, 'groupadmin'),
            '%grp/admin/nl/edit/cancel' => $this->make_hook('admin_nl_cancel', AUTH_MDP, 'groupadmin'),
            '%grp/admin/nl/edit/valid'  => $this->make_hook('admin_nl_valid',  AUTH_MDP, 'groupadmin'),
            '%grp/admin/nl/categories'  => $this->make_hook('admin_nl_cat',    AUTH_MDP, 'groupadmin'),
        );
    }

    protected function getNl()
    {
       global $globals;
       $group = $globals->asso('shortname');
       return NewsLetter::forGroup($group);
    }

    public function handler_admin_nl_enable($page)
    {
        global $globals;
        $nl = $this->getNl();
        if ($nl) {
            return PL_FORBIDDEN;
        }

        if (Post::has('title')) {
            if (!S::has_xsrf_token()) {
                return PL_FORBIDDEN;
            }

            XDB::execute('INSERT INTO  newsletters
                                  SET  group_id = {?}, name = {?}',
                                  $globals->asso('id'), Post::s('title'));

            $mailer = new PlMailer();
            $mailer->assign('group', $globals->asso('nom'));
            $mailer->assign('user', S::user());
            $mailer->send();

            $page->trigSuccessRedirect("La lettre d'informations du groupe " .
                                       $globals->asso('nom') . " a bien été créée",
                                       $globals->asso('shortname') . '/admin/nl');
        }
        $page->setTitle('Activation de la newsletter');
        $page->changeTpl('newsletter/enable.tpl');
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
