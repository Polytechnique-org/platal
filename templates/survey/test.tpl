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

{if $survey.type == 'racine'}
<h1>Sondage : {$survey.question}</h1>
  {if $survey.comment != ''}
    {$survey.comment}
  {/if}
  {if is_array($survey.children)}
    {foreach from=$survey.children item=child}
      {include file='survey/test.tpl' survey=$child recursive=true}
    {/foreach}
  {/if}

{else}
<div>
  <h2>{$survey.question}</h2>
  {if $survey.comment != ''}
    {$survey.comment}<br/>
  {/if}
  {if $survey.type == 'text' || $survey.type == 'num' }
  <input type="text" name="survey{$survey_id}_{$survey.id}" value="" {if $survey_mode eq 'edit'}disabled="disabled"{/if}/>
  {elseif $survey.type == 'radio'}
    {foreach from=$survey.choices item=choice}
  <input type="radio" name="survey{$survey_id}_{$survey.id}" value="{$choice}" id="{$choice}" {if $survey_mode eq 'edit'}disabled="disabled"{/if}/><label for="{$choice}">{$choice}</label>
    {/foreach}
  {/if}
  {if is_array($survey.children)}
  <div style="padding-left:20px">
    {foreach from=$survey.children item=child}
      {include file='survey/test.tpl' survey=$child recursive=true}
    {/foreach}
  </div>
  {/if}
</div>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
