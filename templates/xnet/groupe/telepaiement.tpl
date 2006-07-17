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

<h1>{$asso.nom} : Gestion des télépaiements </h1>

<p class="descr">
Voici la liste des paiements en ligne possible pour le groupe {$asso.nom}
</p>

{foreach from=$titres item=p}

<hr />
<h2>
<a href="https://www.polytechnique.org/payment/{$p.id}">{$p.text}</a>
</h2>

{if $trans[$p.id]}
<table cellpadding="0" cellspacing="0" class='bicol'>
  <tr>
    <th colspan="4">{$p.text} : détails pour les administrateurs</th>
  </tr>
  <tr>
    <td class="center">[{if $order eq 'timestamp'}<strong><a href='{$platal->ns}paiement?order={$order}&order_inv={$order_inv}'>{else}<a href='{$platal->ns}paiement?order=timestamp'>{/if}Date</a>{if $order eq 'timestamp'}</strong>{/if}]</td>
    <td class="center">[{if $order eq 'nom'}<strong><a href='?order={$order}&order_inv={$order_inv}'>{else}<a href='?order=nom'>{/if}Prénom NOM</a>{if $order eq 'nom'}</strong>{/if}]</td>
    <td class="center">[{if $order eq 'promo'}<strong><a href='{$platal->ns}paiement?order={$order}&order_inv={$order_inv}'>{else}<a href='{$platal->ns}paiement?order=promo'>{/if}Promo</a>{if $order eq 'promo'}</strong>{/if}]</td>
    <td class="center">[{if $order eq 'montant'}<strong><a href='{$platal->ns}paiement?order={$order}&order_inv={$order_inv}'>{else}<a href='{$platal->ns}paiement?order=montant'>{/if}Montant</a>{if $order eq 'montant'}</strong>{/if}]</td>
  </tr>
  {assign var="somme" value=0}
  {foreach from=$trans[$p.id] item=p}
  <tr>
    {if $p.nom neq "somme totale"}
        <td class="center">{$p.date|date_format:"%d/%m/%y"}</td>
        <td>
          {$p.prenom} {$p.nom}
          <a href="https://www.polytechnique.org/profile/{$p.alias}"><img alt="[fiche]" title="Voir sa fiche" src="images/loupe.gif"/></a>
          <a href="mailto:{$p.alias}@polytechnique.org"><img alt="[mail]" title="Lui envoyer un mail" src="images/mail.png"/></a>
        </td>
        <td class="center">X {$p.promo}</td>
        <td class="right">{$p.montant}</td>
    {else}
        <td class="right" colspan="3"><strong>Total </strong></td>
        <th class="right">{$p.montant}</th>
    {/if}        
  </tr>
  {/foreach}
</table>
{/if}

{foreachelse}

<p class="descr">
<em>Pas de télépaiement en cours ...</em>
</p>

{/foreach}

{* vim:set et sw=2 sts=2 sws=2: *}
