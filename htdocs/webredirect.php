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

require_once dirname(__FILE__).'/../include/xorg.inc.php';

global $globals;
list($username, $path) = preg_split('/\//', $_SERVER["REQUEST_URI"], 2, PREG_SPLIT_NO_EMPTY);
$res = XDB::query(
        "SELECT  redirecturl
           FROM  auth_user_quick AS a
     INNER JOIN  aliases         AS al ON (al.id = a.user_id AND (al.type='a_vie' OR al.type='alias'))
      LEFT JOIN  virtuals_redirect AS vr ON (vr.redirect LIKE CONCAT(al.alias, '@%')
     INNER JOIN  virtuals        AS v ON (v.vid = vr.vid)
          WHERE  al.alias = {?} OR v.alias = CONCAT({?}, '@', {?})
       GROUP BY  redirecturl", $username, $username, $globals->mail->alias_dom);

if ($url = $res->fetchOneCell()) {
    $url = preg_replace('@/+$@', '', $url);
    if ($path) {
        http_redirect("http://$url/$path");
    } else {
        http_redirect("http://$url");
    }
}

header("HTTP/1.0 404 Not Found");

?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
  <head>
    <title>404 Not Found</title>
  </head>
  <body>
    <h1>Not Found</h1>
    The requested URL <?php echo $_SERVER['REQUEST_URI'] ?> was not found on this server.<p>
    <hr>
    <address>Apache Server at www.carva.org Port 80</address>
  </body>
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
