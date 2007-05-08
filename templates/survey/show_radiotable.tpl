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

<table class="bicol">
  <tr class="pair">
    <td></td>
  {foreach from=$squestion.choices item=schoice}
    <td>{$schoice}</td>
  {/foreach}
  </tr>
{foreach from=$squestion.subquestions item=ssubq key=ssqid}
  <tr class="{cycle values="impair,pair"}">
    <td>{$ssubq}</td>
  {assign var=sid value=$survey.id}
  {assign var=sqid value=$squestion.id}
  {if $survey_resultmode}
    {foreach from=$squestion.choices item=schoice key=value}
    <td>
      {$squestion.result.$ssqid.$value*100/$survey.votes|string_format:"%.1f"}% ({$squestion.result.$ssqid.$value} votes)
    </td>
    {/foreach}
  {else}
    {foreach from=$squestion.choices item=schoice key=value}
    <td>
      <label><input type="radio" name="survey{$sid}[{$sqid}][{$ssqid}]" value="{$value}" {if !$survey_votemode}disabled="disabled" {/if}/></label>
    </td>
    {/foreach}
  {/if}
{/foreach}
</table>

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
