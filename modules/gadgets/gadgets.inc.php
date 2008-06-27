<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

// iGoogle gadgets helpers.
function init_igoogle_xml($template)
{
    Platal::page()->changeTpl($template, NO_SKIN);

    header('Content-Type: application/xml; charset=utf-8');
}

function init_igoogle_html($template, $auth = AUTH_PUBLIC)
{
    $page =& Platal::page();
    $page->changeTpl('gadgets/ig-skin.tpl', NO_SKIN);
    $page->register_modifier('escape_html', 'escape_html');
    $page->default_modifiers = Array('@escape_html');
    header('Accept-Charset: utf-8');

    // Adds external JavaScript libraries provided by iGoogle to the page.
    if (Env::has('libs')) {
        $libs = split(',', Env::s('libs'));
        foreach ($libs as $lib) {
            if (preg_match('@^[a-z0-9/._-]+$@i', $lib) && !preg_match('@([.][.])|([.]/)|(//)@', $lib)) {
                $page->append('gadget_js', 'https://www.google.com/ig/f/' . $lib);
            }
        }
    }

    // Redirects the user to the login pagin if required.
    if ($auth >  S::v('auth', AUTH_PUBLIC)) {
        $page->assign('gadget_tpl', 'gadgets/ig-login.tpl');
        return false;
    }

    $page->assign('gadget_tpl', $template);
    return true;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
