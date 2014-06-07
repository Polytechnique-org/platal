{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

{if $isMe || hasPerm('admin') || empty($hobby.text|smarty:nodefaults)}
  {assign var=hiddenhobby value=false}
{elseif hasPerm('directory_hidden') || (($hobby.pub neq 'hidden') && ($hobby.pub neq 'private'))}
  {assign var=hiddenhobby value=false}
{elseif hasPerm('directory_private') && ($hobby.pub neq 'hidden')}
  {assign var=hiddenhobby value=false}
{else}
  {assign var=hiddenhobby value=true}
{/if}

<tr id="hobby_{$i}">
  <td colspan="2">
    <div style="float: left; width: 200px;">
      <span class="flags">
        <label>
          <input type="checkbox" {if $hobby.pub neq 'private'}checked="checked"{/if}
                 {if $hiddenhobby} disabled="disabled"{/if} name="hobbies[{$i}][pub]" />
        {icon name="flag_green" title="site public"}
      </label>
      </span>&nbsp;
      <input type="hidden" name="hobbies[{$i}][type]" value="{$hobby.type}" />
      <span>{$hobby.type}</span>
    </div>
    <div style="float: left">
      {if $hiddenhobby}
      <input type="hidden" name="hobbies[{$i}][text]" value="{$hobby.text}" />
      (masqué)
      {else}
      <input type="text" name="hobbies[{$i}][text]" value="{$hobby.text}" size="30" />
      {/if}
      <a href="javascript:removeHobby({$i})">
        {icon name=cross title="Supprimer cet élément"}
      </a>
    </div>
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
