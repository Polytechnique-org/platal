<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
        $sub['accueil']           = 'index.php';
        $sub['liste des groupes'] = 'plan.php';
        if (logged()) {
            if (has_perms()) {
                $sub['admin X.net'] = 'admin.php';
            }
            $sub['déconnexion']   = 'deconnexion.php';
        }
        $menu["Menu Principal"]   = $sub;

        if (logged() && (is_member() || may_update())) {
            $sub = array();
            $dim = $globals->asso('diminutif');
            $sub['présentation'] = "$dim/asso.php";
            $sub['annuaire du groupe'] = "$dim/annuaire.php";
            if ($globals->asso('mail_domain')) {
                $sub['listes de diffusion'] = "$dim/listes.php";
            }
            if (false) {
                $sub['evenement'] = "$dim/evenement.php";
                $sub['carnet'] = "$dim/carnet.php";
            }
            $sub['telepaiement'] = "$dim/telepaiement.php";

            $menu[$globals->asso('nom')] = $sub;
        }

        if (logged() && may_update()) {
            $sub = array();
            $sub['modifier l\'acceuil'] = "$dim/edit.php";
            if ($globals->asso('mail_domain')) {
                $sub['envoyer un mail']     = "$dim/mail.php";
                $sub['créer une liste']     = "$dim/listes-create.php";
                $sub['créer un alias']      = "$dim/alias-create.php";
            }
            $menu['Administrer Groupe'] = $sub;
        }

        $this->assign('menu', $menu);
    }

    // }}}
    // {{{ function doAuth()

    function doAuth()
    {
        $this->register_function('list_all_my_groups', 'list_all_my_groups');
        $this->register_modifier('cat_pp', 'cat_pp');
        $this->assign('it_is_xnet', true);
        if (!logged() && Get::has('auth')) {
            $_SESSION['session']->doAuthX($this);
        }
    }

    // }}}
}

// }}}
// {{{ class XnetAuth

/** Une classe pour les pages nécessitant l'authentification.
 * (equivalent de controlauthentification.inc.php)
 */
class XnetAuth extends XnetPage
{
    // {{{ function XnetAuth()

    function XnetAuth($tpl, $type=SKINNED)
    {
        $this->XnetPage($tpl, $type);
    }

    // }}}
    // {{{ function doAuth()

    function doAuth()
    {
        parent::doAuth();
        $_SESSION['session']->doAuth($this);
    }
    
    // }}}
}

// }}}
// {{{ class XnetAdmin

/** Une classe pour les pages réservées aux admins (authentifiés!).
 */
class XnetAdmin extends XnetAuth
{
    // {{{ function XnetAdmin()
    
    function XnetAdmin($tpl, $type=SKINNED)
    {
        global $globals;
        
        $this->XnetAuth($tpl, $type);
        check_perms();

        $this->useMenu();
        if ($globals->asso('cat')) {
            $this->assign('asso', $globals->asso());
            $this->setType($globals->asso('cat'));
        }
    }
    
    // }}}
}

// }}}
// {{{ class XnetGroupPage

/** Une classe pour les pages réservées aux admins (authentifiés!).
 */
class XnetGroupPage extends XnetAuth
{
    // {{{ function XnetAdmin()
    
    function XnetGroupPage($tpl, $type=SKINNED)
    {
        global $globals;

        $this->XnetAuth($tpl, $type);
        if (!is_member() && !has_perms()) {
            $this->kill("You have not sufficient credentials");
        }

        $this->useMenu();
        $this->assign('asso', $globals->asso());
        $this->setType($globals->asso('cat'));
    }
    
    // }}}
}

// }}}
// {{{ class XnetGroupAdmin

/** Une classe pour les pages réservées aux admins (authentifiés!).
 */
class XnetGroupAdmin extends XnetAuth
{
    // {{{ function XnetAdmin()
    
    function XnetGroupAdmin($tpl, $type=SKINNED)
    {
        global $globals;
        
        $this->XnetAuth($tpl, $type);
        if (!may_update()) {
            $this->kill("You have not sufficient credentials");
        }

        $this->useMenu();
        $this->assign('asso', $globals->asso());
        $this->setType($globals->asso('cat'));
    }
    
    // }}}
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
