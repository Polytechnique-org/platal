{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

{assign var=eduname value="edus[`$eduid`]"}
<tr class="edu_{$eduid} {$class}">
  <td colspan="2">
    <a href="javascript:removeEdu('edu_{$eduid}')">
      {icon name=cross title="Supprimer cette formation"}
    </a>
    <select name="{$eduname}[eduid]" onchange="fillType(this.form['{$eduname}[degreeid]'], this.selectedIndex - 1);">
      {education_options selected=$edu.eduid}
    </select>
    <input type="hidden" name="edu_{$eduid}_tmp" value="{$edu.degreeid}" />
    <select name="{$eduname}[degreeid]">
      <option value=""></option>
    </select>
  </td>
</tr>
<tr class="edu_{$eduid} {$class}">
  <td>
    <span class="titre">Domaine de formation&nbsp;:</span>
  </td>
  <td>
    <select name="{$eduname}[fieldid]">
      {foreach from=$edu_fields item=field}
      <option value="{$field.id}" {if $field.id eq $edu.fieldid}selected="selected"{/if}>{$field.field}</option>
      {/foreach}
    </select>
  </td>
</tr>
<tr class="edu_{$eduid} {$class}">
  <td>
    <span class="titre">Année d'obtention du diplôme&nbsp;:</span>
  </td>
  <td>
    <input type="text" {if $edu.error}class="error"{/if} name="{$eduname}[grad_year]"
    value="{$edu.grad_year}" size="4" maxlength="4" />
    <small>(par exemple&nbsp;: 2008)</small>
  </td>
</tr>
<tr class="edu_{$eduid} {$class}">
  <td>
    <span class="titre">Intitulé de la formation&nbsp;:</span>
  </td>
  <td>
    <input type="text" name="{$eduname}[program]" value="{$edu.program}" size="30" maxlength="255" />
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
