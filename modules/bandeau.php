<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

class BandeauModule extends PLModule
{
    function handlers()
    {
        return array(
            'bandeau/icone.png'  => $this->make_hook('icone',AUTH_PUBLIC, 'user', NO_HTTPS),
            'bandeau'            => $this->make_hook('html', AUTH_PUBLIC, 'user', NO_HTTPS),
            'bandeau.css'				 => $this->make_hook('css', AUTH_PUBLIC, 'user', NO_HTTPS),
        );
    }

    function handler_icone(&$page)
    {
        header("Content-Type: image/png");
        readfile('../htdocs/images/x.png');
        exit();
    }
    
    function handler_html(&$page, $login = '')
    {
        $page->changeTpl('skin/common.bandeau.tpl', NO_SKIN);
        $page->assign('login', $login);
    }

    function handler_css(&$page)
    {
        header("Content-Type: text/css");
        readfile('../htdocs/css/bandeau.css');
        exit();
    }
    
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
