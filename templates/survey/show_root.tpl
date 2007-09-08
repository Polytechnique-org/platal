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

<h1>Sondage : {$survey.title}</h1>
<form action="survey/vote{if $survey_votemode}/{$survey.id}{/if}" method='post'>
<table style="width: 100%">
  <tr>
    <td>
    <table class="bicol">
      <tr class="pair">
        <td colspan="2">{$survey.description}</td>
      </tr>
      <tr>
        <td class="titre">Fin du sondage :</td>
        <td>{$survey.end|date_format:"%x"}</td>
      </tr>
      <tr>
        <td class="titre">Type de sondage :</td>
        <td>{$survey_modes[$survey.mode]}</td>
      </tr>
      {if $survey.mode != Survey::MODE_ALL} 
      <tr>
        <td class="titre">Promotions :</td> 
        <td>
          {if $survey.promos eq "#"} 
          erreur 
          {elseif $survey.promos eq ""} 
          aucune restriction 
          {else} 
          {$survey.promos} 
          {/if}
        </td>
      </tr>
      {/if}
      {if $survey_warning}
      <tr class="pair">
        <td colspan="2">{$survey_warning}</td>
      </tr>
      {/if}
    </table>
    {if $survey_resultmode}
    <p class="smaller">{$survey.votes} personnes ont r&#233;pondu &#224; ce sondage.<br />
      R&#233;cup&#233;rer <a href="survey/result/{$survey.id}/csv">l'ensemble des r&#233;sultats</a> au format csv
    </p>
    {/if}
    </td>
    {if $survey_editmode && !$survey.valid}
      {assign var="survey_editallmode" value=true}
    {/if}
    {if $survey_editmode}
    <td class="smaller" style="width: 30%">
      <a href='survey/edit/question/root'>{icon name=page_edit} Modifier la description</a>
      {if $survey_editallmode}<br /><a href='survey/edit/add/0'>{icon name=add} Ajouter une question au d&#233;but</a>{/if}
    </td>
    {/if}
  </tr>
  {if is_array($survey.questions)}
  {foreach from=$survey.questions item=squestion}
  <tr>
    <td>
      {include file='survey/show_question.tpl' squestion=$squestion}
    </td>
    {if $survey_editallmode}
    <td class="smaller" style="width: 30%; vertical-align: middle">
      <a href='survey/edit/question/{$squestion.id}'>{icon name=page_edit} Modifier cette question</a><br />
      <a href='survey/edit/del/{$squestion.id}'>{icon name=delete} Supprimer cette question</a><br />
      <a href='survey/edit/add/{$squestion.id+1}'>{icon name=add} Ajouter une question apr&#232;s</a>
    </td>
    {/if}
  </tr>
  {/foreach}
  {/if}
</table>
<p class="center">
  {if $survey_editmode}
  <a href='survey/edit/valid'>
    {icon name=tick} 
    {if $survey_updatemode}Enregistrer les modifications{else}Proposer ce sondage{/if}
  </a> |
  <a href='survey/edit/cancel'>
    {icon name=cross} Annuler
    {if $survey_updatemode}les modifications{else}totalement la cr&#233;ation de ce sondage{/if}
  </a>
  {elseif $survey_adminmode}
  {if !$survey.valid}<a href="survey/admin/valid/{$survey.id}">Valider ce sondage</a> | {/if}
  <a href="survey/admin/edit/{$survey.id}">{icon name=tick} Modifier ce sondage</a> |
  <a href="survey/admin/del/{$survey.id}">{icon name=cross} Supprimer ce sondage</a> |
  <a href="survey/admin">Retour</a>
  {elseif $survey_votemode}
  <input type='submit' name='survey_submit' value='Voter'/>
  <input type='submit' name='survey_cancel' value='Annuler'/>
  {else}
  <a href="survey">Retour</a>
  {/if}
</p>
</form>

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
