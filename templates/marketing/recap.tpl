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
  Liste des sollicités inscrits récemment
</h1>


<table class="bicol" summary="liste des sollicités inscrits">
  <tr>
    <th>Date Ins.</th>
    <th>Par</th>
    <th>Nom</th>
    <th>inscription</th>
  </tr>
  {iterate from=$recents item=it}
  <tr class="{cycle values="pair,impair"}">
    <td>{$it.success|date_format}</td>
    <td>{$it.sender|lower}</td>
    <td>
      <a href="{rel}/fiche.php?user={$it.forlife}" class="popup2">{$it.prenom} {$it.nom}</a>
      (<a href="promo.php?promo={$it.promo}">{$it.promo}</a>)
    </td>
    <td>{$it.date_succes|date_format}</td>
  </tr>
  {/iterate}
</table>
<p>
{$recents->total()} Polytechniciens ont été sollicités et se sont inscrits.
</p>

<h1>
  Liste des sollicités non inscrits
</h1>

<table class="bicol" summary="liste des sollicités non inscrits">
  <tr>
    <th>Dernier envoi</th>
    <th>Par</th>
    <th>Nom</th>
    <th>Nb mails testés</th>
  </tr>
  {iterate from=$notsub item=x}
  <tr class="{cycle values="pair,impair"}">
    <td class="center">
      {if $x.last != '0000-00-00'}{$x.last|date_format:"%d %b %y"}{else}-{/if}
    </td>
    <td>{$x.sender|lower}</td>
    <td>
      {$x.promo} {$x.nom} {$x.prenom}
    </td>
    <td>
      {$x.nb}
    </td>
  </tr>
  {/iterate}
</table>

<p>
{$notsub->total()} Polytechniciens ont été sollicités et ne se sont toujours pas inscrits.
</p>


{* vim:set et sw=2 sts=2 sws=2: *}
