{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<h1>{$asso->nom}&nbsp;: Evénement {$event.intitule}</h1>

<p>
  [<a href="{$platal->ns}events">Revenir à la liste des événements</a>]
</p>

<p class='descr'>
  {assign var=profile value=$event.organizer->profile()}
  Cet événement a lieu <strong>{$event.date}</strong> et a été proposé par
  <a href='https://www.polytechnique.org/profile/{$profile->hrpid}' class='popup2'>
    {$event.organizer->fullName('promo')}
  </a>.
</p>

<p class='descr'>
  {$event.descriptif|nl2br}
</p>

{if $is_admin || $event.show_participants}
<p class='descr'>
  Tu peux
  <a href="{$platal->ns}events/admin/{$eid}">
    consulter la liste des participants
    {icon name=group title="Liste des participants"}</a>
  déjà inscrits.
</p>
{/if}

<form action="{$platal->ns}events/sub/{$eid}" method="post">
  {xsrf_token_field}
  <table class="tiny" cellspacing="0" cellpadding="0">
    {foreach from=$moments key=item_id item=m}
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
          {assign var=nb value=$subs.$item_id.nb}
          <label><input type="radio" name="moment[{$item_id}]" value="0"
          {if !$nb}checked="checked"{/if}/>Je ne m'inscris pas</label><br />
          {if $event.noinvite}
              <label><input type="radio" name="moment[{$item_id}]" value="1"
              {if $nb eq 1}checked="checked"{/if}/>Je m'inscris</label>
          {else}
              <label><input type="radio" name="moment[{$item_id}]" value="2" id="avec"
              {if $nb > 0}checked="checked"{/if}/>J'inscris</label>
                  <input size="2" name="personnes[{$item_id}]"
                  value="{if $nb > 1}{$nb}{else}1{/if}"/><label for="avec"> personnes</label>
          {/if}
        {else}
          {if !$nb}
            Je ne viendrai pas.
          {else}
            J'ai inscrit {$nb} personne{if $nb > 1}s{/if}.
          {/if}
        {/if}
      </td>
    </tr>
    {/foreach}

    <tr><th>À payer</th></tr>
    <tr>
      <td>
        {if $topay}
          <div class="error">
          {if $paid eq 0}
          Tu dois payer {$topay|replace:'.':','}&nbsp;&euro;.
          {elseif $paid < $topay}
          Tu dois encore payer {math equation="a-b" a=$topay b=$paid|replace:'.':','}&nbsp;&euro;
          (tu as déjà payé {$paid|replace:'.':','}&nbsp;&euro;).
          {else}
          Tu as déjà payé {$paid|replace:'.':','}&nbsp;&euro; pour ton inscription.
          {/if}
        </div>
        <div>
          {if $event.paiement_id &&  $paid < $topay}
          <a href="{$platal->ns}payment/{$event.paiement_id}?montant={math equation="a-b" a=$topay b=$paid}">
          {icon name=money} Payer en ligne</a>
          {elseif $validation && $paid < $topay}
          <br />Le télépaiement pour cet événement est en instance de validation&nbsp;:<br />
          <input type="checkbox" name="notify_payment" {if $event.notify_payment}checked="checked"{/if} id="notify" />
          <label for="notify">être prévenu lorsque le télépaiment pour cet événement sera disponible.</label>
          {/if}
        </div>
        {else}
        Rien à payer
        {if $paid > 0}
        (tu as déjà payé {$paid|replace:'.':','}&nbsp;&euro;).
        {/if}.
        {/if}
      </td>
    </tr>
  </table>

  <p style="text-align:center">
    <input type="submit" name='submit' value="Valider mes inscriptions" />
  </p>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
