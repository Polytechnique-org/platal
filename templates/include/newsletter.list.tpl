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
        $Id: newsletter.list.tpl,v 1.5 2004-08-31 11:25:40 x2000habouzit Exp $
 ***************************************************************************}


<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
  <tr>
    <th>date</th>
    <th>titre</th>
    <th>&nbsp;</th>
  </tr>
  {foreach item=nl from=$nl_list}
  <tr class="{cycle values="impair,pair"}">
    <td>{$nl.date|date_format:"%Y-%m-%d"}</td>
    <td>
      <a href="{"newsletter.php"|url}?nl_id={$nl.id}">{$nl.titre}</a>
    </td>
    {if $admin}
    <td>
      <form method="post" action="{$smarty.server.PHP_SELF}">
        <div>
          <input type="hidden" name="nl_id" value="{$nl.id}" />
          <input type="hidden" name="action" value="edit" />
          <input type="submit" value="edit" />
          <input type="submit" value="del" />
        </div>
      </form>
    </td>
    {else}
    <td>
      &nbsp;
    </td>
    {/if}
  </tr>
  {/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
