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

<h1>{$asso.nom} : Annuaire du groupe </h1>

<p class="descr">
Le groupe {$asso.nom} compte {$nb_tot} membres.
</p>

<p class="descr">
Les membres extérieurs du groupe sont intégrés à cette liste, et repérés par l'inscription 'extérieur' dans la colonne promotion.
</p>

<p class="descr">
Tu peux également :
</p>
<ul class="descr">
  <li>
    <a href="{$platal->ns}annuaire/vcard/photos/{$asso.diminutif}.vcf">
      {icon name=vcard title="Carte de visite"} 
      Ajouter les membres à ton carnet d'adresse
    </a>
    (<a href="{$platal->ns}annuaire/vcard/{$asso.diminutif}.vcf">sans les photos</a>)
  </li>
  {if $admin}
  <li>
    <a href="{$platal->ns}member/new">
      {icon name=add title="Ajouter un membre"} 
      Ajouter un membre
    </a>
  </li>
  <li>
    <a href="{$platal->ns}admin/annuaire">
      {icon name=wand title="Synchroniser"} 
      Synchroniser annuaire et Listes de diffusion
    </a>
  </li>
  {/if}
</ul>

<p class="center">
[<a href="{$platal->ns}annuaire?order={$smarty.request.order}" {if !$request_group}class="erreur"{/if}>tout</a>]
{foreach from=$alphabet item=c}
[<a href="{$platal->ns}annuaire?{$group}={$c}&amp;order={$smarty.request.order}"{if $request_group eq $c} class="erreur"{/if}>{$c}</a>]
{/foreach}
</p>

<table summary="membres du groupe" class="tinybicol">
  <tr>
    <th>
      <a href="{$platal->ns}annuaire?order=alpha{if $sort neq "alpha_inv"}_inv{/if}{if $request_group and $group eq 'initiale'}&amp;initiale={$request_group}{/if}">
      {if $sort eq 'alpha'}
        <img src="{$platal->baseurl}images/dn.png" alt="" title="Tri croissant" />
      {elseif $sort eq 'alpha_inv'}
        <img src="{$platal->baseurl}images/up.png" alt="" title="Tri décroissant" />
      {/if}
      Prénom NOM 
      </a>
    </th>
    <th>
      <a href="{$platal->ns}annuaire?order=promo{if $sort eq "promo"}_inv{/if}{if $request_group and $group eq 'promo'}&amp;promo={$request_group}{/if}">
      {if $sort eq 'promo_inv'}
        <img src="{$platal->baseurl}images/dn.png" alt="" title="Tri croissant" />
      {elseif $sort eq 'promo'}
        <img src="{$platal->baseurl}images/up.png" alt="" title="Tri décroissant" />
      {/if}
        Promo
      </a>
    </th>
    <th>Infos</th>
    {if $admin}
    <th>Actions</th>
    {/if}
  </tr>
  {iterate from=$ann item=m}
  <tr>
    <td>{if $m.admin}<strong>{/if}{if $m.femme}&bull;{/if}{$m.prenom} {$m.nom|strtoupper}{if $m.admin}</strong>{/if}</td>
    <td>{if $m.admin}<strong>{/if}{$m.promo}{if $m.admin}</strong>{/if}</td>
    <td class="center">
      {if $m.x}
      <a href="https://www.polytechnique.org/profile/{$m.email}" class="popup2">{icon name=user_suit title="fiche"}</a>
      <a href="https://www.polytechnique.org/vcard/{$m.email}.vcf">{icon name=vcard title="[vcard]"}</a>
      <a href="mailto:{$m.email}@polytechnique.org">{icon name=email title="mail"}</a>
      {else}
      <a href="mailto:{$m.email}">{icon name=email title="mail"}</a>
      {/if}
    </td>
    {if $admin}
    <td class="center">
      <a href="{$platal->ns}member/{if $m.x}{$m.email}{else}{$m.uid}{/if}">{icon name=user_edit title="Edition du profil"}</a>
      <a href="{$platal->ns}member/del/{if $m.x}{$m.email}{else}{$m.uid}{/if}">{icon name=delete title="Supprimer de l'annuaire"}</a>
    </td>
    {/if}
  </tr>
  {/iterate}
</table>

<p class="descr" style="text-align: center">
{foreach from=$links item=ofs key=txt}
<a href="{$platal->ns}annuaire?offset={$ofs}&amp;initiale={$smarty.request.initiale}"{if $smarty.request.offset eq $ofs} class="erreur"{/if}>{$txt}</a>
{/foreach}
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
