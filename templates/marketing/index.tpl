{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

<p>
nombre d'étudiants et anciens de l'X vivants d'après notre base de données&nbsp;: {$statistics.alive}<br />
nombre d'étudiants et anciens de l'X vivants inscrits à Polytechnique.org&nbsp;: {$statistics.registered}<br />
Soit un pourcentage d'inscrits de&nbsp;: {$statistics.registeredRate} %<br />
</p>

<p>
Parmi ceux-ci&nbsp;:<br />
nombre d'X vivants depuis la promo 1972 d'après notre base de données&nbsp;: {$statistics.alive72}<br />
nombre d'X vivants depuis la promo 1972 inscrits à Polytechnique.org&nbsp;: {$statistics.registered72}<br />
Soit un pourcentage d'inscrits de&nbsp;: {$statistics.registeredRate72} %<br />
</p>

<p>
nombre de étudiantes et anciennes de l'X vivantes&nbsp;: {$statistics.womenAlive}<br />
nombre de étudiantes et anciennes de l'X vivantes et inscrites&nbsp;: {$statistics.womenRegistered}<br />
Soit un pourcentage d'inscrites de&nbsp;: {$statistics.womenRegisteredRate} %<br />
</p>

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
    <td class="titre">Non abouti</td>
    <td class="titre">Abouti</td>
    <td class="titre">Total</td>
  </tr>
  <tr>
    <td>Personnel</td>
    <td>{$marketings.noPerso}</td>
    <td rowspan="3" style="vertical-align: middle">{$marketings.ok}</td>
    <td rowspan="3" style="vertical-align: middle">{$marketings.ok+$marketings.noPerso+$marketings.noXorg+$marketings.noAX}</td>
  </tr>
  <tr>
    <td>Par Polytechnique.org</td>
    <td>{$marketings.noXorg}</td>
  </tr>
  <tr>
    <td>Par l'AX</td>
    <td>{$marketings.noAX}</td>
  </tr>
  <tr>
    <td>Cette semaine</td>
    <td>{$marketings.noWeek}</td>
    <td>{$marketings.okWeek}</td>
    <td>{$marketings.okWeek+$marketings.noWeek}</td>
  </tr>
  <tr>
    <td class="titre">Total</td>
    <td>{$marketings.noPerso+$marketings.noXorg+$marketings.noAX}</td>
    <td>{$marketings.ok}</td>
    <td>{$marketings.ok+$marketings.noPerso+$marketings.noXorg+$marketings.noAX}</td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

