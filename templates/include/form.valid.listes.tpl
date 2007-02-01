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


<tr class="pair">
  <td class="titre">Liste&nbsp;:</td>
  <td>{$valid->liste}@polytechnique.org</td>
</tr>
<tr class="pair">
  <td class="titre">Desc&nbsp;:</td>
  <td style="border: 1px dotted inherit">
    {$valid->desc}
  </td>
</tr>
<tr class="pair">
  <td class="titre">Propriétés&nbsp;:</td>
  <td>
    <table cellpadding='2' cellspacing='0'>
      <tr>
        <td>visibilité:</td>
        <td>{if $valid->advertise}publique{else}privée{/if}</td>
      </tr>
      <tr>
        <td>diffusion:</td>
        <td>{if $valid->modlevel eq 2}modérée{elseif $valid->modlevel}restreinte{else}libre{/if}</td>
      </tr>
      <tr>
        <td>inscription:</td>
        <td>{if $valid->inslevel}modérée{else}libre{/if}</td>
      </tr>
    </table>
  </td>
</tr>
<tr class="pair">
  <td class="titre">Gestionnaires&nbsp;:</td>
  <td>
    {foreach from=$valid->owners item=o}
    <a href="profile/{$o}" class="popup2">{$o}</a>
    {/foreach}
  </td>
</tr>
<tr class="pair">
  <td class="titre">Membres&nbsp;:</td>
  <td>
    {foreach from=$valid->members item=o}
    <a href="profile/{$o}" class="popup2">{$o}</a>
    {/foreach}
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
