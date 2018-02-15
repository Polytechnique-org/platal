{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<h1>Inscrits par promo</h1>

<p>
Voici le nombre d'inscrits par promotion&nbsp;:
</p>

<table class="bicol" cellpadding="3" cellspacing="0" summary="Statistiques">
  {foreach from=$nbs key=cycle item=nb_cycle}
  <tr>
    <th>{$cycle}</th>
    <th>0</th><th>1</th><th>2</th><th>3</th><th>4</th>
    <th>5</th><th>6</th><th>7</th><th>8</th><th>9</th>
  </tr>
  {foreach item=nb10 key=lustre from=$nb_cycle}
  <tr>
    <th>{$lustre}</th>
    {foreach item=nb from=$nb10}
    <td class="center">
      {if $nb && $nb.promo eq $promo}
      <span class='erreur'>{$nb.nb}</span>
      {elseif $nb}
      <a href="stats/promos/{$nb.promo}">{$nb.nb}</a>
      {else}
      -
      {/if}
    </td>
    {/foreach}
  </tr>
  {/foreach}
  {/foreach}
</table>

{if $promo}

<p class='center'>
[<a href="stats/promos">répartition des inscrits par promo</a>]
</p>

<h1>Courbe d'inscription de la promo {$promo}</h1>

<div class="center">
  <img src="stats/graph/{$promo}" alt=" [ INSCRITS ] " />
</div>

{else}

<h1>Inscrits par promo en (%)</h1>

<div class="center">
  <img src="stats/graph/{#Profile::DEGREE_X#}" alt="[graphe du nombre d'inscrits par promotion pour les X]" />
</div>

<div class="center">
  <img src="stats/graph/{#Profile::DEGREE_M#}" alt="[graphe du nombre d'inscrits par promotion pour les masters]" />
</div>

<div class="center">
  <img src="stats/graph/{#Profile::DEGREE_D#}" alt="[graphe du nombre d'inscrits par promotion pour les docteurs]" />
</div>

{/if}


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
