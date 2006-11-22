{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}
<?xml version="1.0" encoding="iso-8859-15"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <base href="{$globals->baseurl}/" />
    <link rel="stylesheet" type="text/css" href="css/default.css" media="all" />
    <link rel="stylesheet" type="text/css" href="css/espace.css" media="all" />
    {include file=skin/common.header.tpl}
    {include file=skin/common.bandeau.head.tpl}
  </head>
  <body>
    {include file=skin/common.devel.tpl}

    {if $smarty.session.suid}
    <table id="suid" cellpadding="0" cellspacing="0">
      <tr>
        <td>
          {$smarty.session.suid} ({$smarty.session.forlife})
          [<a href="exit">exit</a>]
        </td>
      </tr>
    </table>
    {/if}

  {if $simple}

    <div id="content">
      {include file="skin/common.content.tpl"}
    </div>

  {else}

    {include file=skin/common.bandeau.tpl}

    <table id="body" cellpadding="0" cellspacing="0">
      <tr>
        <td id="body-logo">
          <a href="events"><img src="images/skins/espace_logo.gif" alt="[ LOGO ]" /></a>
        </td>
        <td id="body-top">
          <a href="events"><img src="images/skins/espace_ban.jpg" alt="[ Polytechnique.org ]" /></a>
          {include file="skin/common.title.header.tpl"}
          <a href="events"><img src="images/skins/espace_lesX.gif" alt="[LES X SUR LE WEB]" /></a>
        </td>
      </tr>
      <tr>
        <td id="body-menu">
        {include file=skin/common.menu.tpl}
        </td>
        <td id="content">
        {include file="skin/common.content.tpl"}
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
