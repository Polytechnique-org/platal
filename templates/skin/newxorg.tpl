{***************************************************************************
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
        $Id: newxorg.tpl,v 1.1 2004-10-03 22:32:48 x2003bruneau Exp $
 ***************************************************************************}

<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    {include file=skin/common.header.tpl}
    <link rel="stylesheet" type="text/css" href="{"css/default.css"|url}" media="screen" />
    <link rel="stylesheet" type="text/css" href="{"css/newxorg.css"|url}" media="screen" />
    {if $xorg_head}
    {include file=$xorg_head}
    {/if}
    {include file=skin/common.bandeau.head.tpl}
  </head>
  <body>
    {include file=skin/common.devel.tpl}

    {if $smarty.session.suid}
    <table id="suid" cellpadding="0" cellspacing="0">
      <tr>
        <td>
          {dynamic}
          {$smarty.session.suid} ({$smarty.session.forlife})
          {/dynamic}
          [<a href="{"exit.php"|url}">exit</a>]
        </td>
      </tr>
    </table>
    {/if}

  {if $simple}

    <div id="content">
      {include file=$xorg_tpl}
    </div>

  {else}

    {include file=skin/common.bandeau.tpl}

    <table id="body" cellpadding="0" cellspacing="0">
      <tr>
        <td id="body-logo">
          <img src="{"images/sk_newxorg_logo.jpg"|url}" alt="[ LOGO ]" />
        </td>
        <td id="body-top">
          <img src="{"images/sk_newxorg_ban.png"|url}" alt="[ Polytechnique.org ]" />
          <table>
            <tr>
              <td class="date-heure">
                <script type="text/javascript">
                  <!--
                  document.write(getNow());
                  //-->
                </script>
              </td>
              <td class="inscrits">{insert name="getNbIns"} polytechniciens sur le web</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td id="body-menu">
          {include_php file=menu.conf.php}
          {foreach key=menu_title item=menu_list from=$menu}
          {if $menu_title}
          <div class="menu_title">{$menu_title}</div>
          {/if}
          {foreach key=menu_item item=menu_url from=$menu_list}
          <div class="menu_item"><a href="{$menu_url|url}">{$menu_item}</a></div>
          {/foreach}
          {/foreach}
          {perms level=admin}
          <table class="bicol" style="font-weight:normal;text-align:center; border-left:0px; border-right:0px; margin-top:0.5em; width:100%; margin-left: 0; font-size: smaller;">
            <tr><th>Valid</th></tr>
            <tr class="impair">
              <td><a href="{"admin/valider.php"|url}">{insert name="mkStats"}</a></td>
            </tr>
          </table>
          {/perms}
        </td>
        <td id="content">
          {include file=$xorg_tpl}
        </td>
      </tr>
      <tr>
        <td id="body-bottom" colspan="2">
          {include file=skin/common.footer.tpl}
        </td>
      </tr>
    </table>
  {/if}
  </body>
</html>
{* vim:set et sw=2 sts=2 sws=2: *}
