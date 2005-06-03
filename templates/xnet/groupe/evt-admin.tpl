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

<h1>{$asso.nom} : <a href='evenements.php'>Evénements</a> </h1>

{if $moments}
<p class="center">
{iterate from=$moments item=m}
[<a href="{$smarty.server.PHP_SELF}?eid={$m.eid}&item_id={$m.item_id}" {if $smarty.request.item_id eq $m.item_id || ($m.item_id eq 1 && !$smarty.request.item_id)}class="erreur"{/if}>{$m.titre}</a>]
{/iterate}
</p>
{/if}

<p class="descr">
L'événement {$evt.intitule} {if $evt.titre} - {$evt.titre}{/if} comptera {$evt.nb_tot} personne{if $evt.nb_tot > 1}s{/if}.
</p>

<p class="center">
[<a href="{$url_page}" {if !$smarty.request.initiale}class="erreur"{/if}>tout</a>]
{foreach from=$alphabet item=c}
[<a href="{$url_page}&initiale={$c}"{if $smarty.request.initiale eq $c} class="erreur"{/if}>{$c}</a>]
{/foreach}
</p>

<table summary="participants a l'evenement" class="tiny">
  <tr>
    <th>Prénom NOM</th>
    <th>Promo</th>
    <th>Info</th>
    <th>Nombre</th>
  </tr>
  {iterate from=$ann item=m}
  <tr style="background:#d0c198;">
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
    <td>
      {$m.nb}
    </td>
  </tr>
  {/iterate}
</table>

<p class="descr">
{foreach from=$links item=ofs key=txt}
<a href="?offset={$ofs}&amp;initiale={$smarty.request.initiale}"{if $smarty.request.offset eq $ofs} class="erreur"{/if}>{$txt}</a>
{/foreach}
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
