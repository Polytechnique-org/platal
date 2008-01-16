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


<h1>Inscrits des 7 derniers jours</h1>

<p>
{$ins->total()} Polytechniciens se sont inscrits ces 7 derniers jours !
</p>

<div class="right">
  [<a href="marketing/this_week?sort=date_ins">par date</a>]
  [<a href="marketing/this_week?sort=promo">par promo</a>]
</div>

<table class="tinybicol">
  <tr>
    <th>Inscription</th>
    <th>Promo</th>
    <th>Nom</th>
  </tr>
{iterate item=in from=$ins}
  <tr class="{cycle values="impair,pair"}">
    <td class="center">{$in.date_ins|date_format:"%x %X"}</td>
    <td class="center">
      <a href="marketing/promo/{$in.promo}">{$in.promo}</a>
    </td>
    <td>
      <a href="profile/{$in.forlife}" class="popup2">
        {$in.nom} {$in.prenom}</a>
    </td>
  </tr>
{/iterate}
</table>

<div class="right">
  [<a href="marketing/this_week/per_date">par date</a>]
  [<a href="marketing/this_week/per_promo">par promo</a>]
</div>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
