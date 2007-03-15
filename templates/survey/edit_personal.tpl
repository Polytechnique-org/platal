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

    {include file='survey/edit_question.tpl' disable_question=true}
    <tr>
      <td class="titre">Demande</td>
      <td>
        <input type="checkbox" name="survey_question[promo]" value="1" id="survey_question[promo]"{if $survey_current.promo} checked="checked"{/if}/><label for="survey_question[promo]">Demander la promotion</label><br/>
        <input type="checkbox" name="survey_question[name]" value="1" id="survey_question[name]"{if $survey_current.name} checked="checked"{/if}/><label for="survey_question[name]">Demander le nom et le pr&#233;nom</label>
    <tr>
      <td colspan='2'>
        Cette question d&#233;truit totalement ou en partie l'anonymat du sondage : un message sera affich&#233; pour pr&#233;venir les utilisateurs,
        ils pourront accepter ou non de transmettre ces informations.
      </td>
    </tr>

{* vim:set et sw=2 sts=2 sws=2: *}

