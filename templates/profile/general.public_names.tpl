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

{foreach from=$lastnames key=suffix item=description}
{assign var=type value="lastname_"|cat:$suffix}
{assign var=error value=$type|cat:"_error"}
{assign var=particle value="particle_"|cat:$suffix}
<tr class="names_advanced_public" {if !$errors.search_names}style="display: none"{/if}>
  <td>
    <span class="flags">{icon name="flag_green" title="site public"}</span>&nbsp;{$description}
  </td>
  <td>
    <input type="text" name="search_names[public_names][{$type}]" value="{$names.$type}"
      {if t($names.$error)} class="error"{/if} size="25" onkeyup="updateNameDisplay({$isFemale});"/>
  </td>
  <td></td>
</tr>
{/foreach}

{foreach from=$firstnames key=type item=description}
{assign var=error value=$type|cat:"_error"}
<tr class="names_advanced_public" {if !$errors.search_names}style="display: none"{/if}>
  <td>
    <span class="flags">{icon name="flag_green" title="site public"}</span>&nbsp;{$description}
  </td>
  <td>
    <input type="text" name="search_names[public_names][{$type}]" value="{$names.$type}"
      {if t($names.$error)} class="error"{/if} size="25" onkeyup="updateNameDisplay({$isFemale});" />
  </td>
  <td></td>
</tr>
{/foreach}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
