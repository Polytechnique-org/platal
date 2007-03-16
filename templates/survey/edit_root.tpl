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
      <td class="titre">Titre</td>
      <td><input type="text" name="survey_question[question]" size="50" maxlength="200" value="{$survey_current.question}"/></td>
    </tr>
    <tr>
      <td class="titre">Commentaire</td>
      <td><textarea name="survey_question[comment]" rows="5" cols="60">{$survey_current.comment}</textarea></td>
    </tr>
    <tr>
      <td class="titre">Date de fin</td>
      <td><input type="text" name="survey_question[end]" size="50" maxlength="200" value="{if $survey_current.end eq '#'}#{else}{$survey_current.end|date_format:"%d/%m/%Y"}{/if}"/></td>
    </tr>
    <tr>
      <td class="titre">Promotions</td>
      <td><input type="text" name="survey_question[promos]" size="50" maxlength="200" value="{$survey_current.promos}"/></td>
    </tr>

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
