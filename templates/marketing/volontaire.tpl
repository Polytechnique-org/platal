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

<h1>Marketing volontaire</h1>

<p>
Choix de la promo :
</p>
<p>
{foreach from=$promos item=p}
<a href="?promo={$p}">{$p}</a>
{cycle values=",,,,,,,,,,,,,,<br />"}
{/foreach}
</p>

{if $addr}

<p>[<a href="promo.php?promo={$smarty.get.promo}">Marketing promo pour la promo {$smarty.get.promo}</a>]</p>

{if $addr->total()}
<h2>Marketing volontaire</h2>
<table class="bicol" cellpadding="3" summary="Adresses déjà utilisées">
  <tr>
    <th>Camarade concerné</th>
    <th>Adresse email</th>
    <th>"informateur"</th>
  </tr>
  {iterate from=$addr item=it}
  <tr class="{cycle values="pair,impair"}">
    <td><a href="private.php?uid={$it.user_id}">{$it.nom} {$it.prenom}</a></td>
    <td>{$it.email}</td>
    <td>{$it.forlife}</td>
  </tr>
  {/iterate}
</table>
{else}
<p>
pas d'informations pour les gens de cette promo
</p>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
