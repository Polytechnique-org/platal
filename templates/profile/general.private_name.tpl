{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

{assign var=type value=$name.type}
<tr class="names_advanced_private" id="search_name_{$id}" {if !$errors.search_names && !t($new_name)}style="display: none"{/if}>
  <td>
    <span class="flags">{icon name="flag_red" title="site privé"}</span>{if !t($new_name)}&nbsp;{$other_names.$type}{else}
    <select name="search_names[private_names][{$id}][type]">
    {foreach from=$other_names item=description key=type}
      <option value="{$type}">{$description}</option>
    {/foreach}
    </select>
    {/if}
  </td>
  <td>
    {if !t($new_name)}<input type="hidden" name="search_names[private_names][{$id}][type]" value="{$type}" />{/if}
    <input type="text" name="search_names[private_names][{$id}][name]" value="{$name.name}"
      size="25" onkeyup="updateNameDisplay({$isFemale});"/>
  </td>
  <td>
    <a href="javascript:removeSearchName({$id}, {$isFemale})">{icon name=cross title="Supprimer ce nom"}</a>
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
