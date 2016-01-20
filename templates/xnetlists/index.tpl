{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

{if $smarty.get.del_alias}

<p class="error">Es-tu sûr de vouloir supprimer l'alias {$smarty.get.del_alias}&nbsp;?</p>
<form action='{$platal->ns}lists' method="post">
  {xsrf_token_field}
  <div class="center">
    <input type='submit' value="Oui, je suis sûr" />
    <input type='hidden' name='del_alias' value="{$smarty.get.del_alias}" />
  </div>
</form>
<p>[<a href='{$platal->ns}lists'>retour à la page des listes</a>]</p>

{else}

<h1>{$asso->nom}&nbsp;: Listes de diffusion</h1>

<h2>Listes de diffusion du groupe {$asso->nom}&nbsp;:</h2>

<p class="descr">
Une liste dont <strong>la diffusion</strong> est modérée est une liste dont les emails sont validés
par les administrateurs avant d'être transmis aux membres de la liste.  Une liste dont
<strong>l'inscription</strong> est modérée est une liste pour laquelle l'abonnement est soumis à
l'accord préalable des responsables du groupe.
</p>
<p class="descr">
La dernière colonne du tableau t'indique si tu es inscrit{if $smarty.session.user->gender}e{/if} ou non à
la liste. Dans le premier cas, une croix rouge te permet de te désabonner. Dans le second cas, une
croix verte te permet de t'inscrire, après accord des responsables si l'inscription est modérée.
</p>

<table cellpadding="0" cellspacing="0" class='large'>
  <tr>
    <th colspan="2">
      <a href="{$platal->ns}lists/list/{if $order_by eq 'list' && $order eq 'asc'}desc{else}asc{/if}">
        {if $order_by eq 'list'}
        {if $order eq 'desc'}
          <img src="{$globals->baseurl}/images/up.png" alt="" title="Tri décroissant" />
        {else}
          <img src="{$globals->baseurl}/images/dn.png" alt="" title="Tri croissant" />
        {/if}
        {/if}
        Liste
      </a>
    </th>
    <th>
      <a href="{$platal->ns}lists/desc/{if $order_by eq 'desc' && $order eq 'asc'}desc{else}asc{/if}">
        {if $order_by eq 'desc'}
        {if $order eq 'desc'}
          <img src="{$globals->baseurl}/images/up.png" alt="" title="Tri décroissant" />
        {else}
          <img src="{$globals->baseurl}/images/dn.png" alt="" title="Tri croissant" />
        {/if}
        {/if}
        Description
      </a>
    </th>
    <th>Diffusion</th>
    <th>Inscription</th>
    <th>
      <a href="{$platal->ns}lists/nbsub/{if $order_by eq 'nbsub' && $order eq 'asc'}desc{else}asc{/if}">
        {if $order_by eq 'nbsub'}
        {if $order eq 'desc'}
          <img src="{$globals->baseurl}/images/up.png" alt="" title="Tri décroissant" />
        {else}
          <img src="{$globals->baseurl}/images/dn.png" alt="" title="Tri croissant" />
        {/if}
        {/if}
        Nb
      </a>
    </th>
    <th>&nbsp;</th>
  </tr>
  {foreach from=$listes item=l}
  <tr>
    <td class='center'>
      <a href="mailto:{$l.list}@{$asso->mail_domain}">{icon name=email title="email"}</a>
    </td>
    <td>
      {if $l.own}
      {icon name=wrench title="Modérateur"}
      {/if}
      {if $l.priv}
      {icon name=weather_cloudy title="Liste privée"}
      {/if}
      <a href='{$platal->ns}lists/members/{$l.list}'>{$l.list}</a>
    </td>
    <td>{$l.desc|smarty:nodefaults}</td>
    <td class='center'>
      {if $l.diff eq 2}modérée{elseif $l.diff eq 1}restreinte{else}libre{/if}
    </td>
    <td class='center'>{if $l.ins}modérée{else}libre{/if}</td>
    <td align='right'>{$l.nbsub}</td>
    <td align='center'>
      {if $l.sub eq 2}
      <a href="{$platal->ns}lists?del={$l.list}&amp;token={xsrf_token}">{icon name=cross title="me désinscrire"}</a>
      {elseif $l.sub eq 1}
      {icon name=flag_orange title='inscription en attente de modération'}
      {else}
      <a href="{$platal->ns}lists?add={$l.list}&amp;token={xsrf_token}">{icon name=add title="m'inscrire"}</a>
      {/if}
    </td>
  </tr>
  {foreachelse}
  <tr><td colspan='7'>Pas de listes pour ce groupe</td></tr>
  {/foreach}
  {if $may_update}
  <tr><td colspan="7" class="center">
    <a href="{$platal->ns}lists/create">
      {icon name=add title="Créer une liste"} Créer une nouvelle liste
    </a>
  </td></tr>
  {/if}
</table>

<p class="descr">
{icon name=wrench title="Modérateur"} tu es {if $smarty.session.user->gender}modératrice{else}moderateur{/if} sur cette liste.<br />
{icon name=weather_cloudy title="Liste privée"} cette liste est invisible aux non-membres de la liste. S'en désabonner
t'empêcherait de t'y réabonner par la suite sans l'aide d'un administrateur.
</p>

<h2>Voici les alias existants pour le groupe {$asso->nom}&nbsp;:</h2>

<table cellspacing="0" cellpadding="0" class='large'>
  <tr>
    <th{if $may_update} colspan='3'{/if}>Alias</th>
  </tr>
  {if $aliases|@count}
  {foreach from=$aliases item=alias}
  <tr>
    {if $may_update}
    <td class="center"><a href='mailto:{$alias}'>{icon name=email title="email"}</a></td>
    <td><a href="{$platal->ns}alias/admin/{$alias}">{$alias}</a></td>
    <td class="center"><a href="{$platal->ns}lists?del_alias={$alias}">{icon name=delete title='supprimer'}</a></td>
    {else}
    <td><a href='mailto:{$alias}'>{icon name=email title="email"} {$alias}</a></td>
    {/if}
  </tr>
  {/foreach}
  {else}
  <tr>
    <td{if $may_update} colspan='3'{/if}>Aucun alias pour ce groupe</td>
  </tr>
  {/if}
  {if $may_update}
  <tr><td colspan="3" class="center">
    <a href="{$platal->ns}alias/create">
      {icon name=add title="Créer une liste"} Créer un nouvel alias
    </a>
  </td></tr>
  {/if}
</table>

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
