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

require_once('platal/env.inc.php');

// }}}
// {{{ function redirect

function redirect($page)
{
    if (count($_SESSION)) {
        session_write_close();
    }
    header("Location: $page");
    exit;
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
