{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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


{include file="listes/header_listes.tpl" on=archives}


{if $archs}
<h1>Archives de la liste {$platal->argv[1]}</h1>

<h2>Triés par fils de discussion</h2>

<table class="tinybicol" cellspacing="0" cellpadding="0">
  <tr>
    <th>Année</th>
    <th colspan="6">
      Mois
    </th>
  </tr>
  {foreach from=$archs item=m key=y}
  <tr class="center {if $y is odd}pair{else}impair{/if}">
    <td class="titre" rowspan="2">{$y}</td>
    {foreach from=$range item=i}
    <td>
      {if $m[$i]}
      <a href="{$platal->ns}lists/archives/{$platal->argv[1]}?rep={$y}/{$i|string_format:"%02u"}&amp;file=threads.html">{"0000-$i-01"|date_format:"%B"}</a>
      {else}
      &nbsp;
      {/if}
    </td>
    {if $i eq 6}</tr><tr class="center {if $y is odd}pair{else}impair{/if}">{/if}
    {/foreach}
  </tr>
  {/foreach}
</table>

<h2>Triés par date</h2>

<table class="tinybicol" cellspacing="0" cellpadding="0">
  <tr>
    <th>Année</th>
    <th colspan="6">
      Mois
    </th>
  </tr>
  {foreach from=$archs item=m key=y}
  <tr class="center {if $y is odd}pair{else}impair{/if}">
    <td class="titre" rowspan="2">{$y}</td>
    {foreach from=$range item=i}
    <td>
      {if $m[$i]}
      <a href="{$platal->ns}lists/archives/{$platal->argv[1]}?rep={$y}/{$i|string_format:"%02u"}&amp;file=dates.html">{"0000-$i-01"|date_format:"%B"}</a>
      {else}
      &nbsp;
      {/if}
    </td>
    {if $i eq 6}</tr><tr class="center {if $y is odd}pair{else}impair{/if}">{/if}
    {/foreach}
  </tr>
  {/foreach}
</table>
{elseif $archives}
{tidy}
{include file="$archives"}
{/tidy}
{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
