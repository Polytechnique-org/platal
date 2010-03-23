{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

<tr id="search_name_{$i}"{if $class} class="{$class}" {if !$error_name}style="{$style}"{/if}{/if}>
  <td>
    <input type="hidden" name="search_names[{$i}][always_displayed]" value="{$sn.always_displayed}"/>
    <input type="hidden" name="search_names[{$i}][has_particle]" value="{$sn.has_particle}"/>
    <span class="flags">
      <input id="flag_cb_{$i}" type="checkbox" checked="checked" disabled="disabled"/>
      <span id="flag_{$i}">{if $sn.pub}{icon name="flag_green" title="site public"}
      {else}{icon name="flag_red" title="site privé"}{/if}</span>
    </span>&nbsp;
    {if $sn_type_list}
    <select id="search_name_select_{$i}" name="search_names[{$i}][typeid]" onchange="changeNameFlag({$i});updateNameDisplay();">
        {foreach from=$sn_type_list item=sn_type}
          <option value="{$sn_type.id}">{$sn_type.name}</option>
        {/foreach}
    </select>
    {foreach from=$sn_type_list item=sn_type}
    <input type="hidden" name="sn_type_{$sn_type.id}_{$i}" value="{$sn_type.pub}"/>
    {/foreach}
    {else}
    {$sn.type_name}
    <input type="hidden" name="search_names[{$i}][pub]" value="{$sn.pub}"/>
    <input type="hidden" name="search_names[{$i}][type]" value="{$sn.type}"/>
    <input type="hidden" name="search_names[{$i}][type_name]" value="{$sn.type_name}"/>
    <input type="hidden" name="search_names[{$i}][typeid]" value="{$sn.typeid}"/>
    {/if}
  </td>
  <td>
    <input type="text" name="search_names[{$i}][name]" value="{$sn.name}"
      {if $sn.has_particle}title="Coche la case en bout de ligne si ton nom commence par une particle."{/if}
      {if $sn.error} class="error"{/if} size="25" onkeyup="updateNameDisplay();"/>
  </td>
  <td>
    {if $sn.has_particle}<input type="checkbox"{if $sn.particle neq ''} checked="checked"{/if}
      title="Coche cette case si ton nom commence par une particle." onchange="toggleParticle({$i});"/>
    {/if}
    <input type="hidden"  name="search_names[{$i}][particle]" value="{$sn.particle}"/>
    {if !$sn.always_displayed}<a href="javascript:removeSearchName({$i})">
      {icon name=cross title="Supprimer ce nom"}
    </a>{/if}
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
