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


<h1>Relance</h1>

{foreach from=$sent item=l}
<p>{$l}</p>
{/foreach}

<form action="marketing/relance" method="post">
  <table class="bicol" summary="liste des inscriptions non confirmées">
    <tr>
      <th>Date</th>
      <th>Promo</th> 
      <th>Nom</th>
      <th>Dernière relance</th>
      <th>&nbsp;</th>
    </tr>
    {iterate from=$relance item=it}
    <tr class="{cycle values="pair,impair"}">
      <td class="center">{$it.date}</td>
      <td class="center">{$it.promo}</td>
      <td>{$it.nom} {$it.prenom}</td>
      <td class="center">
        {if $it.relance eq "0000-00-00"}Jamais{else}{$it.relance}{/if}
      </td>
      <td class="center">
        <input type="checkbox" name="relance[{$it.uid}]" value="1" />
      </td>
    </tr>
    {/iterate}
  </table>

  <p>
  {$relance->total()} Polytechniciens n'ont pas effectué jusqu'au bout leur inscription.
  </p>
  <div class="center">
    <input type="submit" name="relancer" value="Relancer" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
