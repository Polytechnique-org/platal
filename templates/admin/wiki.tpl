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

<h1>Pages wiki du site</h1>

<form action="admin/wiki/update" method="post">
<table class="bicol">
  <tr>
    <th>
      page
    </th>
    <th>
      lecture
    </th>
    <th>
      écriture
    </th>
    <th class="action">
      action
    </th>
  </tr>
{foreach from=$wiki_pages item=perm key=page}
  <tr class="{cycle values="impair,pair"}">
    <td>
      <a href="{$page|replace:'.':'/'}">{$page}</a> <a href="{$page|replace:'.':'/'}?action=edit" class="indice">{icon name=date_edit title='éditer'}</a>
    </td>
    <td class="center">
      {$perm.read}
    </td>
    <td class="center">
      {$perm.edit}
    </td>
    <td class="action">
      <input type="checkbox" name="{$page|replace:'.':'/'}"/>
    </td>
  </tr>
{/foreach}
  <tr>
    <td>
      Attribue les permissions aux pages cochées :
    </td>
    <td>
      <select name="read">
        <option value=""> - </option>
        {html_options options=$perms_opts}
      </select>
    </td>
    <td>
      <select name="edit">
        <option value=""> - </option>
        {html_options options=$perms_opts}
      </select>
    </td>
    <td class="option">
      <input type="submit" value="ok"/>
    </td>
  </tr>
</table>
</form>
{* vim:set et sw=2 sts=2 sws=2: *}
