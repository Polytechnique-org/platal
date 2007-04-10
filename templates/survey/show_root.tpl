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
<form action="./survey/vote{if $survey_votemode}/{$survey.id}{/if}" method='post'>
{if $survey.description != ''}
  {$survey.description}
{/if}
<br/>Fin du sondage :
{if $survey.end eq "#"}
  erreur
{else}
  {$survey.end|date_format:"%x"}
{/if}
<br/>Type de sondage :
{$survey_modes[$survey.mode]}
{if $survey.mode != Survey::MODE_ALL}
  <br/>R&#233;serv&#233; aux promotions :
  {if $survey.promos eq "#"}
    erreur
  {elseif $survey.promos eq ""}
    aucune restriction
  {else}
    {$survey.promos}
  {/if}
{/if}
{if $survey_warning neq ''}
  <br/>{$survey_warning}
{/if}
<br/>
{if $survey_editmode}
  {assign var="survey_rooteditmode" value=true}
  {if $survey.valid}
    {assign var="survey_editmode" value=false}
  {/if}
{/if}
{if $survey_rooteditmode}<a href='./survey/edit/question/root'>Modifier la racine</a>{/if}
{if $survey_editmode} | <a href='./survey/edit/add/0'>Ajouter une question au d&#233;but</a>{/if}
{if is_array($survey.questions)}
  {foreach from=$survey.questions item=squestion}
    {include file='survey/show_question.tpl' squestion=$squestion}
    {if $survey_editmode}
      <br/>
      <a href='./survey/edit/question/{$squestion.id}'>Modifier cette question</a> |
      <a href='./survey/edit/del/{$squestion.id}'>Supprimer cette question</a> |
      <a href='./survey/edit/add/{$squestion.id+1}'>Ajouter une question apr&#232;s</a>
    {/if}
    <br/>
  {/foreach}
{/if}
{if $survey_rooteditmode}
<br/>
<a href='./survey/edit/valid'>{if $survey_updatemode}Enregistrer les modifications{else}Proposer ce sondage{/if}</a> |
<a href='./survey/edit/cancel'>Annuler {if $survey_updatemode}les modifications{else}totalement la cr&#233;ation de ce sondage{/if}</a>
{elseif $survey_adminmode}
<br/>
{if !$survey.valid}<a href="./survey/admin/valid/{$survey.id}">Valider ce sondage</a> | {/if}
<a href="./survey/admin/edit/{$survey.id}">Modifier ce sondage</a> |
<a href="./survey/admin/del/{$survey.id}">Supprimer ce sondage</a> |
<a href="./survey/admin">Retour</a>
{elseif $survey_votemode}
<input type='submit' name='survey_submit' value='Voter'/>
<input type='submit' name='survey_cancel' value='Annuler'/>
{else}
<a href="./survey">Retour</a>
{/if}
</form>

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
