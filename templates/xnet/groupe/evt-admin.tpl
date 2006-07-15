{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>{$asso.nom} : <a href='{rel}/{$platal->ns}events'>Evénements</a> </h1>

<p>
L'événement {$evt.intitule} {if $evt.titre} - {$evt.titre}{/if} comptera {$evt.nb_tot} personne{if $evt.nb_tot > 1}s{/if}.
</p>

{if $evt.participant_list}
<p class="center">
[<a href="mailto:?bcc={$evt.short_name}-participants@evts.polytechnique.org">envoyer un mail à ceux qui viennent</a>]
-
[<a href="mailto:?bcc={$evt.short_name}-absents@evts.polytechnique.org">envoyer un mail aux membres non inscrits</a>]
</p>
{/if}

{if $moments}
<p class="center">
[<a href="{rel}/{$platal->ns}events/admin/{$page->argv[1]}"{if !$platal->argv[2]}class="erreur"{/if}>tout</a>]
{foreach from=$moments item=m}
[<a href="{rel}/{$platal->ns}events/admin/{$page->argv[1]}/{$m.item_id}" {if $platal->argv[2] eq $m.item_id}class="erreur"{/if}>{$m.titre}</a>]
{/foreach}
</p>
{/if}

<p class="center">
[<a href="{$url_page}" {if !$smarty.request.initiale}class="erreur"{/if}>tout</a>]
{foreach from=$alphabet item=c}
[<a href="{$url_page}?initiale={$c}"{if $smarty.request.initiale eq $c} class="erreur"{/if}>{$c}</a>]
{/foreach}
</p>

{if $admin}{literal}
<script type="text/javascript">
function remplitAuto(mail) {
  document.getElementById('inscription').mail.value=mail;
  document.getElementById('montant').mail.value=mail;
  return false;
}
</script>
{/literal}

{if $oublis}
<p class="erreur">
Ils ont payé mais ont oublié de s'inscrire :
</p>

<table summary="payé mais non inscrits" class="tiny">
  <tr>
    <th>Prénom NOM</th>
    <th>Promo</th>
    <th>Infos</th>
    <th>Montant</th>
  </tr>
  {iterate from=$oubliinscription item=m}
  <tr style="background:#d0c198;">
    <td>
      <a href="" {if $admin}onclick="return remplitAuto('{$m.email}')"{/if}>
      {$m.prenom} {$m.nom}
      </a>
    </td>
    <td>{$m.promo}</td>
    <td>
      <a href="https://www.polytechnique.org/profile/{$m.email}"><img src="{rel}/images/loupe.gif" alt="[fiche]" /></a>
      <a href="https://www.polytechnique.org/vcard/{$m.email}.vcf"><img src="{rel}/images/vcard.png" alt="[vcard]" /></a>
      <a href="mailto:{$m.email}@polytechnique.org"><img src="{rel}/images/mail.png" alt="mail" /></a>
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
    <th>Prénom NOM</th>
    <th>Promo</th>
    <th>Info</th>
    {if $tout}
      {if $moments}
        {foreach from=$moments item=m}
          <th>{$m.titre}</th>
        {/foreach}
      {else}
        <th>Nombre</th>
      {/if}
      {if $admin && $money}
        <th>Montant</th>
        <th>Payé</th>
      {/if}
    {else}
    <th>Nombre</th>
    {/if}
  </tr>
  {foreach from=$participants item=m}
  <tr style="background:#d0c198;">
    <td>
      <a href="" {if $admin}onclick="return remplitAuto('{$m.email}')"{/if}>
        {if $m.femme}&bull;{/if}{$m.prenom} {$m.nom}
      </a>
    </td>
    <td>{$m.promo}</td>
    <td>
      {if $m.x}
      <a href="https://www.polytechnique.org/profile/{$m.email}"><img src="{rel}/images/loupe.gif" alt="[fiche]" /></a>
      <a href="https://www.polytechnique.org/vcard/{$m.email}.vcf"><img src="{rel}/images/vcard.png" alt="[vcard]" /></a>
      <a href="mailto:{$m.email}@polytechnique.org"><img src="{rel}/images/mail.png" alt="mail" /></a>
      {else}
      <a href="mailto:{$m.email}"><img src="{rel}/images/mail.png" alt="mail"></a>
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
      {if $admin && $money}
        <td {if $m.montant > $m.paid}class="erreur"{/if}>{$m.montant}&euro;</td>
        <td>{$m.paid}&euro;</td>
      {/if}
    {else}
    <td>
      {$m.nb}
    </td>
    {/if}
  </tr>
  {/foreach}
</table>

<p class="descr">
{foreach from=$links item=ofs key=txt}
<a href="{$url_page}?offset={$ofs}&amp;initiale={$smarty.request.initiale}"{if $smarty.request.offset eq $ofs} class="erreur"{/if}>{$txt}</a>
{/foreach}
</p>

{if $admin}

<p class="descr">
[<a href="{rel}/{$platal->ns}events/csv/{$platal->argv[1]}/{$platal->argv[2]}/{$evt.intitule}{if $evt.titre}.{$evt.titre}{/if}.csv">Télécharger le fichier Excel</a>]
</p>

<hr />

<p class="descr">
En tant qu'administrateur, tu peux fixer la venue (accompagnée ou pas) d'un des membres du groupe.
Donne ici son mail (complet pour les extérieurs, sans @polytechnique.org pour les X), ainsi que le
nombre de participants.
</p>

<form action="{rel}/{$platal->ns}events/admin/{$evt.eid}/{$platal->argv[2]}" method="post" id="inscription">
  <p class="descr">
  <input type="hidden" name="eid" value="{$platal->argv[1]}" />
  <input type="hidden" name="adm" value="nbs" />

  Mail: <input name="mail" size="20" />
  {if $platal->argv[2]}
  <input type="hidden" name="item_id" value="{$platal->argv[2]}" />
  {$evt.titre}: <input name="nb{$platal->argv[2]}" size="1" value="1" />
  {else}
    {if $moments}
      {foreach from=$moments item=m}
        {$m.titre}: <input name="nb{$m.item_id}" size="1" value="1"/>
      {/foreach}
    {else}
      Nombre: <input name="nb1" size="1" value="1" />
    {/if}
  {/if}
  <input type="submit" />
  </p>
</form>

<hr />

<p class="descr">
En tant qu'administrateur, tu peux entrer un paiement reçu par une autre source que le télépaiement
du site X.org. Ce montant s'ajoutera aux montants déjà entrés. Si tu as fait une erreur, tu peux
entrer un montant négatif.
</p>

<p class="descr">
Note que tu peux cliquer sur les noms des membres pour remplir automatiquement la case ci-dessous
</p>

<form action="{$smarty.server.REQUEST_URI}" method="post" id="montant">
  <p class="descr">
  <input type="hidden" name="eid" value="{$platal->argv[1]}" />
  <input type="hidden" name="adm" value="prix" />
  Mail: <input name="mail" size="20" />
  montant: <input name="montant" size="3" value="0,00" /> &euro;
  <input type="submit" />
  </p>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
