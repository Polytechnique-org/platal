{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

<h1>{$asso.nom} : <a href="{$smarty.server.PHP_SELF}">Evénements</a></h1>

<h2>{$evt.intitule}</h2>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class="tiny" cellspacing="0" cellpadding="0">
    <tr>
      <td class="titre">Annoncé par</td>
      <td>{$evt.prenom} {$evt.nom} (X{$evt.promo})</td>
    </tr>
    <tr>
      <td class="titre">Description</td>
      <td>{$evt.descriptif}</td>
    </tr>
    <tr>
      <td class="titre">Date</td>
      <td>{$evt.deb}{if $evt.fin} - {$evt.fin}{/if}</td>
    </tr>
  </table>

  <div><br /><br /></div>

  {assign var="montant" value=0}

  <table class="bicol" cellpadding="0" cellspacing="0">
    {iterate from=$moments item=m}
    {assign var="montant" value=$montant+$m.montant*$m.nb}
    {if $m.titre || $m.montant}
    <tr>
      <td>
        <input type="hidden" name="item_id{counter}" value="{$m.item_id}" />
        <input type="hidden" name="eid" value="{$evt.eid}" />
        <strong>{$m.titre} - {if $m.montant > 0}{$m.montant}&euro;{else}gratuit{/if}</strong>
      </td>
    </tr>
    {/if}
    <tr>
      <td>{$m.details}</td>
    </tr>
    <tr>
      <td>
        <input name='item_{$m.item_id}' value='0' type='radio' {if $m.nb eq 0}checked="checked"{/if} /> je ne participe pas<br />
        <input name='item_{$m.item_id}' value='1' type='radio' {if $m.nb eq 1}checked="checked"{/if} /> je participe, seul<br />
        <input name='item_{$m.item_id}' value='+' type='radio' {if $m.nb > 1}checked{/if} /> je viens, et serai accompagné de
          <input type='text' name='itemnb_{$m.item_id}' value='{if $m.nb < 2}0{else}{$m.nb-1}{/if}' size="2" maxlength="2" /> personnes
      </td>
    </tr>
    {/iterate}
  </table>
  
  {if $montant > 0 || $paid > 0}
  <p class="erreur">
  Pour cet événement tu dois payer {$montant|replace:'.':','}&nbsp;&euro; {if $paid > 0}, et tu as déjà payé {$paid|replace:'.':','}&nbsp;&euro;{/if}
  {if $evt.paiement_id}[<a href="https://www.polytechnique.org/paiement/?ref={$evt.paiement_id}">Effectuer le paiement</a>]{/if}
  </p>
  {/if}
  <div class="center">
    <input type='submit' name='ins' value='valider ma participation' />
    <input type='reset' value='annuler' />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
