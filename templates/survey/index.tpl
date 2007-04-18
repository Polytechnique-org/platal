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

<h1>Sondages</h1>

<table class="bicol">
  <tr>
    <th>
      Sondages en cours
    </th>
  </tr>
  <tr> 
    <td>
      {if $survey_current->total() eq 0}
      Aucun sondage en cours
      {/if}
      <a style="display: block; float: right;" href="survey/edit/new">{icon name=page_edit} Proposer un sondage</a></td> 
  </tr> 
  {iterate item=s from=$survey_current}
  {if $smarty.session.auth || $s.mode == Survey::MODE_ALL}
  <tr class="{cycle values="impair,pair"}">
    <td class="half">
      &bull;
      <a href="survey/vote/{$s.id}">
        {$s.title} [{$s.end|date_format:"%x"} - {$survey_modes[$s.mode]}]
      </a>
    </td>
  </tr>
  {/if}
  {/iterate}
</table>

<br />

<table class="bicol">
  <tr>
    <th>
      Anciens sondages
    </th>
  </tr>
  {iterate item=s from=$survey_old}
    {if $smarty.session.auth || $s.mode == Survey::MODE_ALL}
  <tr class="{cycle values="impair,pair"}">
    <td class="half">
      &bull;
      <a href="survey/result/{$s.id}">
        {$s.title} [{$s.end|date_format:"%x"} - {$survey_modes[$s.mode]}]
      </a>
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

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
