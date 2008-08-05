{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<h1>{$asso.nom}&nbsp;: Annuaire du groupe </h1>

<p class="descr">
Le groupe {$asso.nom} compte {$nb_tot} membres&nbsp;:
</p>

<ul class="descr">
  {if $is_admin}
  <li>
    <a href="{$platal->ns}member/new">
      {icon name=add title="Ajouter un membre"} 
      Ajouter un membre
    </a>
  </li>
  <li>
    <a href="{$platal->ns}admin/annuaire">
      {icon name=wand title="Synchroniser"} 
      Synchroniser annuaire et listes de diffusion
    </a>
  </li>
  {/if}
  <li>
    <a href="{$platal->ns}annuaire/csv/{$asso.diminutif}.csv">
      {icon name=page_excel title="Fichier Excel"} 
      Obtenir au format Excel
    </a>
  </li>
  <li>
    <a href="{$platal->ns}annuaire/vcard/photos/{$asso.diminutif}.vcf">
      {icon name=vcard title="Carte de visite"} 
      Ajouter les membres à ton carnet d'adresse
    </a>
    (<a href="{$platal->ns}annuaire/vcard/{$asso.diminutif}.vcf">sans les photos</a>)
  </li>
</ul>

{if $plset_base}
{include file="core/plset.tpl"}
{else}

<p class="center">
[<a href="{$platal->ns}annuaire?order={$smarty.request.order}" {if !$only_admin}class="erreur"{/if}>tous les membres</a>]
[<a href="{$platal->ns}annuaire?order={$smarty.request.order}&amp;admin=1" {if $only_admin}class="erreur"{/if}>animateurs</a>]<br/>
{foreach from=$alphabet item=c}
{if $c}
[<a href="{$platal->ns}annuaire?{$group}={$c}&amp;order={$smarty.request.order}{if $only_admin}&amp;admin=1{/if}"{if $request_group eq $c} class="erreur"{/if}>{$c}</a>]
{/if}
{/foreach}
</p>

<table summary="membres du groupe" class="bicol">
  <tr>
    <th>
      <a href="{$platal->ns}annuaire?order=alpha{if $sort neq "alpha_inv"}_inv{/if}{if $request_group and $group eq 'initiale'}&amp;initiale={$request_group}{/if}{if $only_admin}&amp;admin=1{/if}">
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
    <th colspan="2">Infos</th>
    {if $is_admin}
    <th>Actions</th>
    {/if}
  </tr>
  {iterate from=$ann item=m}
  <tr>
    <td>
      {if $m.admin}<strong>{/if}
      {if $m.inscrit}
      <a href="https://www.polytechnique.org/profile/{$m.email}" class="popup2">
      {elseif $m.x}
      <a href="https://www.polytechnique.org/marketing/public/{$m.uid}">
      {/if}
      {if $m.femme}&bull;{/if}{if $m.prenom || $m.nom}{$m.prenom} {$m.nom|strtoupper}{else}{$m.email}{/if}
      {if $m.x}</a>{/if} 
      {if $m.admin}</strong>{/if}
      {if $m.inscrit && !$m.actif}
      <a href="https://www.polytechnique.org/marketing/broken/{$m.email}">{icon name=error title="Recherche d'email"}</a>
      {assign var=broken value=true}
      {/if}</td>
    <td>{if $m.admin}<strong>{/if}{$m.promo}{if $m.admin}</strong>{/if}</td>
    {if $m.comm}
    <td>{$m.comm}</td>
    {/if}
    <td class="right" {if !$m.comm}colspan="2"{/if}>
      {if $m.inscrit}
      <a href="https://www.polytechnique.org/vcard/{$m.email}.vcf">{icon name=vcard title="[vcard]"}</a>
      <a href="mailto:{$m.email}@polytechnique.org">{icon name=email title="email"}</a>
      {else}
      <a href="mailto:{$m.email}">{icon name=email title="email"}</a>
      {/if}
    </td>
    {if $is_admin}
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
<a href="{$platal->ns}annuaire?offset={$ofs}&amp;initiale={$smarty.request.initiale}&amp;order={$sort}"{if $smarty.request.offset eq $ofs} class="erreur"{/if}>{$txt}</a>
{/foreach}
</p>

{if $broken}
<p class="smaller">
  {icon name=error}&nbsp;Un camarade signalé par ce symbole n'a plus d'adresse de redirection et ne peut donc
  plus être contacté via son adresse polytechnique.org. Si tu connais sa nouvelle adresse, tu peux nous la communiquer en
  cliquant sur le symbole.
</p>
{/if}

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
