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

<h1>Sondages</h1>

{* Survey::MODE_ALL equals 0. *}
{assign var=SurveyMODE_ALL value=0}
{if $survey_current->total() > 0 || $smarty.session.auth}
<table class="bicol">
  <tr>
    <th colspan="3">
      Sondages en cours
    </th>
  </tr>
  {iterate item=s from=$survey_current}
  {if $smarty.session.auth || $s.mode == $SurveyMODE_ALL}
  <tr class="{cycle name=cs_cycle values="impair,pair"}">
    <td class="half" style="clear: both">
      <a href="survey/vote/{$s.id}">{$s.title}</a>
      {if $s.uid eq $smarty.session.user->id() || hasPerm('admin')}
      (<a href="survey/result/{$s.id}">résultats partiels</a>)
      {/if}
    </td>
    <td>
      {$s.end|date_format:"%x"}
    </td>
    <td>
      {$survey_modes[$s.mode]}
    </td>
  </tr>
    {assign var="has_cs" value="true"}
  {/if}
  {/iterate}
  {if hasPerm('user')}
  <tr class="impair">
    <td colspan="3" style="text-align: right">
      {if $smarty.session.auth}<a href="survey/edit/new">{icon name=page_edit} Proposer un sondage</a>{/if}
    </td>
  </tr>
  {/if}
</table>
{/if}

<br />

<table class="bicol">
  <tr>
    <th colspan="3">
      Anciens sondages
    </th>
  </tr>
  {iterate item=s from=$survey_old}
    {if $smarty.session.auth || $s.mode == $SurveyMODE_ALL}
  <tr class="{cycle name=os_cycle values="impair,pair"}">
    <td>
      <a href="survey/result/{$s.id}">
        {$s.title}
      </a>
    </td>
    <td>
      {$s.end|date_format:"%x"}
    </td>
    <td>
      {$survey_modes[$s.mode]}
    </td>
  </tr>
      {assign var="has_os" value="true"}
    {/if}
  {/iterate}
  {if !$has_os}
  <tr>
    <td class="half">Aucun ancien sondage</td>
  </tr>
  {/if}
</table>

{* vim:set et sw=2 sts=2 ts=8 fenc=utf-8: *}
