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
        $Id: cookie_on.php,v 1.6 2004-11-22 20:04:35 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_skinned_page('cookie_on.tpl', AUTH_MDP);

$res = @$globals->db->query( "SELECT password FROM auth_user_md5 WHERE user_id='{$_SESSION['uid']}'" );
list($password)=mysql_fetch_row($res);
$cookie=md5($password);
@mysql_free_result($res);

setcookie('ORGaccess',$cookie,(time()+25920000),'/','',0);
$_SESSION['log']->log("cookie_on");

$page->run();
?>
