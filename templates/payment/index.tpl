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


<h1>Télépaiements</h1>

{if $smarty.request.op eq "submit" and !$xorg_errors|count}

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
    <td>{$montant} &euro;</td>
  </tr>
{if $comment}
  <tr>
    <td><b>Commentaire</b>
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
En cliquant sur "Valider", tu seras redirigé{if $smarty.session.sexe}e{/if} vers le site de {$pay->api->nomsite}, où il te
sera demandé de saisir ton numéro de carte bancaire.  Lorsque le paiement aura été effectué, tu
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

<script type='text/javascript'>
{literal}
function payment_submit(form)
{
    form.op.value = 'select';
    form.montant.value = 0;
    form.action = 'payment/' + form.ref.value;
    form.submit();
}
{/literal}
</script>

<form method="post" action="{$platal->pl_self()}">
  <p> Si tu ne souhaites pas utiliser notre interface de
  télépaiement, tu peux virer directement la somme de ton choix sur notre compte
  <strong>30004 00314 00010016782 60</strong>. Nous veillerons à ce que ton paiement parvienne à
  son destinataire.  Pense toutefois à le préciser dans le motif du
  versement.
  <br /><br />
  </p>
  <table class="bicol">
    <tr>
      <th colspan="2">Effectuer un télépaiement</th>
    </tr>
    <tr>
      <td>Transaction</td>
      <td>
        {if $asso}
        <strong>{$pay->text}</strong><input type="hidden" name="ref" value="{$pay->id}" />
        {else}
        <select name="ref" onchange="payment_submit(this.form)">
          {select_db_table table="`$prefix`paiements" valeur=$pay->id where="WHERE FIND_IN_SET('old',t.flags)=0"
                           join="LEFT JOIN groupex.asso AS g ON (t.asso_id = g.id)" group="g.nom"}
        </select>
        {/if}
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
          {select_db_table table="paiement.methodes" valeur=$smarty.request.methode}
        </select>
      </td>
    </tr>
    <tr>
      <td>Montant</td>
      <td><input type="text" name="montant" size="13" class='right' value="{$montant}" /> &euro;</td>
    </tr>
    <tr>
      <td>Commentaire</td>
      <td><textarea name="comment" rows="5" cols="30"></textarea></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>
        <input type="hidden" name="op" value="submit" />
        <input type="submit" value="Continuer" />
      </td>
    </tr>
  </table>
</form>

{if $transactions}
<p class="descr">Tu as déjà effectué des paiements pour cette transaction&nbsp;:</p>
<table class="bicol">
<tr><th>Date</th><th>Montant</th></tr>
{iterate from=$transactions item=t}
  <tr class="{cycle values="pair,impair"}">
    <td>{$t.timestamp|date_format}</td>
    <td>{$t.montant|replace:'EUR':'&euro;'}</td>
  </tr>
{/iterate}
</table>
{/if}

{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
