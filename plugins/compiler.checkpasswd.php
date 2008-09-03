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
      $width = '230px';
    }
    if (!isset($prompt)) {
      $prompt = "'nouveau'";
    }
    if (!isset($submit)) {
      $submit = "'submitn'";
    }
    if (!isset($text)) {
      $text = "'Changer'";
    }

    return '?><script type="text/javascript" src="javascript/jquery.js" ></script>
              <script type="text/javascript" src="javascript/jquery.color.js" ></script>
              <script type="text/javascript">//<![CDATA[
                var passwordprompt_name = '.  $prompt . ';
                var passwordprompt_submit = ' . $submit . ';
                $(":input[@name=' . $prompt . ']").keyup(function(event) { checkPassword(event.target, ' . $text . '); });
                $(document).ready(function() {
                  checkPassword($(":input[@name=' . $prompt . ']").get(0), ' . $text . ');
                });
              //]]></script>
              <div>
                <div style="border: 1px solid white; width: ' . $width . '; height: 7px; background-color: #444; margin-top: 4px; float: left">
                  <div id="passwords_measure" style="height: 100%; background-color: red; width: 0px"></div>
                </div>
                <a href="Xorg/MDP?display=light" style="display: block; float: left; margin-left: 4px;" class="popup_600x800">
                  <img src="images/icons/information.gif" alt="Aide" title="Comment construire un mot de passe fort..." />
                </a>
              </div><?php';
}

/* vim: set expandtab enc=utf-8: */

?>
