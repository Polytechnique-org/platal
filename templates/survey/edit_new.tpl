{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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
    <tr>
      <td class="titre">Type</td>
      <td>
        <select name="survey_type" id="survey_type" onchange="Ajax.update_html('survey_form', 'survey/ajax/' + document.getElementById('survey_type').value); return false">
        {foreach from=$survey_types key='stype_v' item='stype_t'}
          <option value="{$stype_v}"{if $survey_type eq $stype_v} selected="selected"{/if}>{$stype_t}</option>
        {/foreach}
        </select>
      </td>
    </tr>
    {if $survey_type == "new"}
      {include file='survey/edit_question.tpl'}
    {else}
      {include file="survey/edit_$survey_type.tpl"}
    {/if}

{* vim:set et sw=2 sts=2 sws=2: *}

