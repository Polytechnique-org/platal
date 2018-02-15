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

<h1>{$asso->nom}&nbsp;: Accueil</h1>

<table cellpadding="0" cellspacing="0" class='tiny'>
  {if $asso->site}
  <tr>
    <td class="titre">
      Site Web&nbsp;:
    </td>
    <td><a href="{$asso->site}">{$asso->site}</a></td>
  </tr>
  {/if}

  {if $asso->resp || $asso->mail}
  <tr>
    <td class="titre">
      Contact&nbsp;:
    </td>
    <td>
      {if $asso->mail}
      {mailto address=$asso->mail text=$asso->resp|utf8_decode|default:"par email" encode=javascript}
      {else}
      {$asso->resp}
      {/if}
    </td>
  </tr>
  {/if}

  {if $asso->positions|count}
  <tr>
    <td class="titre">
      Bureau&nbsp;:
    </td>
    <td>
      {foreach from=$asso->positions item=position}
      <em>&bull;&nbsp;{$position.position}&nbsp;:</em> {profile user=$position.uid promo=true}<br />
      {/foreach}
    </td>
  </tr>
  {/if}

  {if $asso->forum}
  <tr>
    <td class="titre">
      Forum&nbsp;:
    </td>
    <td>
      <a href="{$platal->ns}forum">par le web</a>
      ou <a href="news://ssl.polytechnique.org/{$asso->forum}">par nntp</a>
    </td>
  </tr>
  {/if}

  {if $asso->phone}
  <tr>
    <td class="titre">Téléphone&nbsp;:</td>
    <td>{$asso->phone}</td>
  </tr>
  {/if}

  {if $asso->fax}
  <tr>
    <td class="titre">Fax&nbsp;:</td>
    <td>{$asso->fax}</td>
  </tr>
  {/if}

  {if $asso->address}
  <tr>
    <td class="titre">Adresse&nbsp;:</td>
    <td>{$asso->address}</td>
  </tr>
  {/if}

  {* Allow subscribing to promo group for X accounts if the year matches *}
  {if !$is_member && $is_logged && $asso->inscriptible && ($xnet_type != 'promotions' || (t($user_xpromo) && $asso->diminutif == $user_xpromo))}
  <tr>
    <td class="titre">
      M'inscrire&nbsp;:
    </td>
    <td>
      <a href="{if $asso->sub_url}{$asso->sub_url}{else}{$platal->ns}subscribe{/if}">m'inscrire</a>
    </td>
  </tr>
  {elseif $is_member}
  <tr>
    <td class="titre">
      Me désinscrire&nbsp;:
    </td>
    <td>
      <a href="{if $asso->unsub_url}{$asso->unsub_url}{else}{$platal->ns}unsubscribe{/if}">me désinscrire</a>
    </td>
  </tr>
  {/if}

  {if $asso->ax}
  <tr>
    <td class="titre center" colspan="2">
      groupe agréé par l'AX {if $asso->axDate}le {$asso->axDate}{/if}
    </td>
  </tr>
  {/if}

  {if $is_admin && $requests}
  <tr>
    <td class="titre center" colspan="2">
      <a href="{$platal->ns}subscribe/valid">{$requests} demande{if $requests gt 1}s{/if} d'inscription en attente</a>
    </td>
  </tr>
  {/if}
</table>

<br />

<div style="text-align: justify">
  {$asso->descr|miniwiki:title|smarty:nodefaults}
</div>

<br />

{if t($article_index) && $article_index->total()}
<table class="tinybicol">
  <tr>
    <th>
      {if $smarty.session.user->token}
      <a href='{$platal->ns}rss/{$smarty.session.hruid}/{$smarty.session.user->token}/rss.xml' style="display:block;float:right">
        {icon name=feed title='fil rss'}
      </a>
      {else}
      <a href='https://www.polytechnique.org/prefs/rss?referer=events'  style="display:block;float:right">
        {icon name=feed_add title='Activer mon fil rss'}
      </a>
      {/if}
      Sommaire des annonces du groupe
    </th>
  </tr>
  {iterate item=art from=$article_index}
  <tr>
    <td>&bull;
    {if $art.nonlu}
      <a href="{$platal->ns}#art{$art.id}"><strong>
    {else}
      <a href="{$platal->ns}?unread={$art.id}">
    {/if}
    {tidy}{$art.titre}{/tidy}
    {if $art.nonlu}</strong>{/if}</a>
    </td>
  </tr>
  {/iterate}
  {if $is_admin}
  <tr class="pair">
    <td class="center">
      <a href="{$platal->ns}announce/new">{icon name=add} Ajouter une annonce</a>
    </td>
  </tr>
  {/if}
</table>

<br />

{if $articles->total()}
<div>
{iterate item=art from=$articles}
{include file="xnetgrp/form.announce.tpl"}
<br />
{/iterate}
</div>

<p style="text-align: justify;">
<small>
<em>Nota Bene&nbsp;:</em> les informations présentées ici n'engagent que leurs auteurs
respectifs et sont publiées à leur initiative. L'association Polytechnique.org
ne pourrait en aucun cas être tenue responsable de la nature des propos relatés
sur cet espace d'expression et d'information. Elle se réserve le droit de
refuser ou de retirer toute information de nature diffamante ou pouvant être
interprétée comme polémique par un lecteur.
</small>
</p>
{/if}
{elseif $is_admin}
<div class="center">
  [<a href="{$platal->ns}announce/new">{icon name=page_edit} Ajouter une annonce</a>]
</div>
{/if}

{if t($payments)}
<p>Télépaiements publics pour le groupe {$asso->nom}&nbsp;:</p>
<ul>
{foreach from=$payments item=payment}
<li><a href="{$platal->ns}payment/{$payment.id}">{icon name=money title="Télépaiement"}{$payment.text}</a></li>
{/foreach}
</ul>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
