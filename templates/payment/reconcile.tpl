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

<h1>Réconciliation - {$title}</h1>

{if $step eq 'list'}

<form method="POST" action="admin/reconcile/transfers">
<table class="bicol">
  <tr class="impair">
    <th>&nbsp;</th><th>id</th><th>method</th><th>du</th><th>au</th><th>statut</th><th>transactions</th><th>total (€)</th><th>coms (€)</th><th>actions</th>
  </tr>
	<tr class="impair" style="border-top: 1px solid #A2C2E1">
		<th>&nbsp;</th>
		<th style="text-align: left" colspan="9">non régroupées</th>
	</tr>
  <tr class="pair">
    <td>&nbsp;</td>
    <td colspan="8"><strong><a href="admin/reconcile/importlogs/step1">Créer une nouvelle réconciliation</a></strong></td>
    <td class="right"><a href="admin/reconcile/importlogs/step1">{icon name=add title='nouvelle réconciliation'}</a></td>
  </tr>
  {foreach from=$recongps item=recongp}
	  {assign var='sum1' value='0'}
	  {assign var='sum2' value='0'}
	  {assign var='sum3' value='0'}
	  {if $recongp.id}
		<tr class="impair"><td colspan="10">&nbsp;</td></tr>
		<tr class="impair" style="border-top: 1px solid #A2C2E1">
		  <th>&nbsp;</th>
		  <th style="text-align: left" colspan="8">groupe ID {$recongp.id}</th>
		  <th style="text-align: right"><a href="admin/reconcile/transfers/delgroup/{$recongp.id}?token={xsrf_token}">{icon name=delete title='supprimer'}</a></th>
		</tr>
		{/if}
	  {foreach from=$recongp.recons item=recon}
	    {assign var='sum1' value=`$sum1+$recon.payment_count`}
	    {assign var='sum2' value=`$sum2+$recon.sum_amounts`}
	    {assign var='sum3' value=`$sum3+$recon.sum_commissions`}
		  <tr class="pair">
			<td>
			  {if $recon.status eq 'transfering'}
			    <input type="checkbox" name="recon_id[{$recon.id}]" />
			  {else}
			    &nbsp;
			  {/if}
			</td>
			<td>{$recon.id}</td>
			<td>{$recon.method}</td>
			<td>{$recon.period_start}</td>
			<td>{$recon.period_end}</td>
			<td>{$recon.status}</td>
			<td class="right">{$recon.payment_count}</td>
			<td class="right">{$recon.sum_amounts}</td>
			<td class="right">{$recon.sum_commissions}</td>
			<td class="right">{if $recongp.id}&nbsp;{else}<a href="admin/reconcile/delete/{$recon.id}?token={xsrf_token}">{icon name=delete title='supprimer'}</a>{/if}</td>
		  </tr>
	  {/foreach}
	  {if $recongp.id}
			<tr class="impair">
				<td colspan="5">&nbsp;</td>
				<td class="right">total :</td>
				<td class="right">{$sum1}</td>
				<td class="right">{$sum2|string_format:"%.2f"}</td>
				<td class="right">{$sum3|string_format:"%.2f"}</td>
				<td>&nbsp;</td>
			</tr>
			<tr><td colspan="10">
				<table class="bicol">
				<tr>
					<th>id</th><th>date</th><th>message</th><th>RIB</th><th>€</th><th>action</th>
				</tr>
				{assign var='sum' value='0'}
				{foreach from=$recongp.transfers item=transfer}
					{assign var='sum' value=`$sum+$transfer.amount`}
					<tr class="{cycle values="pair,impair"}">
						<td>{$transfer.id}</td>
						<td>{if $transfer.date}{$transfer.date}{else}à virer{/if}</td>
						<td><small>{$transfer.message}</small></td>
						<td>{$transfer.owner}</td>
						<td class="right">{$transfer.amount}</td>
						<td class="right">
						  {if !$transfer.date}<a href="admin/reconcile/transfers/confirm/{$transfer.id}?token={xsrf_token}">{icon name=tick title='Confirmer la réalisation'}</a>{/if}
						  <a href="admin/reconcile/transfers/edit/{$transfer.id}?token={xsrf_token}">{icon name=page_edit title='Éditer'}</a>
						</td>
					</tr>
				{/foreach}
				</table>
			</td></tr>
			<tr class="impair">
				<td colspan="6">&nbsp;</td>
				<td class="right">total :</td>
				<td colspan="2">{$sum|string_format:"%.2f"} - coms = {$sum-$sum3|string_format:"%.2f"}</td>
				<td>&nbsp;</td>
			</tr>
	  {/if}
  {/foreach}
</table>
<p><input type="submit" name="generate" value="Grouper et créer les virements" /></p>
</form>

{elseif $step eq 'step1'}

<table class="bicol">
  <tr class="impair">
    <th>Choix de la méthode de paiement</th>
  </tr>
  {foreach from=$methods item=method}
  <tr class="{cycle values="pair,impair"}">
    <td>
      <a href="admin/reconcile/importlogs/step1/{$method.id}">{$method.text}</a>
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
<a href="admin/reconcile">back</a>
</p>
{/if}
