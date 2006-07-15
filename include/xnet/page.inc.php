<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

require_once('platal/page.inc.php');
require_once('xnet/smarty.plugins.inc.php');

// {{{ class XnetPage

class XnetPage extends PlatalPage
{
    // {{{ function XnetPage()

    function XnetPage($tpl, $type=SKINNED)
    {
        $this->PlatalPage($tpl, $type);
    }

    // }}}
    // {{{ function run()

    function run()
    {
        $this->_run('xnet/skin.tpl');
    }

    // }}}
    // {{{ function setType

    function setType($type)
    {
        $this->assign('xnet_type', strtolower($type));
    }

    // }}}
    // {{{ function useMenu

    function useMenu()
    {
        global $globals;

        $menu = array();

        $sub = array();
        $sub['accueil']           = '';
        $sub['liste des groupes'] = 'plan';
        if (logged()) {
            if (has_perms()) {
                $sub['admin X.net'] = 'admin';
            }
            $sub['déconnexion']   = 'exit';
        }
        $menu["Menu Principal"]   = $sub;

        if (logged() && (is_member() || may_update())) {
            $sub = array();
            $dim = $globals->asso('diminutif');
            $sub['présentation'] = "$dim/";
            if (may_update() || $globals->asso('pub') == 'public') {
                $sub['annuaire du groupe'] = "$dim/annuaire";
                if ($globals->xnet->geoloc)
                    $sub['carte'] = "$dim/geoloc.php";
            }
            if ($globals->asso('mail_domain')) {
                $sub['listes de diffusion'] = "$dim/lists";
            }
            $sub['événement'] = "$dim/events";
            if (false) {
                $sub['carnet'] = "$dim/carnet.php";
            }
            $sub['télépaiement'] = "$dim/paiement";

            $menu[$globals->asso('nom')] = $sub;
        }

        if (logged() && may_update()) {
            $sub = array();
            $sub['modifier l\'accueil'] = "$dim/edit";
            if ($globals->asso('mail_domain')) {
                $sub['envoyer un mail']     = "$dim/mail";
                $sub['créer une liste']     = "$dim/lists/create";
                $sub['créer un alias']      = "$dim/alias/create";
            }
            $menu['Administrer Groupe'] = $sub;
        }

        $this->assign('menu', $menu);
    }

    // }}}
    // {{{ function doAuth()

    function doAuth($force = false)
    {
        $this->register_function('list_all_my_groups', 'list_all_my_groups');
        $this->register_modifier('cat_pp', 'cat_pp');
        $this->assign('it_is_xnet', true);
        if (!logged() && $force) {
            $_SESSION['session']->doLogin($this);
        }
        if (!logged() && Get::has('auth')) {
            $_SESSION['session']->doAuthX($this);
        }
    }

    // }}}
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
