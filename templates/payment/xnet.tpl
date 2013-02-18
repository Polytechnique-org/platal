{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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

{if t($anchor)}
{literal}
<script type="text/javascript">
//<![CDATA[
$(function() {
{/literal}
  location.hash = "#{$anchor}";
{literal}
});
// ]]>
</script>
{/literal}
{/if}

<h1>{$asso->nom}&nbsp;: Gestion des télépaiements </h1>

<p class="descr">
Voici la liste des paiements en ligne possible pour le groupe {$asso->nom}. Pour afficher les camarades dans un certain ordre,
il suffit de cliquer sur le titre de la colonne concernée.
</p>

{foreach from=$titles item=p}

<fieldset>
<legend id="legend_{$p.id}">{icon name=money title="Télépaiement"}{$p.text}</legend>

{if $event[$p.id]}
{assign var='ev' value=$event[$p.id]}
<p>
  {if t($p.url)}
  Plus d'informations sur ce télépaiement sont disponibles sur <a href="{$p.url}">cette page</a>.<br />
  {/if}
  {if t($ev.eid)}
  Ce paiement est associé à l'événement <a href="{$platal->ns}events">{$ev.title}</a>.<br />
    {if $ev.ins}
    Tu es inscrit à cet événement.
      {if $ev.topay > $ev.paid}
      <a href="{$platal->ns}payment/{$p.id}?montant={math equation="a-b" a=$ev.topay b=$ev.paid}">
        Tu dois encore payer {math equation="a-b" a=$ev.topay b=$ev.paid}&euro;.
      </a>
      {elseif $ev.topay eq $ev.paid}
      Tu as déjà réglé l'intégralité de ton inscription ({$ev.topay}&euro;).
      {else}
      Tu as réglé {$ev.paid}&euro; alors que tu n'en devais que {$ev.topay}&euro;.
      {/if}
    {else}
    <a href="{$platal->ns}events/sub/{$ev.eid}">Tu peux t'inscire à cet événement.</a>
    {/if}
  {else}
    {if !$ev.paid}
    Tu n'as actuellement rien payé sur ce télépaiement.
    {else}
    Tu as déjà payé {$ev.paid}&euro;.
    {/if}
  {/if}
</p>
{/if}

{if $is_admin && $trans[$p.id]}
<p>
  Liste des personnes ayant payé (pour les administrateurs uniquement)&nbsp;:<br />
  {include file="include/csv.tpl" url="`$platal->ns`payment/csv/`$p.id`.csv"}
</p>
<table cellpadding="0" cellspacing="0" id="list_{$p.id}" class='bicol'>
  <tr>
    <th>
      {if $order eq 'timestamp' && $order_id eq $p.id}
        <a href='{$platal->ns}payment?order={$order}&amp;order_inv={$order_inv}&amp;order_id={$p.id}'>
          <img src="{$platal->baseurl}images/{if !$order_inv}dn{else}up{/if}.png" alt="" title="Tri {if $order_inv}dé{/if}croissant" />
      {else}
        <a href='{$platal->ns}payment?order=timestamp&amp;order_id={$p.id}'>
      {/if}Date</a>
    </th>
    <th colspan="2">
      {if $order eq 'directory_name' && $order_id eq $p.id}
        <a href='{$platal->ns}payment?order={$order}&amp;order_inv={$order_inv}&amp;order_id={$p.id}'>
          <img src="{$platal->baseurl}images/{if $order_inv}dn{else}up{/if}.png" alt="" title="Tri {if !$order_inv}dé{/if}croissant" />
      {else}
        <a href='{$platal->ns}payment?order=directory_name&amp;order_id={$p.id}'>{/if}
      NOM Prénom</a>
    </th>
    <th>
      {if $order eq 'promo' && $order_id eq $p.id}
        <a href='{$platal->ns}payment?order={$order}&amp;order_inv={$order_inv}&amp;order_id={$p.id}'>
          <img src="{$platal->baseurl}images/{if $order_inv}dn{else}up{/if}.png" alt="" title="Tri {if !$order_inv}dé{/if}croissant" />
      {else}
        <a href='{$platal->ns}payment?order=promo&amp;order_id={$p.id}'>
      {/if}Promo</a>
    </th>
    <th>
      {if $order eq 'comment' && $order_id eq $p.id}
        <a href='{$platal->ns}payment?order=comment&amp;order_inv={$order_inv}&amp;order_id={$p.id}'>
          <img src="{$platal->baseurl}images/{if $order_inv}dn{else}up{/if}.png" alt="" title="Tri {if !$order_inv}dé{/if}   siant" />
      {else}
        <a href='{$platal->ns}payment?order=comment&amp;order_id={$p.id}'>
      {/if}Commentaire</a>
    </th>
    <th>
      {if $order eq 'amount' && $order_id eq $p.id}
        <a href='{$platal->ns}payment?order={$order}&amp;order_inv={$order_inv}&amp;order_id={$p.id}'>
          <img src="{$platal->baseurl}images/{if $order_inv}dn{else}up{/if}.png" alt="" title="Tri {if !$order_inv}dé{/if}croissant" />
      {else}
        <a href='{$platal->ns}payment?order=amount&amp;order_id={$p.id}'>
      {/if}Montant</a>
    </th>
  </tr>
  {assign var="somme" value=0}
  {foreach from=$trans[$p.id] item=p name=people}
  {if !t($p.limit)}
  <tr>
    <td class="center">{if $p.date eq 0}-{else}{$p.date|date_format:"%d/%m/%y"}{/if}</td>
    <td>
      {profile user=$p.user promo=false}
    </td>
    <td>
      <a href="mailto:{$p.user->bestEmail()}">{icon name=email title="email"}</a>
    </td>
    <td class="center">{$p.user->promo()}</td>
    <td>{$p.comment|comment_decode}</td>
    <td class="right">{$p.amount|replace:'.':','} &euro;</td>
  </tr>
  {elseif $smarty.foreach.people.first}
  <tr>
    <td colspan="6" class="center">Personne n'a encore payé pour ce télépaiement.</td>
  </tr>
  {else}
  <tr class="pair">
    <td class="right" colspan="5"><strong>Total </strong></td>
    <th class="right">{$p.amount|replace:'.':','} &euro;</th>
  </tr>
  {/if}
  {/foreach}
</table>
{/if}
</fieldset>

{foreachelse}

<p class="descr">
<em>Pas de télépaiement en cours&hellip;</em>
</p>

{/foreach}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
