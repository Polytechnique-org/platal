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
 ***************************************************************************
    $Id: xorg.inc.php,v 1.4 2004-11-23 11:32:34 x2000habouzit Exp $
 ***************************************************************************/

function microtime_float() 
{ 
    list($usec, $sec) = explode(" ", microtime()); 
    return ((float)$usec + (float)$sec); 
} 
$TIME_BEGIN = microtime_float();
 
// {{{ defines

$i=0;
define("AUTH_PUBLIC", $i++);
define("AUTH_COOKIE", $i++);
define("AUTH_MDP", $i++);

define("PERMS_EXT", "ext");
define("PERMS_USER", "user");
define("PERMS_ADMIN", "admin");

define('SKINNED', 0);
define('NO_SKIN', 1);

// }}}
// {{{ import class definitions

require_once("xorg.globals.inc.php");
require_once("xorg/menu.inc.php");
require_once("xorg/session.inc.php");

$globals = new XorgGlobals;

// }}}
// {{{ Build Menu, TODO: move that into appropriates hooks

$globals->menu = new XOrgMenu();

$globals->menu->addPrivateEntry(XOM_NO,       10, 'Page d\'accueil',       'login.php');

$globals->menu->addPrivateEntry(XOM_CUSTOM,   00, 'Mes emails',            'emails.php');
$globals->menu->addPrivateEntry(XOM_CUSTOM,   10, 'Mon profil',            'profil.php');
$globals->menu->addPrivateEntry(XOM_CUSTOM,   20, 'Mes contacts',          'profil.php');
$globals->menu->addPrivateEntry(XOM_CUSTOM,   30, 'Mon carnet',            'carnet/');
$globals->menu->addPrivateEntry(XOM_CUSTOM,   40, 'Mon mot de passe',      'motdepassemd5.php');
$globals->menu->addPrivateEntry(XOM_CUSTOM,   50, 'Mes préférences',       'preferences.php');

$globals->menu->addPrivateEntry(XOM_SERVICES, 00, 'Envoyer un mail',       'sendmail.php');
$globals->menu->addPrivateEntry(XOM_SERVICES, 10, 'Forums & PA',           'banana/');
$globals->menu->addPrivateEntry(XOM_SERVICES, 20, 'Listes de diffusion',   'listes/');
$globals->menu->addPrivateEntry(XOM_SERVICES, 30, 'Envoyer un mail',       'sendmail.php');
$globals->menu->addPrivateEntry(XOM_SERVICES, 40, 'Patte cassée',          'pattecassee.php');
       
$globals->menu->addPrivateEntry(XOM_GROUPS,   00, 'Annuaire',              'search.php');
$globals->menu->addPrivateEntry(XOM_GROUPS,   10, 'Trombi promo',          'trombipromo.php');
$globals->menu->addPrivateEntry(XOM_GROUPS,   20, 'Conseil Professionnel', 'referent.php');
$globals->menu->addPrivateEntry(XOM_GROUPS,   30, 'Groupes X',             'http://www.polytechnique.net/plan.php');
$globals->menu->addPrivateEntry(XOM_GROUPS,   40, 'Sites Polytechniciens', 'http://www.polytechnique.net/');

$globals->menu->addPrivateEntry(XOM_INFOS,    00, 'Lettres mensuelles',    'newsletter/');
$globals->menu->addPrivateEntry(XOM_INFOS,    10, 'Documentations',        'docs/');
$globals->menu->addPrivateEntry(XOM_INFOS,    20, 'Nous contacter',        'docs/contacts.php');
$globals->menu->addPrivateEntry(XOM_INFOS,    30, 'Emploi',                'http://www.manageurs.com/');

$globals->menu->addPrivateEntry(XOM_ADMIN,    00, 'Marketing',           'marketing/');
$globals->menu->addPrivateEntry(XOM_ADMIN,    10, 'Administration',      'admin/');
$globals->menu->addPrivateEntry(XOM_ADMIN,    20, 'Clear cache',         'clear_all_cache.php');

$globals->menu->addPublicEntry(XOM_US,    00, 'Me connecter !',         'login.php');
$globals->menu->addPublicEntry(XOM_US,    10, 'M\'inscrire',            'inscription/');
$globals->menu->addPublicEntry(XOM_US,    20, 'Pourquoi m\'inscrire ?', 'docs/services.php');

$globals->menu->addPublicEntry(XOM_EXT,   00, 'Annuaire de l\'X',       'search.php');
$globals->menu->addPublicEntry(XOM_EXT,   10, 'Associations X',         'http://www.polytechnique.net/');
$globals->menu->addPublicEntry(XOM_EXT,   20, 'Recrutement',            'http://www.manageurs.com/');

$globals->menu->addPublicEntry(XOM_INFOS, 00, 'A propos du site',       'docs/apropos.php');
$globals->menu->addPublicEntry(XOM_INFOS, 10, 'Nous contacter',         'docs/contacts.php');
$globals->menu->addPublicEntry(XOM_INFOS, 20, 'FAQ',                    'docs/faq.php');

// }}}
// {{{ function _new_page()

function _new_page($type, $tpl_name, $tpl_head, $min_auth, $admin=false)
{
    global $page;
    require_once("xorg.page.inc.php");
    if (!empty($admin)) {
        $page = new XorgAdmin($tpl_name, $type);
    } else switch($min_auth) {
        case AUTH_PUBLIC:
            $page = new XorgPage($tpl_name, $type);
            break;
            
        case AUTH_COOKIE:
            $page = new XorgCookie($tpl_name, $type);
            break;
            
        case AUTH_MDP:
            $page = new XorgAuth($tpl_name, $type);
    }

    $page->assign('xorg_head', $tpl_head);
    $page->assign('xorg_tpl', $tpl_name);
}

// }}}
// {{{ function new_skinned_page()

function new_skinned_page($tpl_name, $min_auth, $tpl_head="")
{
    _new_page(SKINNED, $tpl_name, $tpl_head, $min_auth);
}

// }}}
// {{{ function new_simple_page()

function new_simple_page($tpl_name, $min_auth, $tpl_head="")
{
    global $page;
    _new_page(SKINNED, $tpl_name, $tpl_head, $min_auth);
    $page->assign('simple', true);
}

// }}}
// {{{ function new_nonhtml_page()

function new_nonhtml_page($tpl_name, $min_auth)
{
    _new_page(NO_SKIN, $tpl_name, "", $min_auth, false);
}

// }}}
// {{{ function new_admin_page()

function new_admin_page($tpl_name, $tpl_head="")
{
    _new_page(SKINNED, $tpl_name, $tpl_head, AUTH_MDP, true);
}

// }}}
// {{{ function new_admin_table_editor()

function new_admin_table_editor($table, $idfield, $idedit=false)
{
    global $editor;
    new_admin_page('table-editor.tpl');
    require_once('xorg.table-editor.inc.php');
    $editor = new XOrgAdminTableEditor($table,$idfield,$idedit);
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
