{* $Id: index.tpl,v 1.5 2004-08-24 23:06:05 x2000habouzit Exp $ *}

<div class="rubrique">
  Micropaiments
</div>
{dynamic}
{if $op eq "submit" and !$error}
{include_php file=$methode_include}
{else}
{foreach from=$erreur item=e}
<p class="erreur">{$e}</p>
{/foreach}

<form method="post" action="{$smarty.server.PHP_SELF}">
  <p class="normal"> Si tu ne souhaites pas utiliser notre interface de
  télépaiement, tu peux virer directement la somme de ton choix sur notre compte
  30004 00314 00010016782 60. Nous veillerons à ce que ton paiement parvienne à
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
        <select name="ref" onchange="this.form.op.value='select'; this.form.submit();">
          {select_db_table table="paiement.paiements" valeur=$ref where=" WHERE FIND_IN_SET('old',flags)=0"}
        </select>
        {if $ref_url}
        <a href="{$ref_url}" onclick="return popup(this)">plus d'informations</a>
        {/if}
      </td>
    </tr>
    <tr>
      <td>Méthode</td>
      <td>
        <select name="methode">
          {select_db_table table="paiement.methodes" valeur=$methode}
        </select>
      </td>
    </tr>
    <tr>
      <td>Montant (euros)</td>
      <td><input type="text" name="montant" size="13" value="{$montant}" /></td>
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

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
