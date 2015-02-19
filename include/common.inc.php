<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

function xorg_autoload($cls)
{
    if (!pl_autoload($cls)) {
        $cls = strtolower($cls);
        if (substr($cls, 0, 4) == 'ufc_' || substr($cls, 0, 4) == 'ufo_' || $cls == 'profilefilter' || $cls == 'userfiltercondition' || $cls == 'userfilterorder') {
            xorg_autoload('userfilter');
            return;
        } else if (substr($cls, 0, 4) == 'pfc_'
                || substr($cls, 0, 4) == 'pfo_'
                || substr($cls, 0, 8) == 'plfilter') {
            xorg_autoload('plfilter');
            return;
        } else if ($cls == 'direnumeration' || substr($cls, 0, 3) == 'de_') {
            xorg_autoload('direnum');
            return;
        } else if ($cls == 'validate' || substr($cls, -3, 3) == 'req'
                   || substr($cls, -8, 8) == 'validate' || substr($cls, 0, 8) == 'validate') {
            require_once 'validations.inc.php';
            return;
        } else if (substr($cls, 0, 6) == 'banana') {
            require_once 'banana/hooks.inc.php';
            Banana::load(substr($cls, 6));
            return;
        } else if (substr($cls, 0, 5) == 'raven') {
            // Handled by Raven autoloader.
            return;
        }
        include "$cls.inc.php";
    }
}

function __autoload($cls)
{
    return xorg_autoload($cls);
}

spl_autoload_register('xorg_autoload');

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
