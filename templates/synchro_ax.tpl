{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************}

<h1>
  Synchronisation depuis l'AX
</h1>

<table class="bicol" cellpadding="0" cellspacing="0">
  <tr>
    <th>champ</th>
    <th style='width:50%'>AX</th>
    <th style='width:50%'>x.org</th>
  </tr>
{foreach from=$ax item='val' key='i'}
  <tr class="{cycle values='pair,impair'}">
    <td>
    {$i}
    </td>
    <td>
    {if ($i neq 'adr') and ($i neq 'adr_pro')}
    {$val}
    {else}
    {foreach from=$val item='sval' key='j'}
      {include file='geoloc/address.tpl' address=$sval}
    {/foreach}{/if}
    </td>
    <td>
    {if ($i neq 'adr') and ($i neq 'adr_pro')}
    {$x[$i]}
    {else}
    {foreach from=$x[$i] item='sval' key='j'}
      {include file='geoloc/address.tpl' address=$sval}
    {/foreach}{/if}
    </td>
  </tr>
{/foreach}
</table>


{* vim:set et sw=2 sts=2 sws=2: *}
