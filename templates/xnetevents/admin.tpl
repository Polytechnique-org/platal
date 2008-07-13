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

<h1>{$asso.nom}&nbsp;: <a href='{$platal->ns}events'>Événements</a> </h1>

<p>
L'événement {$evt.intitule}
{if $evt.titre} - {$evt.titre}
{/if}
{if $evt.titre || count($moments) eq 1}
comptera {$evt.nb_tot} personne{if $evt.nb_tot > 1}s{/if}.
{else}
({$evt.nb} personne{if $evt.nb > 1}s ont réalisé leur{else} a réalisé son{/if} inscription).
{/if}
</p>

{if $evt.participant_list && $is_admin}
<p class="center">
[<a href="mailto:?bcc={$evt.short_name}-participants@{#globals.xnet.evts_domain#}">envoyer un mail à ceux qui viennent</a>]
-
[<a href="mailto:?bcc={$evt.short_name}-absents@{#globals.xnet.evts_domain#}">envoyer un mail aux membres non inscrits</a>]
</p>
{/if}

{if count($moments) > 1}
<p class="center">
[<a href="{$platal->ns}events/admin/{$evt.short_name|default:$evt.eid}"{if
!$platal->argv[2]}class="erreur"{/if}>Vue générale</a>]
{foreach from=$moments item=m}
[<a href="{$platal->ns}events/admin/{$evt.short_name|default:$evt.eid}/{$m.item_id}" {if $platal->argv[2] eq $m.item_id}class="erreur"{/if}>{$m.titre}</a>]
{/foreach}
</p>
{/if}

<p class="center">
[<a href="{$platal->pl_self()}" {if !$smarty.request.initiale}class="erreur"{/if}>tout</a>]
{foreach from=$alphabet item=c}
[<a href="{$platal->pl_self()}?initiale={$c}"{if $smarty.request.initiale eq $c} class="erreur"{/if}>{$c}</a>]
{/foreach}
</p>

{if $is_admin}{literal}
<script type="text/javascript">
function remplitAuto(mail) {
  document.getElementById('inscription').mail.value=mail;
  var f = document.getElementById('montant');
  if (f)
      f.mail.value=mail;
}
</script>
{/literal}

{if $oublis}
<p class="erreur">
Ils ont payé mais ont oublié de s'inscrire&nbsp;:
</p>

<table summary="payé mais non inscrits" class="tinybicol">
  <tr>
    <th>Prénom NOM</th>
    <th>Promo</th>
    <th>Infos</th>
    <th>Montant</th>
  </tr>
  {iterate from=$oubliinscription item=m}
  <tr class="pair">
    <td>
      <a href="" {if $is_admin}onclick="return remplitAuto('{$m.email}')"{/if}>
        {if !$m.prenom && !$m.nom}
        {$m.email}
        {else}
        {$m.prenom} {$m.nom}
        {/if}
      </a>
    </td>
    <td>{$m.promo}</td>
    <td>
      <a href="https://www.polytechnique.org/profile/{$m.email}">{icon name=user_suit title="fiche"}</a>
      <a href="https://www.polytechnique.org/vcard/{$m.email}.vcf">{icon name=vcard title="vcard"}</a>
      <a href="mailto:{$m.email}@{#globals.mail.domain#}">{icon name=email title="mail"}</a>
    </td>
    <td>{$m.montant}</td>
  </tr>
  {/iterate}
</table>

<hr />
{/if}

{/if}

<table summary="participants a l'evenement" class="{if $tout}large{else}tiny{/if}">
  <tr>
    {if $is_admin && $evt.paiement_id && $tout}
      {assign var=height value='rowspan="2"'}
    {/if}
    <th {$height|smarty:nodefaults}>Prénom NOM</th>
    <th {$height|smarty:nodefaults}>Promo</th>
    <th {$height|smarty:nodefaults}>Info</th>
    {if $tout}
      {if $moments}
        {foreach from=$moments item=m}
          <th {$height|smarty:nodefaults}>{$m.titre}</th>
        {/foreach}
      {else}
        <th {$height|smarty:nodefaults}>Nombre</th>
      {/if}
      {if $is_admin && $evt.money}
        <th {$height|smarty:nodefaults}>Montant</th>
        <th colspan="3">Payé</th>
      {/if}
    {else}
    <th {$height|smarty:nodefaults}>Nombre</th>
    {/if}
  </tr>
  {if $is_admin && $evt.paiement_id && $tout}
  <tr>
    <th>Télépaiement</th>
    <th>Autre</th>
    <th>Total</th>
  </tr>
  {/if}
  {foreach from=$participants item=m}
  <tr>
    <td>
      {if $is_admin}<a href="javascript:remplitAuto('{$m.email}')">{/if}
        {if $m.femme}&bull;{/if}{if !$m.prenom && !$m.nom}{$m.email}{else}{$m.prenom} {$m.nom}{/if}
      {if $is_admin}</a>{/if}
    </td>
    <td>{$m.promo}</td>
    <td>
      {if $m.x}
      <a href="https://www.polytechnique.org/profile/{$m.email}">{icon name=user_suit title="fiche"}</a>
      <a href="https://www.polytechnique.org/vcard/{$m.email}.vcf">{icon name=vcard title="vcard"}</a>
      <a href="mailto:{$m.email}@{#globals.mail.domain#}">{icon name=email title="mail"}</a>
      {else}
      <a href="mailto:{$m.email}">{icon name=email title="mail"}</a>
      {/if}
    </td>
    {if $tout}
      {if $moments}
        {foreach from=$moments item=i}
          <td>{$m[$i.item_id]}</td>
        {/foreach}
      {else}
        <td>{$m[1]}</td>
      {/if}
      {if $is_admin && $evt.money}
        <td {if $m.montant > $m.paid}class="erreur"{/if}>{$m.montant}&euro;</td>
        {if $evt.paiement_id}
          <td>{$m.telepayment|default:0}&euro;</td>
          <td>{$m.adminpaid|default:0}&euro;</td>
        {/if}
        <td {if $m.montant < $m.paid}class="erreur"{/if}>{$m.paid}&euro;</td>
      {/if}
    {else}
    <td>
      {$m.nb}
    </td>
    {/if}
  </tr>
  {/foreach}
  {if $is_admin && $evt.money && $tout}
  <tr>
    {assign var=cols value=$moments|@count}
    <td colspan="{$cols+3}" class="right"><strong>Total</strong></td>
    <td>{$evt.topay}&euro;</td>
    {if $evt.paiement_id}
    <td>{$evt.telepaid|default:0}&euro;</td>
    <td>{$evt.adminpaid|default:0}&euro;</td>
    {/if}
    <td>{$evt.paid}&euro;</td>
  </tr>
  {/if}
</table>

<p class="descr">
{foreach from=$links item=ofs key=txt}
<a href="{$platal->pl_self()}?offset={$ofs}&amp;initiale={$smarty.request.initiale}"{if $smarty.request.offset eq $ofs} class="erreur"{/if}>{$txt}</a>
{/foreach}
</p>

{if $absents->total()}

<hr />

<p>Les personnes suivantes ont indiqué qu'elles ne viendraient pas à l'événement&nbsp;:</p>

<table class="tinybicol">
  <tr><th>Prénom NOM</th><th>Origine</th></tr>
  {iterate from=$absents item=m}
  <tr>
    <td>
      {if $is_admin}<a href="javascript:remplitAuto('{$m.email}')">{/if}
      {if $m.sexe}&bull;{/if}{$m.prenom} {$m.nom}
      {if $is_admin}</a>{/if}
    </td>
    <td>
      {$m.promo}
    </td>
  </tr>
  {/iterate}
</table>

{/if}

{if $is_admin}

<p class="descr">
  <a href="{$platal->ns}events/csv/{$evt.eid}/{$platal->argv[2]}/{$evt.intitule}{if $evt.titre}.{$evt.titre}{/if}.csv">
    {icon name=page_excel title="Télécharger au format Excel"} Télécharger le fichier Excel
  </a>
</p>

<hr />

<p class="descr">
En tant qu'administrateur, tu peux fixer la venue (accompagnée ou pas) d'un des membres du groupe.
Donne ici son mail, ainsi que le nombre de participants.
</p>

<form action="{$platal->pl_self()}" method="post" id="inscription">
  {xsrf_token_field}
  <p class="descr">
    <input type="hidden" name="adm" value="nbs" />

    Mail&nbsp;: <input name="mail" size="20" />

    {if $platal->argv[2]}
    {$evt.titre}&nbsp;: <input name="nb[{$platal->argv[2]}]" size="1" value="1" />
    {else}
    {foreach from=$moments item=m}
    {$m.titre}&nbsp;: <input name="nb[{$m.item_id}]" size="1" value="1"/>
    {foreachelse}
    Nombre&nbsp;: <input name="nb[1]" size="1" value="1" />
    {/foreach}
    {/if}
    <input type="submit" />
  </p>
</form>

{if $evt.money}

<hr />

<p class="descr">
En tant qu'administrateur, tu peux entrer un paiement reçu par une autre source que le télépaiement
du site X.org. Ce montant s'ajoutera aux montants déjà entrés. Si tu as fait une erreur, tu peux
entrer un montant négatif.
</p>

<p class="descr">
Note que tu peux cliquer sur les noms des membres pour remplir automatiquement la case ci-dessous.
</p>

<form action="{$platal->pl_self()}" method="post" id="montant">
  {xsrf_token_field}
  <p class="descr">
  <input type="hidden" name="adm" value="prix" />
  Mail&nbsp;: <input name="mail" size="20" />
  montant&nbsp;: <input name="montant" size="3" value="0,00" /> &euro;
  <input type="submit" />
  </p>
</form>
{/if}

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
