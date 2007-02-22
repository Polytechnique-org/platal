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
  Envoi exceptionnel de l'AX
</h1>

<h2>Ton statut</h2>

{if $axs}
<p>
Tu es actuellement inscrit aux envois exceptionnels de l'AX (pour choisir le format HTML ou texte, rends toi sur la page <a href='prefs'>des préférences</a>)
</p>
<div class='center'>
  [<a href='ax/out'>{icon name=delete} me désinscrire des envois exceptionnels</a>]
</div>
{else}
<p>
Tu n'es actuellement pas inscrit aux envois exceptionnels de l'AX.
</p>
<div class='center'>
  [<a href='ax/in'>{icon name=add} m'inscrire</a>]
</div>
{/if}

<h2>Les archives</h2>

<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
  <tr>
    <th>date</th>
    <th>titre</th>
  </tr>
  {if $ax_rights && !$new}
  <tr class="pair">
    <td colspan="2" class="center">
      <a href="ax/edit">{icon name=page_edit} Proposer un nouveau mail</a>
    </td>
  </tr>
  {elseif $ax_rights && $new}
  <tr class="pair">
    <td><a href="ax/edit">{icon name=page_edit} Editer la demande</a></td>
    <td>
      {if $new->title()}
      <a href="ax/show/{$new->id()}"><strong>{$new->title(true)}</strong></a>
      {/if}
    </td>
  </tr>
  {/if}
  {foreach item=al from=$ax_list}
  <tr class="{cycle values="impair,pair"}">
    <td>{$al.date|date_format}</td>
    <td>
      <a href="ax/show/{$al.id}">{$al.titre}</a>
    </td>
  </tr>
  {/foreach}
</table>

{if $ax_rights}
<p>Il y a actuellement {$count} inscrits aux envois</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
