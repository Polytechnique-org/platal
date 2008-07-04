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

<h1>{$asso.nom}&nbsp;: Evénement {$event.intitule}</h1>

<p>
  [<a href="{$platal->ns}events">Revenir à la liste des événements</a>]
</p>

<p class='descr'>
  Cet événement a lieu 
  <strong>
  {if $event.fin and $event.fin neq $event.debut}
    {if $event.debut_day eq $event.fin_day}
      le {$event.debut|date_format:"%d %B %Y"} de {$event.debut|date_format:"%H:%M"} à {$event.fin|date_format:"%H:%M"}
    {else}
      du {$event.debut|date_format:"%d %B %Y à %H:%M"}<br />
      au {$event.fin|date_format:"%d %B %Y à %H:%M"}
    {/if}
  {else}
    le {$event.debut|date_format:"%d %B %Y à %H:%M"}
  {/if}
  </strong>
  et a été proposé par
  <a href='https://www.polytechnique.org/profile/{$event.alias}' class='popup2'>
    {$event.prenom} {$event.nom} ({$event.promo}).
  </a>
</p>

<p class='descr'>
  {$event.descriptif|nl2br}
</p>

{if $admin || $event.show_participants}
<p class='descr'>
  Tu peux 
  <a href="{$platal->ns}events/admin/{$event.eid}">
    consulter la liste des participants
    {icon name=group title="Liste des participants"}</a>
  déjà inscrits.
</p>
{/if}

<form action="{$platal->ns}events/sub/{$event.eid}" method="post">
  {xsrf_token_field}
  <table class="tiny" cellspacing="0" cellpadding="0">
    {foreach from=$event.moments item=m}
    <tr><th>{$m.titre} ({$m.montant} &euro;)</th></tr>
    {if $m.details}
    <tr>
      <td>
      {$m.details|nl2br}
      </td>
    </tr>
    {/if}
    <tr>
      <td>
        {if $event.inscr_open}
          <input type="radio" name="moment[{$m.item_id}]" value="0"
          {if !$m.nb}checked="checked"{/if}/>non
          {if $event.noinvite}
              <input type="radio" name="moment[{$m.item_id}]" value="1"
              {if $m.nb eq 1}checked="checked"{/if}/>oui
          {else}
              <input type="radio" name="moment[{$m.item_id}]" value="1"
              {if $m.nb eq 1}checked="checked"{/if}/>seul<br />
              <input type="radio" name="moment[{$m.item_id}]" value="2"
              {if $m.nb > 1}checked="checked"{/if}/>avec
                <input size="2" name="personnes[{$m.item_id}]"
                  value="{if $m.nb > 1}{math equation='x - 1' x=$m.nb}{else}1{/if}"/> personnes
          {/if}
        {else}
          {if !$m.nb}
            Je ne viendrai pas.
          {elseif $m.nb eq 1}
            Je viendrai{if !$event.noinvite} seul{/if}.
          {else}
            Je viendrai avec {$m.nb} personne{if $m.nb > 2}s{/if}.
          {/if}
        {/if}
      </td>
    </tr>
    {/foreach}

    <tr><th>À payer</th></tr>
    <tr>
      <td>
        {if $event.topay}
        <div class="error">
          {if !$event.paid}
          Tu dois payer {$event.topay|replace:'.':','}&nbsp;&euro;.
          {elseif $event.paid < $event.topay}
          Tu dois encore payer {math equation="a-b" a=$event.topay b=$event.paid|replace:'.':','}&nbsp;&euro;
          (tu as déjà payé {$event.paid|replace:'.':','}&nbsp;&euro;).
          {else} 
          Tu as déjà payé {$event.paid|replace:'.':','}&nbsp;&euro; pour ton inscription.
          {/if}
        </div>
        <div>
          {if $event.paiement_id &&  $event.paid < $event.topay}
          <a href="{$platal->ns}payment/{$event.paiement_id}?montant={math equation="a-b" a=$event.topay b=$event.paid}">
          {icon name=money} Payer en ligne</a>
          {elseif $validation && $event.paid < $event.topay}
          <br />Le télépaiement pour cet événement est en instance de validation&nbsp;:<br />
          <input type="checkbox" name="notify_payment" {if $event.notify_payment}checked="checked"{/if} id="notify" />
          <label for="notify">être prévenu lorsque le télépaiment pour cet événement sera disponible.</label>
          {/if}
        </div>
        {else}
        Rien à payer
        {if $event.paid > 0}
        (tu as déjà payé {$event.paid|replace:'.':','}&nbsp;&euro;).
        {/if}.
        {/if}
      </td>
    </tr>
  </table>

  <p style="text-align:center">
    <input type="submit" name='submit' value="Valider mes inscriptions" />
  </p>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
