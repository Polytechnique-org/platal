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
        $Id: noIE.tpl,v 1.2 2004-08-31 11:25:43 x2000habouzit Exp $
 ***************************************************************************}

<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    {include file=skin/common.header.tpl}
    <link rel="stylesheet" type="text/css" href="{"css/noie.css"|url}" media="screen" />
    {if $xorg_head}
    {include file=$xorg_head}
    {/if}
    {include file=skin/common.bandeau.head.tpl}
  </head>
  <body>

  {if $simple}

    <div id="content">
      {include file=$xorg_tpl}
    </div>

  {else}

    <div id="top">
      {if $smarty.session.suid}
      <div id="suid">
        {dynamic}
        {$smarty.session.suid} ({$smarty.session.username})
        {/dynamic}
        [<a href="{"exit.php"|url}">exit</a>]
      </div>
      {/if}

      {include file=skin/common.bandeau.tpl}
     
      <div class="center">
        <img src="{"images/sk_sharky_ban.png"|url}" alt="[ BAN ]" />
        <span>
          {insert name="getNbIns"} polytechniciens sur le web
        </span>
      </div>

      <ul id="menu">
        {include_php file=menu.conf.php}
        {foreach key=menu_title item=menu_list from=$menu}
        {if $menu_title}
        
        <li>
        {$menu_title}
        <div class="liens">
          {foreach key=menu_item item=menu_url from=$menu_list}
          <a class="menu_item" href="{$menu_url|url}">{$menu_item}</a>
          {/foreach}
        </div>
        </li>
        
        {else}
        
        {foreach key=menu_item item=menu_url from=$menu_list}
        <li>
        <a class="menu_item" href="{$menu_url|url}">{$menu_item}</a>
        </li>
        {/foreach}
       
        {/if}
        {/foreach}
        
        {perms level=admin}
        <li><a href="{"admin/valider.php"|url}">{insert name="mkStats"}</a></li>
        {/perms}
        <li style="clear:both"></li>
      </ul>

    </div>
    
    <div id="content">
      {include file=$xorg_tpl}
      {include file=skin/common.devel.tpl}
    </div>

    <div id="bottom">
      {include file=skin/common.footer.tpl}
    </div>
  {/if}
  </body>
</html>
{* vim:set et sw=2 sts=2 sws=2: *}
