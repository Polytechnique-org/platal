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
    $Id: xorg.common.inc.php,v 1.11 2004-11-22 10:42:52 x2000habouzit Exp $
 ***************************************************************************/

// {{{ defines

$i=0;
define("AUTH_PUBLIC", $i++);
define("AUTH_COOKIE", $i++);
define("AUTH_MDP", $i++);

define("PERMS_EXT", "ext");
define("PERMS_USER", "user");
define("PERMS_ADMIN", "admin");

define('SKIN_COMPATIBLE','default.tpl');
define('SKIN_COMPATIBLE_ID',1);

define('SKINNED', 0);
define('NO_SKIN', 1);

// }}}
// {{{ import class definitions

require_once("xorg.globals.inc.php");
require_once("xorg/menu.inc.php");
require_once("xorg/session.inc.php");

$globals = new XorgGlobals;
require("xorg.config.inc.php");
$globals->menu = new XOrgMenu();

// }}}
// {{{ start session + database connection

session_start();

// connect to database
$globals->dbconnect();
if ($globals->debug) {
    $globals->db->trace_on();
}

//}}}

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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
