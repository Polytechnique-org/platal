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
        $Id: insert.password.inc.php,v 1.2 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/

function smarty_insert_getName() {
    $pre = strtok($_COOKIE['ORGlogin'],".");
    $pre1=strtok($pre,"-");
    $pre2=strtok(" ");
    $pre1=ucfirst($pre1);
    $pre2=ucfirst($pre2);
    if ($pre2) {
        $prenom = $pre1."-".$pre2;
    } else {
        $prenom = $pre1;
    }
    return $prenom;
}

function smarty_insert_getUsername() {
    return isset($_SESSION['username'])
        ? $_SESSION['username']
        : (isset($_COOKIE['ORGlogin']) ? $_COOKIE['ORGlogin'] : "");
}
?>
