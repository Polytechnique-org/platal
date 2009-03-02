{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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

<h1>{$asso->nom}&nbsp;: Annuaire du groupe </h1>

<p class="descr">
Le groupe {$asso->nom} compte {$nb_tot} membres&nbsp;:
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
    <a href="{$platal->ns}annuaire/csv/{$asso->diminutif}.csv">
      {icon name=page_excel title="Fichier Excel"} 
      Obtenir au format Excel
    </a>
  </li>
  <li>
    <a href="{$platal->ns}annuaire/vcard/photos/{$asso->diminutif}.vcf">
      {icon name=vcard title="Carte de visite"} 
      Ajouter les membres à ton carnet d'adresse
    </a>
    (<a href="{$platal->ns}annuaire/vcard/{$asso->diminutif}.vcf">sans les photos</a>)
  </li>
</ul>

{if $plset_base}
{include core=plset.tpl}
{else}

<p class="center">
[<a href="{$platal->ns}annuaire?order={$order}" {if !$only_admin}class="erreur"{/if}>tous les membres</a>]
[<a href="{$platal->ns}annuaire?order={$order}&amp;admin=1" {if $only_admin}class="erreur"{/if}>animateurs</a>]<br/>
{*
 XXX: This code has been temporary dropped, waiting for a cleaner way to do that stuff
{foreach from=$alphabet item=c}
{if $c}
[<a href="{$platal->ns}annuaire?order={$order}&amp;admin={$only_admin}"{if $request_group eq $c} class="erreur"{/if}>{$c}</a>]
{/if}
{/foreach}
*}
</p>

<table summary="membres du groupe" class="bicol">
  <tr>
    <th>
      <a href="{$platal->ns}annuaire?order={if $order eq 'directory_name'}-{/if}directory_name&amp;admin={$only_admin}">
      {if $order eq 'directory_name'}
        <img src="{$platal->baseurl}images/dn.png" alt="" title="Tri croissant" />
      {elseif $order eq '-directory_name'}
        <img src="{$platal->baseurl}images/up.png" alt="" title="Tri décroissant" />
      {/if}
      Prénom NOM
      </a>
    </th>
    <th>
      <a href="{$platal->ns}annuaire?order={if $order eq 'promo'}-{/if}promo&amp;admin={$only_admin}">
      {if $order eq '-promo'}
        <img src="{$platal->baseurl}images/dn.png" alt="" title="Tri croissant" />
      {elseif $order eq 'promo'}
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
  {foreach from=$users item=user}
  <tr>
    <td>
      {profile user=$user promo=false}
    <td>
      {if $user->group_perms eq 'admin'}<strong>{/if}
      {$user->promo()}
      {if $user->group_perms eq 'admin'}</strong>{/if}
    </td>
    {if $user->group_comm}
    <td>{$user->group_comm}</td>
    {/if}
    <td class="right" {if !$user->group_comm}colspan="2"{/if}>
      {if $user->hasProfile()}
      <a href="https://www.polytechnique.org/vcard/{$user->login()}.vcf">{icon name=vcard title="[vcard]"}</a>
      {/if}
      <a href="mailto:{$user->bestEmail()}">{icon name=email title="email"}</a>
    </td>
    {if $is_admin}
    <td class="center">
      <a href="{$platal->ns}member/{$user->login()}">{icon name=user_edit title="Édition du profil"}</a>
      <a href="{$platal->ns}member/del/{$user->login()}">{icon name=delete title="Supprimer de l'annuaire"}</a>
    </td>
    {/if}
  </tr>
  {/foreach}
</table>

{if $pages gt 1}
<p class="descr" style="text-align: center">
{section name="links" loop=$pages}
{if $smarty.section.links.index eq $current}
<span class="erreur">{$smarty.section.links.iteration}</span>
{else}
{if $smarty.section.links.first}
<a href="{$platal->ns}annuaire?offset={$current-1}&amp;order={$order}&amp;admin={$only_admin}">précédente</a>
{/if}
<a href="{$platal->ns}annuaire?offset={$smarty.section.links.index}&amp;order={$order}&amp;admin={$only_admin}">{$smarty.section.links.iteration}</a>
{if $smarty.section.links.last}
<a href="{$platal->ns}annuaire?offset={$current+1}&amp;order={$order}&amp;admin={$only_admin}">suivante</a>
{/if}
{/if}
{/section}
</p>
{/if}

{if $broken}
<p class="smaller">
  {icon name=error}&nbsp;Un camarade signalé par ce symbole n'a plus d'adresse de redirection et ne peut donc
  plus être contacté via son adresse polytechnique.org. Si tu connais sa nouvelle adresse, tu peux nous la communiquer en
  cliquant sur le symbole.
</p>
{/if}

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
