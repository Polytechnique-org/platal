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
 ***************************************************************************
        $Id: nb_by_promo.tpl,v 1.5 2004-10-24 14:41:17 x2000habouzit Exp $
 ***************************************************************************}


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
{dynamic}
  {foreach item=nb10 key=lustre from=$nbs}
  <tr>
    <th>{$lustre}-</th>
    {foreach item=nb from=$nb10}
    <td class="center">
      {if $nb}
      <a href="{"stats/stats_promo.php?promo=`$nb.promo`"|url}">{$nb.nb}</a>
      {else}
      -
      {/if}
    </td>
    {/foreach}
  </tr>
  {/foreach}
{/dynamic}
</table>

<h1>
  Inscrits par promo en (%)
</h1>

<img src="/stats/graph-promo2.png" />

{* vim:set et sw=2 sts=2 sws=2: *}
