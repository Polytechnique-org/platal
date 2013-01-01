{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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

<h1>Aliases</h1>

<table cellspacing="0" cellpadding="0" class="tinybicol">
  <tr>
    <th>Aliases</th>
  </tr>
  {if $aliases|@count}
  {foreach from=$aliases item=alias}
  <tr>
    <td><a href="admin/aliases/{$alias}">{$alias}</a></td>
  </tr>
  {/foreach}
  {else}
  <tr>
    <td>Aucun alias</td>
  </tr>
  {/if}
</table>

<form method="post" action="admin/aliases">
  {xsrf_token_field}
  <p class="center">
    <input type="text" name="new_alias" />
    <input type="submit" value="Créer l'alias" />
  </p>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
