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

<h1>{$asso.nom} : Gestion des télépaiements </h1>

<p class="descr">
Voici la liste des paiements en ligne possible pour le groupe {$asso.nom}
</p>

{foreach from=$titres item=p}

<hr />
<h2>
<a href="https://www.polytechnique.org/paiement/?ref={$p.id}">{$p.text}</a>
</h2>

{if $trans[$p.id]}
<table cellpadding="0" cellspacing="0" class='bicol'>
  <tr>
    <th colspan="3">{$p.text} : détails pour les administrateurs</th>
  </tr>
  <tr>
    {foreach from=$trans[$p.id] item=p}
    <td>{$p.date|date_format}</td>
    <td>
      <a href="https://www.polytechnique.org/fiche.php?user={$p.alias}">{$p.nom} {$p.prenom} (X{$promo})</a>
      (<a href="mailto:{$p.alias}@polytechnique.org">mail</a>)
    </td>
    <td>{$p.montant}</td>
    {/foreach}
  </tr>
</table>
{/if}

{foreachelse}

<p class="descr">
<em>Pas de télépaiement en cours ...</em>
</p>

{/foreach}

{* vim:set et sw=2 sts=2 sws=2: *}
