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
      Sondages en attente de validation
    </th>
  </tr>
  {iterate item=s from=$survey_waiting}
  <tr class="{cycle values="impair,pair"}">
    <td class="half">
      &bull;
      <a href="survey/admin/edit/{$s.survey_id}">
        {$s.title} ({$s.end|date_format:"%x"})
      </a>
    </td>
  </tr>
  {assign var="has_ws" value="true"}
  {/iterate}
  {if !$has_ws}
  <tr>
    <td class="half">Aucun sondage en attente de validation</td>
  </tr>
  {/if}
</table>

<br />

<table class="bicol">
  <tr>
    <th>
      Sondages en cours
    </th>
  </tr>
  {iterate item=s from=$survey_current}
  <tr class="{cycle values="impair,pair"}">
    <td class="half">
      &bull;
      <a href="survey/admin/edit/{$s.survey_id}">
        {$s.title} ({$s.end|date_format:"%x"})
      </a>
    </td>
  </tr>
  {assign var="has_cs" value="true"}
  {/iterate}
  {if !$has_cs}
  <tr>
    <td class="half">Aucun sondage en cours</td>
  </tr>
  {/if}
</table>

<br />

<table class="bicol">
  <tr>
    <th>
      Anciens sondages
    </th>
  </tr>
  {iterate item=s from=$survey_old}
  <tr class="{cycle values="impair,pair"}">
    <td class="half">
      &bull;
      <a href="survey/admin/edit/{$s.survey_id}">
        {$s.title} ({$s.end|date_format:"%x"})
      </a>
    </td>
  </tr>
  {assign var="has_os" value="true"}
  {/iterate}
  {if !$has_os}
  <tr>
    <td class="half">Aucun ancien sondage</td>
  </tr>
  {/if}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
