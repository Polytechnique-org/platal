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

{include file='survey/edit_radio.tpl'}
    <tr>
      <td class="titre">Sous-questions</td>
      <td>
        {foreach from=$survey_current.subquestions key=value item=subquestion}
        <div id="subquestions_t{$value}">
          <input type="text" name="survey_question[subquestions][t{$value}]" size="50" maxlength="200" value="{$subquestion}" />
          <a href="javascript:removeField('subquestions', 't{$value}')">{icon name=delete title="Supprimer"}</a>
        </div>
        {/foreach}
        <div id="subquestions_last">
          <a href="javascript:newField('subquestions', 'last')">{icon name=add}</a>
        </div>
      </td>
    </tr>

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
