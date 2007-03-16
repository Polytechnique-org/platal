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
{if $survey.type == 'root'}
<h1>Sondage : {$survey.question}</h1>
  {if $survey.comment != ''}
    {$survey.comment}
  {/if}
<br/>Fin du sondage :
  {if $survey.end eq "#"}
    erreur
  {else}
    {$survey.end|date_format:"%x"}
  {/if}
<br/>R&#233;serv&#233; aux promotions :
  {if $survey.promos eq "#"}
    erreur
  {elseif $survey.promos eq ""}
    aucune restriction
  {else}
    {$survey.promos}
  {/if}
<br/>
<a href='./survey/edit/question/{$survey.id}'>Modifier la racine</a> |
<a href='./survey/edit/nested/{$survey.id}'>Ajouter une question au début</a>
  {if is_array($survey.children)}
    {foreach from=$survey.children item=child}
      {include file='survey/show_survey.tpl' survey=$child recursive=true}
    {/foreach}
  {/if}
<br/><br/>
<a href='./survey/edit/valid'>{if $survey_adminmode}Enregistrer les modifications{else}Proposer ce sondage{/if}</a> |
<a href='./survey/edit/cancel'>Annuler {if $survey_adminmode}les modifications{else}totalement la cr&#233;ation de ce sondage{/if}</a>

{else}
<div>
  <h2>{$survey.question}</h2>
  {if $survey.comment != ''}
    {$survey.comment}<br/>
  {/if}
  {assign var='survey_type' value=$survey.type}
  {include file="survey/show_$survey_type.tpl"}
  <br/>
  <a href='./survey/edit/question/{$survey.id}'>Modifier cette question</a> |
  <a href='./survey/edit/del/{$survey.id}'>Supprimer cette question</a> |
  {if is_array($survey.children)}
  <a href='./survey/edit/nested/{$survey.id}'>Ajouter une question imbriqu&#233;e</a>
  <div style="padding-left:20px">
    {foreach from=$survey.children item=child}
      {include file='survey/show_survey.tpl' survey=$child recursive=true}
    {/foreach}
  </div>
  {/if}
  <a href='./survey/edit/after/{$survey.id}'>Ajouter une question apr&#232;s</a>
</div>
{/if}

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
