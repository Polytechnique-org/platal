{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

<h1>Réconciliation - {$title}</h1>

{if $step eq 'step1'}

<table class="bicol">
  <tr class="impair">
    <th>Choix de la méthode de paiement</th>
  </tr>
  {foreach from=$methods item=method}
  <tr class="{cycle values="pair,impair"}">
    <td>
      <a href="admin/payments/reconcile/step1/{$method.id}">{$method.text}</a>
    </td>
  </tr>
  {/foreach}
</table>

{elseif $step eq 'step2'}

{include core=csv-importer.tpl}

{elseif $step eq 'step3'}

<form method="POST">
  {xsrf_token_field}
	<table class="bicol">
		<tr class="impair">
			<th colspan="3">Récapitulatif des informations de réconciliation</th>
		</tr>
		<tr class="pair">
			<td width="30%">ID de la méthode de paiement:</td>
			<td colspan="2">{$recon.method_id}</td>
		</tr>
		<tr class="impair">
			<td>Début de période :</td>
			<td colspan="2">
				<input type="text" name="period_start" value="{$recon.period_start}" maxlength="10" />
				<em>jj/mm/aaaa</em>
			</td>
		</tr>
		<tr class="pair">
			<td>Fin de période :</td>
			<td colspan="2">
			  <input type="text" name="period_end" value="{$recon.period_end}" maxlength="10" />
				<em>jj/mm/aaaa</em>
			</td>
		</tr>
		<tr class="impair">
			<td>Nombre de transactions :</td>
			<td colspan="2">{$recon.payment_count}</td>
		</tr>
		<tr class="pair">
			<td>Total des paiements :</td>
			<td>{$recon.sum_amounts|string_format:"%.2f"|replace:'.':','}€</td>
			<td>(environ {$recon.sum_amounts/$recon.payment_count|string_format:"%.2f"|replace:'.':','}€/paiement)</td>
		</tr>
		<tr class="impair">
			<td>Total des commissions :</td>
			<td>{$recon.sum_commissions|string_format:"%.2f"|replace:'.':','}€</td>
			<td>(environ {$recon.sum_commissions/$recon.sum_amounts*100|string_format:"%.2f"|replace:'.':','}%)</td>
		</tr>
	</table>

	<br />

	<table class="bicol">
		<tr class="impair">
			<th colspan="2">À l'étape suivante, une comparaison entre les transactions existantes et la liste importé va être réalisée.</th>
		</tr>
		<tr class="pair">
			<td width="30%">Vérification à faire :</td>
			<td>
			  <label><input type="checkbox" name="check1" checked="checked" disabled="disabled"/> apparier les transactions</label><br />
			  <label><input type="checkbox" name="check2" checked="checked" /> afficher les transactions existantes orphelines</label><br />
			  <label><input type="checkbox" name="check3" checked="checked" /> afficher les transactions importées orphelines</label><br />
			</td>
		</tr>
	</table>

  <p class="center"><input type="submit" name="next" value="étape suivante" /></p>
</form>

{elseif $step eq 'step4'}

<p>ok : {$ok_count}<br />
differ : {$differ_count}<br />
onlydb : {$onlydb_count}<br />
onlyim : {$onlyim_count}<br />
total (excepted onlydb) : {$ok_count+$differ_count+$onlyim_count} (doit être égal à {$recon.payment_count})
</p>

<h2>Enregistrements avec champs qui diffèrent</h2>

{if $differ_count ne 0}
<table class="bicol">
	<tr class="impair">
		<th>Référence</th><th>method_id</th><th>Date</th>
		<th>Montant</th><th>Com</th><th>Statut</th>
		<th>recon_id</th><th>Action</th>
	</tr>
{foreach from=$differs item=i}
  <tr class="{cycle values="pair,impair"}">
		<td>{$i.fullref}<br />{$i.reference}</td>
		<td>{$i.method_id}<br />&nbsp;</td>
		<td>{$i.ts_confirmed}<br />{$i.date}</td>
		<td>{$i.amount}<br />{$i.amount2}</td>
		<td>{$i.commission}<br />{$i.commission2}</td>
		<td>{$i.status}<br />&nbsp;</td>
		<td>{$i.recon_id}<br />&nbsp;</td>
		<td><form method="POST">{xsrf_token_field}<input type="submit" name="force[{$i.id}]" value="Forcer" /></form></td>
	</tr>
{/foreach}
</table>
{else}
<p>Aucun</p>
{/if}

<h2>Enregistrements uniquement dans la base</h2>

{if $onlydb_count ne 0}
<table class="bicol">
{assign var='headerstatus' value='doheader'}
{foreach from=$only_database item=i}
	{if $headerstatus eq 'doheader'}
	{assign var='headerstatus' value='headerdone'}
	<tr class="impair">
		{foreach from=$i key=k item=v}
			<th>{$k}</th>
		{/foreach}
	</tr>
	{/if}
  <tr class="{cycle values="pair,impair"}">
		{foreach from=$i key=k item=v}
			<td>{$v}</td>
		{/foreach}
	</tr>
{/foreach}
</table>
{else}
<p>Aucun</p>
{/if}

<h2>Enregistrements uniquement dans l'import</h2>

{if $onlyim_count ne 0}
<table class="bicol">
{assign var='headerstatus' value='doheader'}
{foreach from=$only_import item=i}
	{if $headerstatus eq 'doheader'}
	{assign var='headerstatus' value='headerdone'}
	<tr class="impair">
		{foreach from=$i key=k item=v}
			<th>{$k}</th>
		{/foreach}
	</tr>
	{/if}
  <tr class="{cycle values="pair,impair"}">
		{foreach from=$i key=k item=v}
			<td>{$v}</td>
		{/foreach}
	</tr>
{/foreach}
</table>
{else}
<p>Aucun</p>
{/if}

<h2>Commentaires</h2>

<p>Les tableaux si dessus ne seront pas enregistrés, il convient donc de reprendre leur contenu dans
le champ de commentaires si dessous, si nécesssaire.</p>

<form method="POST">
{xsrf_token_field}
<textarea name="comments" rows="10" cols="100">{$recon.comments}</textarea>
<input type="submit" name="savecomments" value="Enregistrer les commentaires" /></p>
</form>

<h2>Suite</h2>

<form method="POST">
<p class="center"><input type="submit" name="next" value="Terminer la réconciliation" /></p>
</form>

{else} {* defaults to "list" *}
{assign var='dontshowback' value='dontshowback'}

TODO: listing

{/if}

{if $dontshowback}
<p>
<a href="admin/payments/reconcile">back</a>
</p>
{/if}
