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

function tmp_menu()
{
    global $globals;

    $globals->menu->addPrivateEntry(XOM_CUSTOM,   10, 'Mon profil',         'profile/edit');
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   20, 'Mes contacts',       'carnet/contacts');
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   30, 'Mon carnet',         'carnet/');
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   40, 'Mon mot de passe',   'password');
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   50, 'Mes préférences',    'prefs');

    $globals->menu->addPrivateEntry(XOM_GROUPS,   10, 'Trombi promo',       'trombi');
    $globals->menu->addPrivateEntry(XOM_GROUPS,   20, 'Conseil Pro.',       'referent/search');
    if ($globals->geoloc->use_map())
        $globals->menu->addPrivateEntry(XOM_GROUPS,   10, 'Planisphère',    'geoloc/');
    $globals->menu->addPrivateEntry(XOM_GROUPS,   30, 'Groupes X',          'http://www.polytechnique.net/plan');

    $globals->menu->addPrivateEntry(XOM_INFOS,    10, 'Documentations',     'Docs/');
    $globals->menu->addPrivateEntry(XOM_INFOS,    20, 'Nous contacter',     'Docs/NousContacter');
    $globals->menu->addPrivateEntry(XOM_INFOS,    30, 'Carrières',          'Docs/Emploi');

    $globals->menu->addPrivateEntry(XOM_ADMIN,    00, 'Marketing',          'marketing');
    $globals->menu->addPrivateEntry(XOM_ADMIN,    10, 'Administration',     'admin/');
    $globals->menu->addPrivateEntry(XOM_ADMIN,    20, 'Clear cache',        'purge_cache');
    $globals->menu->addPrivateEntry(XOM_ADMIN,    30, 'Trackers',           'http://trackers.polytechnique.org');
    $globals->menu->addPrivateEntry(XOM_ADMIN,    40, 'Support',            'http://support.polytechnique.org');

    $globals->menu->addPublicEntry(XOM_US,    00, 'Me connecter !',         'events');
    $globals->menu->addPublicEntry(XOM_US,    10, 'M\'inscrire',            'register/');
    $globals->menu->addPublicEntry(XOM_US,    20, 'Pourquoi m\'inscrire ?', 'Docs/PourquoiM\'Inscrire');

    $globals->menu->addPublicEntry(XOM_EXT,   10, 'Associations X',         'http://www.polytechnique.net/');
    $globals->menu->addPublicEntry(XOM_EXT,   20, 'Recrutement',            'http://www.manageurs.com/');

    $globals->menu->addPublicEntry(XOM_INFOS, 00, 'A propos du site',       'Docs/APropos');
    $globals->menu->addPublicEntry(XOM_INFOS, 10, 'Nous contacter',         'Docs/NousContacter');
    $globals->menu->addPublicEntry(XOM_INFOS, 20, 'FAQ',                    'Docs/FAQ');
}

// {{{ subscribe HOOK

function tmp_subscribe($forlife, $uid, $promo, $password)
{

    require_once('notifs.inc.php');
    register_watch_op($uid, WATCH_INSCR);
    inscription_notifs_base($uid);
}

// }}}
// {{{ prfs hook

function tmp_prefs()
{
    $fmt  = S::v('mail_fmt', 'html') == 'html' ? 'texte' : 'html';
    $fmt2 = S::v('mail_fmt', 'html') == 'html' ? 'texte' : 'HTML';
    return Array(
            Array(
                'url'    => 'prefs?mail_fmt='.$fmt,
                'title'  => 'Recevoir les mails en format '.$fmt2,
                'text'   => 'Tu recois les mails envoyés par le site (lettre mensuelle, carnet, ...) de préférence <strong>sous forme de '
                            .S::v('mail_fmt', 'html').'</strong>',
                'weight' => 80
            ),
            Array(
                'url'    => 'prefs?rss='.(intval(S::v('core_rss_hash')=='')),
                'title'  => (S::v('core_rss_hash') ? 'Désactiver' : 'Activer').' les fils rss',
                'text'   => 'Ceci te permet d\'utiliser les fils rss du site. Attention, désactiver puis réactiver les fils en change les URL !',
                'weight' => 90
            )
        );
}

// }}}
?>
