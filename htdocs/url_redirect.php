<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

require_once 'xorg.inc.php';

$platal = new Xorg('core');

global $globals;
$alias = ltrim($platal->pl_self(), '/');

if (preg_match('/^[a-zA-Z0-9\-\/]+$/i', $alias)) {
    $url = XDB::fetchOneCell('SELECT  url
                                FROM  url_shortener
                               WHERE  alias = {?}',
                             $alias);
    if ($url) {
        http_redirect($url);
    }
}

header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
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
    <address><?php echo $_SERVER['SERVER_SIGNATURE'] ?></address>
  </body>
</html>
<?php
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
