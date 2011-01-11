{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

{if $active->total() > 0}
<table class="bicol" id="surveyList">
  <tr>
    <th>
      Sondages en cours
    </th>
    {if hasPerm('admin')}
    <th></th>
    {/if}
  </tr>
  {iterate from=$active item=survey}
  <tr class="{cycle values="impair,pair"}">
    <td>
      <a href="survey/vote/{$survey->shortname}">{$survey->title}</a>
    </td>
    {if hasPerm('admin')}
    <td style="text-align: right">
      <a href="survey/edit/{$survey->shortname}">{icon name=page_edit}</a>
    </td>
    {/if}
  </tr>
  {/iterate}
</table>
{/if}
<br />
<div class="center">
  <a href="survey/edit">{icon name=page_edit} Proposer un nouveau sondage</a>
</div>

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
