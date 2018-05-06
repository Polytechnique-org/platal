{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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
Le groupe {$asso->nom} compte {if t($full_count)}{$full_count}{elseif $plset_total_count}{$plset_total_count}{else}{$plset_count}{/if} membres{if !t($full_count)}.{else}, dont {if $plset_total_count}{$plset_total_count}{else}{$plset_count}{/if} dans le {$plset_mods.$plset_mod|lower}.{/if}
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
    <a href="{$platal->ns}directory/sync">
      {icon name=arrow_refresh title="Importer dans l'annuaire à partir de toutes les listes"}
      Importer dans l'annuaire à partir de toutes les listes
    </a>
  </li>
  <li>
    <a href="{$platal->ns}directory/unact">
      {icon name=group_gear title="Lister les membres du groupe sans compte actif"}
      Lister les membres du groupe sans compte actif
    </a>
  </li>
  <li>
    <a href="{$platal->ns}directory/awact">
      {icon name=group_gear title="Lister les membres du groupe ayant un compte en attente d'activation"}
      Lister les membres du groupe ayant un compte en attente d'activation
    </a>
  </li>
  {if $asso->has_ml}
  <li>
    <a href="{$platal->ns}admin/annuaire">
      {icon name=wand title="Synchroniser"}
      Synchroniser annuaire et listes de diffusion
    </a>
  </li>
  {/if}
  <li>
    <a href="{$platal->ns}former_users">
      {icon name=status_offline title="Anciens membres du groupe"}
      Anciens membres du groupe
    </a>
  </li>
  <li>
    {include file="include/csv.tpl" url="`$platal->ns`annuaire/csv/`$asso->diminutif`.csv"}
  </li>
  <li>
    <a href="{$platal->ns}annuaire/csv-ax/{$asso->diminutif}.csv">
      {icon name="page_excel" title="Télécharger au format Excel"}
      Télécharger la liste pour un import sur le site de l'AX
    </a>
    <small>(encodage&nbsp;: UTF-8, séparateur&nbsp;: point-virgule)</small>
  </li>
  <li>
    <a href="{$platal->ns}annuaire/vcard/photos/{$asso->diminutif}.vcf">
      {icon name=vcard title="Carte de visite"}
      Ajouter les membres à ton carnet d'adresse
    </a>
    (<a href="{$platal->ns}annuaire/vcard/{$asso->diminutif}.vcf">sans les photos</a>)
  </li>
  {/if}
</ul>

<p class="center">
[<a href="{$platal->ns}{$plset_base}/{$plset_mod}{$plset_args}" {if !$only_admin}class="erreur"{/if}>tous les membres</a>]
[<a href="{$platal->ns}{$plset_base}/admins/{$plset_mod}{$plset_args}" {if $only_admin}class="erreur"{/if}>animateurs</a>]
</p>

{include core=plset.tpl}

{if $lostUsers}
<p class="smaller">
  {icon name=error}&nbsp;Un camarade signalé par ce symbole n'a plus d'adresse de redirection et ne peut donc
  plus être contacté via son adresse polytechnique.org. Si tu connais sa nouvelle adresse, tu peux nous la communiquer en
  cliquant sur le symbole.
</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
