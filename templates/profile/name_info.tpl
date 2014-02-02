{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

<h1>Les différents types de noms</h1>
<table class="bicol" style="margin-bottom: 1em" summary="Explications sur les différents types de noms">
  <tr>
    <th>Type</th>
    <th>Description</th>
    <th>Confidentialité&nbsp;&sup1;</th>
    <th>Prise en compte de la particule&nbsp;&sup2;</th>
  <tr>
  </tr>
  {iterate from=$types item=type}
  <tr>
    <td>{$type.name}</td>
    <td>{$type.explanations}</td>
    <td class="center">
      {if $type.public}
        {icon name="flag_green" title="site public"}
      {else}
        {icon name="flag_red" title="site privé"}
      {/if}
    </td>
    <td class="center">{if $type.has_particle}Oui{else}Non{/if}</td>
  </tr>
  {/iterate}
</table>

<h2>Légende :</h2>
<ul>
<li>&sup1;&nbsp;{icon name="flag_green" title="site public"} signifie que les données sont visibles
  sur la partie publique du site alors que {icon name="flag_red" title="site privé"} indique
  qu'elles sont cantonnées au site privé.</li>
<li>&sup2;&nbsp;Pour les types concernés, il faut cocher la case en bout de ligne si le nom débute
  par une particule, comme cela tu seras correctement classé dans l'ordre alphabétique.</li>
</ul>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
