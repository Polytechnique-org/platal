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

<h1>Marketing Polytechnique.org</h1>

<table class="bicol" cellpadding="3" summary="Système">
  <tr>
    <th>actions disponibles</th>
  </tr>
  <tr class="impair">
    <td>
      <a href="search?nonins=1">Chercher un non inscrit</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="marketing/promo">Marketing promo</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="marketing/volontaire">Adresses données par les inscrits</a>
    </td>
  </tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th colspan="4">Statistiques</th>
  </tr>
  <tr>
    <td class="titre"></td>
    <td style="text-align: right" class="titre">Vivants</td>
    <td style="text-align: right" class="titre">Inscrits</td>
    <td style="text-align: right" class="titre">Pourcentage</td>
  </tr>
  {foreach from=$statistics item=data}
  <tr>
    <td>{$data.description}</td>
    <td style="text-align: right">{$data.alive}</td>
    <td style="text-align: right">{$data.registered}</td>
    <td style="text-align: right">{$data.rate}</td>
  </tr>
  {/foreach}
</table>

<p>
nombre d'<a href="marketing/this_week">inscrits ces 7 derniers jours</a>&nbsp;: {$registrations.week}<br />
nombre d'<a href="marketing/relance">inscriptions en cours</a> (2ème phase non terminée)&nbsp;: {$registrations.pending}
</p>

<table class="bicol">
  <tr>
    <th colspan="4">Marketings</th>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td style="text-align: right" class="titre">Non abouti</td>
    <td style="text-align: right" class="titre">Abouti</td>
    <td style="text-align: right" class="titre">Total</td>
  </tr>
  <tr>
    <td>Personnel</td>
    <td style="text-align: right">{$marketings.noPerso}</td>
    <td rowspan="3" style="vertical-align: middle; text-align: right">{$marketings.ok}</td>
    <td rowspan="3" style="vertical-align: middle; text-align: right">{$marketings.ok+$marketings.noPerso+$marketings.noXorg+$marketings.noAX}</td>
  </tr>
  <tr>
    <td>Par Polytechnique.org</td>
    <td style="text-align: right">{$marketings.noXorg}</td>
  </tr>
  <tr>
    <td>Par l'AX</td>
    <td style="text-align: right">{$marketings.noAX}</td>
  </tr>
  <tr>
    <td>Cette semaine</td>
    <td style="text-align: right">{$marketings.noWeek}</td>
    <td style="text-align: right">{$marketings.okWeek}</td>
    <td style="text-align: right">{$marketings.okWeek+$marketings.noWeek}</td>
  </tr>
  <tr>
    <td class="titre">Total</td>
    <td style="text-align: right">{$marketings.noPerso+$marketings.noXorg+$marketings.noAX}</td>
    <td style="text-align: right">{$marketings.ok}</td>
    <td style="text-align: right">{$marketings.ok+$marketings.noPerso+$marketings.noXorg+$marketings.noAX}</td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

