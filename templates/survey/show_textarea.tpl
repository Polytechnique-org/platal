{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

{if $survey_resultmode}
  {if count($squestion.result) == 0}
  Aucune réponse n'a été donnée.
  {else}
    {if count($squestion.result) ==1}
    Une réponse donnée par une d{else}Quelques réponses données par l{/if}es personnes sondées&nbsp;:
    <ul>
    {assign var=nbhidden value=0}
    {foreach item=sresult from=$squestion.result}
      {if trim($result.answer)}
      <li>{$sresult.answer}</li>
      {else}
      {assign var=nbhidden value=$nbhidden+1}
      {/if}
    {/foreach}
    {if $nbhidden > 0}
      <li><em>{$nbhidden} réponse{if $nbhidden > 1}s{/if} vide{if $nbhidden > 1}s{/if}</em></li>
    {/if}
    </ul>
  {/if}
{else}
  <textarea name="survey{$survey.id}[{$squestion.id}]" rows="5" cols="60" {if !$survey_votemode}disabled="disabled"{/if}></textarea>
{/if}

{* vim:set et sw=2 sts=2 ts=8 fenc=utf-8: *}
