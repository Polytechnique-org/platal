{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************}


{dynamic}

<h1>
  Marketing volontaire
</h1>

<h2>Adresses neuves</h2>

<table class="bicol" cellpadding="3" summary="Adresses neuves">
  <tr>
    <th>Camarade concerné</th>
    <th>Adresse email</th>
    <th>Camarade "informateur"</th>
    <th>Dernière adresse connue</th>
    <th>Lui écrire ?</th>
  </tr>
  {foreach from=$neuves item=it}
  <tr class="{cycle values="pair,impair"}">
    <td>{$it.nom} {$it.prenom} (X{$it.promo})</td>
    <td>{$it.email}</td>
    <td>{$it.snom} {$it.sprenom} (X{$it.spromo})</td>
    <td>{$it.last_known_email}</td>
    <td>
      {if $it.mailperso}
      <a
      href="utilisateurs_marketing.php?xmat={$it.dest}&amp;sender={$it.expe}&amp;from={$it.sprenom}%20{$it.snom}%20<{$it.forlife}&#64;polytechnique.org>&amp;mail={$it.email}&amp;submit=Mailer">Perso</a>
      {else}
      <a href="utilisateurs_marketing.php?xmat={$it.dest}&amp;sender={$it.expe}&amp;from=Equipe%20Polytechnique.org%20<register&#64;polytechnique.org>&amp;mail={$it.email}&amp;submit=Mailer">Equipe</a>
      {/if}
      <a href="{$smarty.server.PHP_SELF}?done={$it.id}">Fait !</a>
      <a href="{$smarty.server.PHP_SELF}?del={$it.id}">Del</a>
    </td>
  </tr>
  {/foreach}
</table>

<br />
<br />

<h2>Adresses déjà utilisées</h2>

<table class="bicol" cellpadding="3" summary="Adresses déjà utilisées">
  <tr>
    <th>Camarade concerné</th>
    <th>Adresse email</th>
    <th>Camarade "informateur"</th>
    <th>inscrit?</th>
  </tr>
  {foreach from=$used item=it}
  <tr class="{cycle values="pair,impair"}">
    <td>{$it.nom} {$it.prenom} (X{$it.promo})</td>
    <td>{$it.email}</td>
    <td>{$it.snom} {$it.sprenom} (X{$it.spromo})</td>
    <td>{if $it.inscrit}OUI{else}NON{/if}</td>
  </tr>
  {/foreach}
</table>

<p>
{$rate.j} inscrits sur {$rate.i} sollicités, soit {$rate.rate}% de succès.
</p>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
