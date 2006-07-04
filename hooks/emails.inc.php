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

// {{{ config HOOK

// {{{ class SkinConfig

class MailConfig
{
    var $domain     = '';
    var $domain2    = '';

    var $alias_dom  = '';
    var $alias_dom2 = '';

    var $send_form  = true;

    function shorter_domain()
    {
        if (empty($this->domain2) || strlen($this->domain2)>strlen($this->domain)) {
            return $this->domain;
        } else {
            return $this->domain2;
        }
    }
}

// }}}

function emails_config()
{
    global $globals;
    $globals->mail = new MailConfig;
}
// }}}
// {{{ menu HOOK

function emails_menu()
{
    global $globals;
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   00, 'Mes emails',          'emails');

    if ($globals->mail->send_form) {
        $globals->menu->addPrivateEntry(XOM_SERVICES, 00, 'Envoyer un mail', 'emails/send');
    }

    $globals->menu->addPrivateEntry(XOM_SERVICES, 40, 'Patte cassée',        'emails/broken');
    
}

// }}}
// {{{ prefs HOOK

function emails_prefs()
{
    global $globals;
    
    $res[] = Array(
            'url'    => 'emails',
            'title'  => 'Mes adresses de redirection',
            'text'   => 'Tu peux configurer tes différentes redirections de mails ici.',
            'weight' => 10
    );

    if ($globals->mail->alias_dom) {
        $res [] = Array(
                'url'    => 'emails/alias',
                'title'  => 'Mon alias mail @'.$globals->mail->alias_dom,
                'text'   => "Pour choisir un alias @{$globals->mail->alias_dom}/{$globals->mail->alias_dom2} (en choisir un nouveau annule l'ancien).",
                'weight' => 20
        );
    }


    return $res;
}

// }}}
?>
