{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************}



{if $smarty.request.action eq "edit"}
<h1>
  Gestion des opérations de trésorerie
</h1>

<form method="post" action="{$smarty.server.PHP_SEL}">
  <table class="bicol">
    <tr>
      <th colspan="2">
        {if $op_id}
        Modifier une opération
        {else}
        Ajouter une opération
        {/if}
      </th>
    </tr>
    <tr>
      <td>Date (DD/MM/YYYY)</td>
      <td><input type="text" name="op_date" size="40"
        value="{$op_date|date_format:"%d/%m/%Y"}" /></td>
    </tr>
    <tr>
      <td>Description libre</td>
      <td><input type="text" name="op_label" size="40" value="{$op_label}" /></td>
    </tr>
    <tr>
      <td>Débit</td>
      <td><input type="text" name="op_debit" size="40" value="{$op_debit}" /></td>
    </tr>
    <tr>
      <td>Crédit</td>
      <td><input type="text" name="op_credit" size="40" value="{$op_credit}" /></td>
    </tr>
    <tr>
      <td class="center" colspan="2">
        <input type="hidden" name="op_id" value="{$op_id}" />
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="annee" value="{$annee_sel}" />
        <input type="hidden" name="mois" value="{$mois_sel}" />
        <input type="submit" value="enregistrer" /> 
      </td>
    </tr>
  </table>
</form>

<a href="{$smarty.server.PHP_SELF}">retour</a>
{elseif $smarty.request.action eq "update" && $op_id}
<strong>modification de l'opération</strong>
{elseif $smarty.request.action eq "update"}
<strong>ajout de l'opération</strong>
{elseif $smarty.request.action eq "del"}
<strong>suppression de l'opération</strong>
{/if}

{if $smarty.request.action neq "edit"}
<h1>
  Tr&eacute;sorerie pour {$mon_sel}
</h1>

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
      <form method="post" action="{$smarty.server.PHP_SELF}">
        <div>
          <input type="hidden" name="op_id" value="0" />
          <input type="hidden" name="action" value="edit" />
          <input type="hidden" name="annee" value="{$annee_sel}" />
          <input type="hidden" name="mois" value="{$mois_sel}" />
          <input type="submit" value="new" />
        </div>
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
      <form method="post" action="{$smarty.server.PHP_SELF}">
        <div>
          <input type="hidden" name="op_id" value="{$op.id}" />
          <input type="hidden" name="annee" value="{$annee_sel}" />
          <input type="hidden" name="mois" value="{$mois_sel}" />
          <input type="submit" name="action" value="edit" />
          <input type="submit" name="action" value="del" />
        </div>
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

{* vim:set et sw=2 sts=2 sws=2: *}
