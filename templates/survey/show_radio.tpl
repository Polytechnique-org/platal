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

{if $survey_resultmode}
  <ul>
  {foreach item=sresult from=$squestion.result}
    <li>{$squestion.choices[$sresult.answer]}&nbsp;: {$sresult.count*100/$survey.votes|string_format:"%.1f"}% ({$sresult.count} votes)</li>
  {/foreach}
  </ul>
{else}
  {assign var=sid value=$survey.id}
  {assign var=sqid value=$squestion.id}
  {if $survey_votemode}
    {html_radios name="survey$sid[$sqid]" options=$squestion.choices separator='<br/>'}
  {else}
    {html_radios name="survey$sid[$sqid]" options=$squestion.choices separator='<br/>' disabled='disabled'}
  {/if}
{/if}

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
