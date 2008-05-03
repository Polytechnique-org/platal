<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
require_once 'platal.inc.php';

function smarty_compiler_checkpasswd($tag_attrs, &$compiler)
{
    extract($compiler->_parse_attrs($tag_attrs));
    if (!isset($width)) {
      $width = '250px';
    }
    if (!isset($id)) {
      $id = "newpassword";
    }

    return '?><script type="text/javascript" src="javascript/jquery.js" ></script>
              <script type="text/javascript">//<![CDATA[
                $(":input[@name=' . $id . ']").keyup(function(event) { checkPassword(event.target); });
              //]]></script>
              <div style="border: 1px solid white; width: ' . $width . '; height: 7px; background-color: #444">
                <div id="passwords_measure" style="height: 100%; background-color: red; width: 0px"></div>
              </div><?php';
}

/* vim: set expandtab enc=utf-8: */

?>
