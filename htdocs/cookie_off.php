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
        $Id: cookie_off.php,v 1.6 2004/11/22 20:04:35 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_skinned_page('index.tpl', AUTH_COOKIE);

setcookie('ORGaccess','',(time()+1),'/','',0);
$_SESSION['log']->log("cookie_off");

// si on a le cookie, et qu'on est pas identifié,
// s'enlever le cookie revient à se déconnecter
if(!identified()) {
    session_destroy();
    $_SESSION = array();
    header("Location: index.php");
}

$page->run();
?>
