{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

{foreach from=$backtraces key=bt_name item=trace}
<div class="backtrace">
  <h1>
    {if $trace->error}<span style="color: #f00">{/if}
    Exécution de {$bt_name}&nbsp;: {$trace->traces|@count} actions en {$trace->totaltime|string_format:"%.3f"}s (hover-me pour la trace)
    {if $trace->error}</span>{/if}
  </h1>
  <div class="hide">
{foreach item=query from=$trace->traces}
{if $query.data}
{assign var=cols value=$query.data[0]|@count}
{else}
{assign var=cols value=1}
{/if}
<table class="bicol" style="width: 75%; font-size: smaller; margin-left:2px; margin-top: 3px;">
  <tr class="impair">
    <td colspan="{$cols}">
      <strong>ACTION:</strong>
      <pre style="padding: 0; margin: 0;">{$query.action}</pre>
      <br/>
    </td>
  </tr>
  {if $query.error}
  <tr>
    <td colspan="{$cols}">
      <strong style="color: #f00">ERROR:</strong><br />
      {$query.error|nl2br}
    </td>
  </tr>
  {else}
  <tr>
    <td colspan="{$cols}">
      <strong>INFO:</strong><br />
      {$query.rows} ligne{if $query.rows > 1}s{/if} en {$query.exectime|string_format:"%.3f"}s
    </td>
  </tr>
  {/if}
{if $query.data}
  <tr>
    {foreach key=key item=item from=$query.data[0]}
    <th style="font-size: smaller">{$key}</th>
    {/foreach}
  </tr>
  {foreach item=data_row from=$query.data}
  <tr class="impair">
    {foreach item=item from=$data_row}
    <td class="center" style="font-size: smaller">{$item}</td>
    {/foreach}
  </tr>
  {/foreach}
{/if}
</table>
{/foreach}
</div>
</div>
{/foreach}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
