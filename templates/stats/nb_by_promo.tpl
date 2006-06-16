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


<h1>
  Inscrits par promo
</h1>

<p>
Voici le nombre d'inscrits par promo :
</p>

<table class="bicol" cellpadding="3" cellspacing="0" summary="Statistiques">
  <tr>
    <th></th>
    <th>0</th><th>1</th><th>2</th><th>3</th><th>4</th>
    <th>5</th><th>6</th><th>7</th><th>8</th><th>9</th>
  </tr>
  {foreach item=nb10 key=lustre from=$nbs}
  <tr>
    <th>{$lustre}-</th>
    {foreach item=nb from=$nb10}
    <td class="center">
      {if $nb && $nb.promo eq $smarty.request.promo}
      <span class='erreur'>{$nb.nb}</span>
      {elseif $nb}
      <a href="?promo={$nb.promo}">{$nb.nb}</a>
      {else}
      -
      {/if}
    </td>
    {/foreach}
  </tr>
  {/foreach}
</table>

{if $smarty.request.promo}

<p class='center'>
[<a href="{$smarty.server.PHP_SELF}">répartition des inscrits par promo</a>]
</p>

<h1>Courbe d'inscription de la promo {$smarty.request.promo}</h1>

<div class="center">
  <img src="{"stats/graph_promo.php"|url}?promo={$smarty.request.promo}" alt=" [ INSCRITS ] " />
</div>

{else}

<h1>Inscrits par promo en (%)</h1>

<div class="center">
  <img src="{"stats/graph_by_promo.php"|url}" alt="[graphe du nombre d'inscrits par promo]" />
</div>

{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
