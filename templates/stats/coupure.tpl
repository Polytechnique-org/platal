{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<h1>Coupures de Polytechnique.org</h1>

{if $cp}
<table class="bicol" summary="Ruptures de service">
  <tr>
    <th colspan="2">détails de l'interruption de service</th>
  </tr>
  <tr class="pair">
    <td class="titre">début</td>
    <td>{$cp.debut|date_format:"%x %X"}</td>
  </tr>
  <tr class="pair">
    <td class="titre">durée</td>
    <td>{$cp.duree}</td>
  </tr>
  <tr class="impair">
    <td class="titre">résumé</td>
    <td>{$cp.resume}</td>
  </tr>
  <tr class="impair">
    <td class="titre">services</td>
    <td>
      {$cp.lg_services}
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">description </td>
    <td>{$cp.description}</td>
  </tr>
</table>

<p class="center">
<a href="stats/coupures">retour à la liste</a>
</p>

{else}

<p>
  Tu trouveras ici les interruptions de service de Polytechnique.org qui ont été
  constatées <strong>durant les trois dernières semaines</strong>, ou qui sont prévues dans le futur.
  Il est à noter qu'à ce jour la quasi-totalité des coupures proviennent
  de défaillances du réseau de l'École, où nos serveurs sont hébergés (rupture de la
  connexion internet de l'École, problème électrique&hellip;).
</p>
<p>
  Pour avoir les détails d'une interruption particulière il te suffit de cliquer dessus.
</p>

<table class="bicol" summary="Détail de la coupure">
  <tr>
    <th>date</th>
    <th>résumé</th>
    <th>services affectés</th>
  </tr>
{if $coupures->total()}
{iterate item=cp from=$coupures}
  <tr class="{cycle values="impair,pair"}">
    <td>
      <span class="smaller">
        {$cp.debut|date_format}
      </span>
    </td>
    <td>
      <span class="smaller">
        <a href="stats/coupures/{$cp.id}">{$cp.resume}</a>
      </span>
    </td>
    <td>
      <span class="smaller">
        {$cp.services}
      </span>
    </td>
  </tr>
{/iterate}
{else}
  <tr>
    <td colspan="3">
      Il n'y a eu aucune coupure de service récemment
    </td>
  </tr>
{/if}
</table>

<h1>Problèmes avec les autres fournisseurs de services</h1>

<p>
  La liste ci-dessous indique quels sont les fournisseurs d'emails vers lesquels nous avons
  actuellement des problèmes de tranmission.
</p>

<table class="bicol">
  <tr><th>Liste des disfonctionnements</th></tr>
  {iterate from=$mxs item=mx}
  <tr class="{cycle values="impair,pair"}">
    <td><strong>{$mx.host}</strong><div class="explication">{$mx.text}</div></td>
  </tr>
  {/iterate}
</table>

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
