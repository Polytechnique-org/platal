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

// {{{ config HOOK

// {{{ class SearchConfig

class SearchConfig
{
    var $public_max  =  25;
    var $private_max = 800;

    var $per_page    =  20;
}

// }}}

function search_search(&$result)
{
    global $glabals;
    $globals->search = new SearchConfig;
}

// }}}
// {{{ menu HOOK

function search_menu(&$result)
{
    global $globals;
    $globals->menu->addPrivateEntry(XOM_GROUPS, 00, 'Annuaire',         'search.php');
    $globals->menu->addPublicEntry(XOM_EXT,     00, 'Annuaire de l\'X', 'search.php');
}

// }}}

?>
