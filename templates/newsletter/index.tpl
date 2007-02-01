{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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
  Lettre de Polytechnique.org
</h1>


<p class="center">
  <a href="nl/submit">{icon name=page_edit value="Proposer un article"} Proposer un article pour la lettre mensuelle</a>
</p>

<h2>Ton statut</h2>

{if $nls}
<p>
Tu es actuellement inscrit à la lettre mensuelle de Polytechnique.org (pour choisir le format HTML ou texte, rends toi sur la page <a href='prefs'>des préférences</a>)
</p>
<div class='center'>
  [<a href='nl/out'>{icon name=delete} me désinscrire de la lettre mensuelle</a>]
</div>
{else}
<p>
Tu n'es actuellement pas inscrit à la lettre mensuelle de Polytechnique.org.
</p>
<div class='center'>
  [<a href='nl/in'>{icon name=add} m'inscrire à la lettre mensuelle</a>]
</div>
{/if}

<h2>Les archives</h2>

<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
  <tr>
    <th>date</th>
    <th>titre</th>
  </tr>
  {foreach item=nl from=$nl_list}
  <tr class="{cycle values="impair,pair"}">
    <td>{$nl.date|date_format}</td>
    <td>
      <a href="nl/show/{$nl.id}">{$nl.titre}</a>
    </td>
  </tr>
  {/foreach}
</table>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
