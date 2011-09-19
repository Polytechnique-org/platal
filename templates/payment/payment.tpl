{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

{if $smarty.request.op eq "submit" and !$pl_errors}

<h1>Télépaiements</h1>

<table class="bicol">
  <tr>
    <th colspan="2">Paiement via {$meth->text}</th>
  </tr>
  <tr>
    <td><b>Transaction</b></td>
    <td>{$pay->text}</td>
  </tr>
  <tr>
    <td><b>Montant</b></td>
    <td>{$amount} &euro;</td>
  </tr>
{if $comment}
  <tr>
    <td><b>Commentaire</b></td>
    <td>{$comment}</td>
  </tr>
{/if}
  <tr>
    <td>&nbsp;</td>
    <td>
      <form method="post" action="{$pay->api->urlform}">
      <div>
	<!-- infos commercant -->
        {foreach from=$pay->api->infos.commercant key="name" item="value"}
        <input type="hidden" name="{$name}" value="{$value}" />
        {/foreach}
        <!-- infos client -->
        {foreach from=$pay->api->infos.client key="name" item="value"}
        <input type="hidden" name="{$name}" value="{$value}" />
        {/foreach}
        <!-- infos commande -->
        {foreach from=$pay->api->infos.commande key="name" item="value"}
        <input type="hidden" name="{$name}" value="{$value}" />
        {/foreach}

        <!-- infos divers -->
        {foreach from=$pay->api->infos.divers key="name" item="value"}
        <input type="hidden" name="{$name}" value="{$value}" />
        {/foreach}
        <input type="submit" value="Valider" />
      </div>
      </form>
    </td>
  </tr>
</table>
<p>
En cliquant sur "Valider", tu seras redirigé{if $sex}e{/if} vers le site de {$pay->api->nomsite}, où il te
sera demandé de saisir ton numéro de carte bancaire. Lorsque le paiement aura été effectué, tu
recevras une confirmation par email.
</p>
{if $pay->api->text}
<p>
{$pay->api->text}
</p>
{/if}
{if $evtlink}
<p class="erreur">
Si tu n'es pas encore inscrit à cet événement, n'oublie pas d'aller t'<a href='http://www.polytechnique.net/{$evtlink.diminutif}/events/sub/{$evtlink.eid}'>inscrire</a>.
</p>
{/if}

{else}

{if t($donation)}
{include wiki=Docs.Dons}
{/if}

<form method="post" action="{$platal->pl_self()}">
  <table class="bicol">
    <tr>
      <th colspan="2">Effectuer un télépaiement</th>
    </tr>
    <tr>
      <td>Transaction</td>
      <td>
        <strong>{$pay->text}</strong><input type="hidden" name="ref" value="{$pay->id}" />
        {if $pay->url}
        <br />
        <a href="{$pay->url}">plus d'informations</a>
        {/if}
      </td>
    </tr>
    <tr>
      <td>Méthode</td>
      <td>
        <select name="methode">
          {select_db_table table="payment_methods" valeur=$smarty.request.methode}
        </select>
      </td>
    </tr>
    <tr>
      <td>Montant</td>
      <td><input type="text" name="amount" size="13" class="right" value="{$pay->amount_def}" /> &euro;</td>
    </tr>
    {if t($public)}
    <tr>
      <td>Identifiant <small>(prenom.nom.promo)</small></td>
      <td><input type="text" name="login" size="30" /></td>
    </tr>
    {/if}
    <tr>
      <td>Commentaire</td>
      <td><textarea name="comment" rows="5" cols="30"></textarea></td>
    </tr>
    {if t($donation)}
    <tr>
      <td>Afficher ton nom dans la liste des donateurs</td>
      <td>
        <label>Oui<input type="radio" name="display" value="1" checked="checked" /></label>
        &nbsp;-&nbsp;
        <label><input type="radio" name="display" value="0" />Non</label>
      </td>
    </tr>
    {/if}
    <tr>
      <td>&nbsp;</td>
      <td>
        <input type="hidden" name="op" value="submit" />
        <input type="submit" value="Continuer" />
      </td>
    </tr>
  </table>
</form>

{if t($transactions)}
<p class="descr">Tu as déjà effectué des paiements pour cette transaction&nbsp;:</p>
<table class="bicol">
<tr><th>Date</th><th>Montant</th></tr>
{iterate from=$transactions item=t}
  <tr class="{cycle values="pair,impair"}">
    <td>{$t.ts_confirmed|date_format}</td>
    <td>{$t.amount|replace:'.':','} &euro;</td>
  </tr>
{/iterate}
</table>
{/if}

{if t($donation)}
<p class="descr">Les 10 plus gros dons sont les suivants&nbsp;:</p>
<table class="bicol">
  <tr>
    <th>Nom</th>
    <th>Montant</th>
    <th>Date</th>
  </tr>
  {foreach from=$biggest_donations item=d}
  <tr class="{cycle values="pair,impair"}">
    <td>{$d.name}</td>
    <td class="center">{$d.amount|replace:'.':','} &euro;</td>
    <td>{$d.ts_confirmed|date_format}</td>
  </tr>
  {/foreach}
</table>

<p class="descr">Les dons par période&nbsp;:</p>
<table class="tinybicol">
  <tr>
    <th>Période</th>
    <th>Montant</th>
  </tr>
  {foreach from=$donations item=d}
  <tr class="{cycle values="pair,impair"}">
    <td>{if $d.month neq 0}{$d.ts_confirmed|date_format:"%B %Y"}{else}{$d.ts_confirmed|date_format:"%Y"}{/if}</td>
    <td style="text-align: right">{$d.amount|replace:'.':','} &euro;</td>
  </tr>
  {/foreach}
</table>
{/if}

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
