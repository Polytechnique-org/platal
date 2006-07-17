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


<h1>Administration Polytechnique.org</h1>

{foreach from=$index key=h1 item=index2}
<table class="bicol" cellpadding="3" summary="Système">
  <tr><th>{$h1}</th></tr>
  {foreach from=$index2 key=h2 item=index3}
  <tr class="{cycle values="impair,pair"}">
    <td>
      <strong>{$h2} :</strong>&nbsp;&nbsp;
      {foreach from=$index3 item=ln name=ln}
      <a href="{$ln.url}">{$ln.txt}</a>
      {if !$smarty.foreach.ln.last}
      &nbsp;&nbsp;|&nbsp;&nbsp;
      {/if}
      {/foreach}
    </td>
  </tr>
  {/foreach}
</table>
<br />
{/foreach}

{* vim:set et sw=2 sts=2 sws=2: *}
