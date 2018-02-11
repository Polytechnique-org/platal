{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

{if $isMe || hasPerm('admin') || empty($nw.address|smarty:nodefaults)}
  {assign var=hiddennw value=false}
{elseif hasPerm('directory_hidden') || (($nw.pub neq 'hidden') && ($nw.pub neq 'private'))}
  {assign var=hiddennw value=false}
{elseif hasPerm('directory_private') && ($nw.pub neq 'hidden')}
  {assign var=hiddennw value=false}
{else}
  {assign var=hiddennw value=true}
{/if}

<tr id="networking_{$i}">
  <td colspan="2">
    <div style="float: left; width: 200px;">
      <span class="flags">
        <label><input type="checkbox"
          {if $nw.pub neq 'private'} checked="checked"{/if}
          {if $hiddennw} disabled="disabled"{/if}
          name="networking[{$i}][pub]"/>
        {icon name="flag_green" title="site public"}</label>
      </span>&nbsp;
      <input type="hidden" name="networking[{$i}][type]" value="{$nw.type}"/>
      <input type="hidden" name="networking[{$i}][name]" value="{$nw.name}"/>
      <img src="profile/networking/{$nw.type}" alt="{$nw.name}" title="{$nw.name}" />
      <span style="">{$nw.name}</span>
    </div>
    <div style="float: left">
      {if $hiddennw}
      <input type="hidden" name="networking[{$i}][address]" value="{$nw.address}" />
      (masqué)
      {else}
      <input type="text" name="networking[{$i}][address]" value="{$nw.address}"
        {if $nw.error} class="error" {/if}
        size="30"/>
      {/if}
      <a href="javascript:removeNetworking({$i})">
        {icon name=cross title="Supprimer cet élément"}
      </a>
    </div>
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
