{* $Id: gere_operations.tpl,v 1.3 2004-02-11 15:35:33 x2000habouzit Exp $ *}

{dynamic}

{if $smarty.request.action eq "edit"}
<div class="rubrique">
  Gestion des opérations de trésorerie
</div>

<form method="POST" action="{$smarty.server.PHP_SEL}">
  <input type="hidden" name="operation_id" value="{$operation_id}" />
  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="annee" value="{$annee_sel}" />
  <input type="hidden" name="mois" value="{$mois_sel}" />
  <table class="bicol">
    <tr>
      <th colspan="2">
        {if $operation_id}
        Modifier une opération
        {else}
        Ajouter une opération
        {/if}
      </th>
    </tr>
    <tr>
      <td>Date (DD/MM/YYYY)</td>
      <td><input type="text" name="operation_date" size="40"
        value="{$operation_date|date_format:"%d/%m/%Y"}" /></td>
    </tr>
    <tr>
      <td>Description libre</td>
      <td><input type="text" name="operation_label" size="40" value="{$operation_label}" /></td>
    </tr>
    <tr>
      <td>Débit</td>
      <td><input type="text" name="operation_debit" size="40" value="{$operation_debit}" /></td>
    </tr>
    <tr>
      <td>Crédit</td>
      <td><input type="text" name="operation_credit" size="40" value="{$operation_credit}" /></td>
    </tr>
    <tr>
      <td class="center" colspan="2">
        <input type="submit" value="enregistrer" /> 
      </td>
    </tr>
  </table>
</form>

<a href="{$smarty.server.PHP_SELF}">retour</a>
{elseif $smarty.request.action eq "update" && $operation_id}
<strong>modification de l'opération</strong>
{elseif $smarty.request.action eq "update"}
<strong>ajout de l'opération</strong>
{elseif $smarty.request.action eq "del"}
<strong>suppression de l'opération</strong>
{/if}

{if $smarty.request.action neq "edit"}
<div class="rubrique">
  Tr&eacute;sorerie pour {$mon_sel}
</div>

<table class="bicol">
<tr>
  <th>Solde en début de mois</th>
</tr>
<tr>
  <td class="right">{$from_solde}</td>
</tr>
</table>

<br />

<table class="bicol" style="font-size: 90%">
  <tr>
    <th>Id</th>
    <th>Date</th>
    <th>Description</th>
    <th>Débit</th>
    <th>Crédit</th>
    <th>Action</th>
  </tr>
  <tr class="impair">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><strong>Nouvelle opération</strong></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>
      <form method="POST" action="{$smarty.server.PHP_SELF}">
        <input type="hidden" name="operation_id" value="0" />
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="annee" value="{$annee_sel}" />
        <input type="hidden" name="mois" value="{$mois_sel}" />
        <input type="submit" value="new" />
      </form>
    </td>
  </tr>
{foreach item=op from=$ops}
  <tr class="{cycle values="pair,impair"}">
    <td>{$op.id}</td>
    <td>{$op.date|date_format:"%d/%m/%Y"}</td>
    <td>{$op.label}</td>
    <td class="right">{$op.debit}</td>
    <td class="right">{$op.credit}</td>
    <td>
      <form method="POST" action="{$smarty.server.PHP_SELF}">
        <input type="hidden" name="operation_id" value="{$op.id}" />
        <input type="hidden" name="annee" value="{$annee_sel}" />
        <input type="hidden" name="mois" value="{$mois_sel}" />
        <input type="submit" name="action" value="edit" />
        <input type="submit" name="action" value="del" />
      </form>
    </td>
  </tr>
{/foreach}
</table>

<br />

<table class="bicol">
<tr>
  <th>Solde en fin de mois</th>
</tr>
<tr>
  <td class="right">{$to_solde}</td>
</tr>
</table>

<br />

{include file=trezo/choix_date.tpl month_arr=$month_arr}
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
