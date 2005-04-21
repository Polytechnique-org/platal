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

<h1>{$asso.nom} : Annuaire du groupe </h1>

<p class="descr">
Le groupe {$asso.nom} compte {$nb_tot} membres.
</p>

<p class="descr">
Les membres extérieurs du groupe sont intégrés à cette liste, et repérés par l'inscription 'extérieur' dans la colonne promotion.
</p>

{if $admin}
<p class="descr">
Fonctionnalités visibles uniquement par les administrateurs :
</p>
<ul class="descr">
  <li><a href="membresx-edit.php?new=x">Ajouter un membre X</a></li>
  <li><a href="membres-edit.php?new=ext">Ajouter un membre extérieur</a></li>
</ul>
{/if}

<p class="descr">
Choisis une initiale pour restreindre la liste aux membres dont le nom commence par cette lettre. Tu
peux aussi <a href='{$smarty.server.PHP_SELF}'>afficher toute la liste</a>.
</p>

<p class="center">
{foreach from=$alphabet item=c}
[<a href="?initiale={$c}">{$c}</a>]
{/foreach}
</p>



<table summary"membres du groupe" style="width: 100%">
  <tr>
    <th>prénom et nom</th>
    <th>promotion</th>
    <th>fiche X.org / carte / lui écrire</th>
    {if $admin}
    <th></th>
    <th>admin</th>
    <th>éditer un profil</th>
    <th>désinscrire</th>
    {/if}
  </tr>
  {iterate from=$ann item=m}
  <tr>
    <td>{if $m.femme}&bull;{/if}{$m.prenom} {$m.nom}</td>
    <td>{$m.promo}</td>
    <td>
      {if $m.x}
      <a href="https://www.polytechnique.org/fiche.php?user={$m.email}"><img src="{rel}/images/loupe.gif" alt="[fiche]" /></a>
      <a href="https://www.polytechnique.org/vcard.php/{$m.email}.vcf/?x={$m.email}"><img src="{rel}/images/vcard.png" alt="[vcard]" /></a>
      <a href="mailto:{$m.email}@polytechnique.org"><img src="{rel}/images/mail.png" alt="mail" /></a>
      {else}
      <a href="mailto:{$m.email}"><img src="{rel}/images/mail.png" alt="mail"></a>
      {/if}
    </td>
    {if $admin}
    <td></td>
    <td>{if $m.admin}&times;{else}&nbsp;{/if}</td>
    <td><a href="{if $m.x}membresx{else}membres{/if}-edit.php?edit={$m.email}"><img src="{rel}/images/profil.png" alt="Edition du profil" /></a></td>
    <td><a href="{if $m.x}membresx{else}membres{/if}-edit.php?del={$m.email}"><img src="{rel}/images/del.png" alt="Suppression de {$m.prenom} {$m.nom}" /></a></td>
    {/if}
  </tr>
  {/iterate}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
