{* $Id: index.tpl,v 1.3 2004-02-11 15:35:33 x2000habouzit Exp $ *}

{dynamic}

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

<table class="bicol">
  <tr>
    <th>Date</th>
    <th>Description</th>
    <th>D&eacute;penses</th>
    <th>Recettes</th>
  </tr>
{foreach item=op from=$ops}
  <tr class="{cycle values="impair,pair"}">
    <td>{$op.date|date_format:"%d/%m/%Y"}</td>
    <td>{$op.label}</td>
    <td class="right">{$op.debit}</td>
    <td class="right">{$op.credit}</td>
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

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
