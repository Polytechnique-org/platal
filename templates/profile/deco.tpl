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

<table class="bicol">
  <tr>
    <th>
      <div class="flags" style="float: left">
        <label><input type="checkbox" name="medals_pub"{if $medals_pub eq 'public'} checked="checked"{/if} />
        {icon name="flag_green" title="site public"}</label>
      </div>
      Médailles, Décorations, Prix&hellip;
    </th>
  </tr>
  <tr>
    <td>
      <div style="clear: both; margin-top: 0.2em" id="medals">
        <select name="medal_sel" onchange="updateMedal()">
          <option value=''>&nbsp;</option>
          {foreach from=$medal_list key=type item=list}
          <optgroup label="{$fullType[$type]}&hellip;">
            {foreach from=$list item=m}
            <option value="{$m.id}">{$m.text}</option>
            {/foreach}
          </optgroup>
          {/foreach}
        </select>
        <span id="medal_add" style="display: none">
          <a href="javascript:addMedal();">{icon name=add title="Ajouter cette médaille"}</a>
        </span>
      </div>
      {foreach from=$medals item=medal key=id}
      {include file="profile/deco.medal.tpl" medal=$medal id=$id}
      {/foreach}
      <p class="center">
        <small>
          Si {if $isMe}ta{else}la{/if} décoration
          ou {if $isMe}ta{else}la{/if} médaille ne figure pas dans la liste,
          <a href="mailto:support@{#globals.mail.domain#}">contacte-nous</a>.
        </small>
      </p>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
