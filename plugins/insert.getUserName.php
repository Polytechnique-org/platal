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

function smarty_insert_getUsername()
{
    global $globals;

    $id = Cookie::getInt('ORGuid', -1);
    $id = S::v($_SESSION['uid'], $id);

    if ($id<0) {
        return "";
    }

    if (Cookie::get('ORGdomain', 'login') != 'alias') {
	$res = XDB::query("SELECT  alias FROM aliases
	                              WHERE  id={?} AND (type IN ('a_vie','alias') AND FIND_IN_SET('bestalias', flags))", $id);
	return $res->fetchOneCell();
    } else {
	$res = XDB::query("
		SELECT v.alias
	          FROM virtual AS v
	    INNER JOIN virtual_redirect USING(vid)
	    INNER JOIN aliases AS a ON(id={?} AND a.type='a_vie')
                 WHERE redirect = CONCAT(a.alias, {?}) 
		       OR redirect = CONCAT(a.alias, {?})",
		$id, "@".$globals->mail->domain, "@".$globals->mail->domain2);
	$alias = $res->fetchOneCell();
	return substr($alias, 0, strpos($alias, "@"));
     }

     return $login;
}

?>
