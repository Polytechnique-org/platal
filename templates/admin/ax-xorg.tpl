{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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
  Vérifier notre table de correspondance entre l'annuaire de l'AX et le nôtre
</h1>

<h2>
  Liste des camarades qui n'apparaissent pas sous le même nom dans les deux annuaires
</h2>

<table class="bicol" summary="liste des absents de notre annuaire">
  <tr>
    <th>Promo</th>
    <th>Nom X.org</th>  <th>Prénom X.org</th>   <th>Mat X.org</th>
    <th>Nom AX</th>     <th>Prénom AX</th>      <th>Mat AX</th>
  </tr>
{foreach item=x from=$diffs}
  <tr class="{cycle values="impair,pair"}">
    <td>{$x.promo}</td>
    <td>{$x.nom}</td>   <td>{$x.prenom}</td>    <td>{$x.mat}</td>
    <td>{$x.nomax}</td> <td>{$x.prenomax}</td>  <td>{$x.matax}</td>
  </tr>
{/foreach}
</table>

<p>
  <strong>{$diffs|@count} camarades ont un état civil différent dans les 2 annuaires.</strong>
</p>

<br />
<br />

<h2>
  Liste des camarades de l'annuaire de l'AX qui manquent à notre annuaire
</h2>

<table class="bicol" summary="liste des absents de notre annuaire">
  <tr>
    <th>Promo</th>  <th>Nom</th>  <th>Prénom</th>
  </tr>
{foreach item=x from=$mank}
  <tr class="{cycle values="impair,pair"}">
    <td>{$x.promo}</td>
    <td>{$x.nom} {if $x.nom_patro neq $x.nom}({$c.nom_patro}){/if}</td>
    <td>{$x.prenom}</td>
  </tr>
{/foreach}
</table>
<p>
  <strong>{$mank|@count} camarades sont absents de notre annuaire.</strong>
</p>

<br />
<br />

<h2>
  Liste des camarades de notre annuaire qui ne sont pas dans l'annuaire de l'AX
</h2>

<table class="bicol" summary="liste des absents de l'AX">
  <tr>
    <th>Promo</th>        <th>Nom</th>        <th>Prénom</th>
  </tr>
{foreach item=x from=$plus}
  <tr class="{cycle values="impair,pair"}">
    <td>{$x.promo}</td>   <td>{$x.nom}</td>   <td>{$x.prenom}</td>
  </tr>
{/foreach}
</table>

<p>
  <strong>{$plus|@count} camarades sont absents de l'annuaire de l'AX.</strong>
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
