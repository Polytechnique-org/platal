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
  Marketing Polytechnique.org
</h1>

<table class="bicol" cellpadding="3" summary="Système">
  <tr>
    <th>actions disponibles</th>
  </tr>
  <tr class="impair">
    <td>
      <a href="{rel}/search.php?nonins=1">Chercher un non inscrit</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="promo.php">Marketing promo</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="volontaire.php">Addresses données par les inscrits</a>
    </td>
  </tr>
</table>

<br />

<p>
Nombre d'X vivants d'après notre base de données : {$stats.vivants}<br />
Nombre d'X vivants inscrits à Polytechnique.org : {$stats.inscrits}<br />
Soit un pourcentage d'inscrits de : {$stats.ins_rate} %<br />
</p>

<p>
Parmi ceux-ci :<br />
Nombre d'X vivants depuis 1972 d'après notre base de données : {$stats.vivants72}<br />
Nombre d'X vivants depuis 1972 inscrits à Polytechnique.org : {$stats.inscrits72}<br />
Soit un pourcentage d'inscrits de : {$stats.ins72_rate} % <br />
</p>

<p>
Nombre de Polytechniciennes vivantes : {$stats.vivantes}<br />
Nombre de Polytechniciennes vivantes et inscrites : {$stats.inscrites} <br />
Soit un pourcentage d'inscrites de : {$stats.inse_rate} % <br />
</p>

<p>
Nombre d'<a href="this_week.php">inscrits depuis le début de la semaine</a> : {$nbInsSem}<br />
Nombre d'<a href="relance.php">inscriptions en cours</a> (2ème phase non terminée) : {$nbInsEnCours} <br />
Nombre d'envois marketing effectués n'ayant pas abouti : {$nbInsMarket}<br />
Nombre d'envois marketing ayant abouti cette semaine : {$nbInsMarkOK}
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
