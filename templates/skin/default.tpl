{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

{include file=skin/common.doctype.tpl}
    <link rel="stylesheet" type="text/css" href="css/default.css" media="all" />
    {include file=skin/common.header.tpl}
    {include file=skin/common.bandeau.head.tpl}
  </head>
  <body>
    {include file=skin/common.devel.tpl}
    {if !$simple}
      {include file=skin/common.bandeau.tpl}
    {/if}
    {if $smarty.session.suid}
    <div id="suid">
      <a href="exit">
        Quitter le SU sur {$smarty.session.forlife} ({$smarty.session.perms->flags()})
      </a>
    </div>
    {/if}

  {if $simple}

    <div id="content">
      {include file="skin/common.content.tpl"}
    </div>

  {else}

    <table id="body" cellpadding="0" cellspacing="0">
      <tr>
        <td id="body-logo">
          <a href=""><img src="images/skins/default_headlogo.jpg" alt="[ LOGO ]" /></a>
        </td>
        <td id="body-top">
          <a href=""><img src="images/skins/default_ban.jpg" alt="[ Polytechnique.org ]" /></a>
          {include file="skin/common.title.header.tpl"}
          <a href=""><img src="images/skins/default_lesX.gif" alt="[LES X SUR LE WEB]" /></a>
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
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
