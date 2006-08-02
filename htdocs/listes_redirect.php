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

require_once dirname(__FILE__).'/../include/xorg.inc.php';

preg_match('/^\/(moderate|admin|members)\/(.*)_([^_]*)$/', $_SERVER['REQUEST_URI'], $matches);

if ($matches) {

    $action = $matches[1];
    $mbox   = $matches[2];
    $fqdn   = strtolower($matches[3]);

    if ($fqdn == 'polytechnique.org') {
        http_redirect("https://www.polytechnique.org/lists/$action/$mbox");
    }

    $res = XDB::query("select diminutif from groupex.asso where mail_domain = {?}", $fqdn);
    if ($gpx = $res->fetchOneCell()) {
        http_redirect("http://www.polytechnique.net/$gpx/lists/$action/$mbox");
    }
}
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
</html>
