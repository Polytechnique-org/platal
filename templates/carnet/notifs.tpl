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
 ***************************************************************************
        $Id: notifs.tpl,v 1.12 2004-11-07 09:36:03 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{foreach from=$err item=e}
<p class='erreur'>{$e}</p>
{/foreach}

<h1>Notifications automatiques</h1>

<p>Les mails sont hebdomadaires (pour éviter une trop grosse charge du serveur de mails et de ta boite mail).
S'il n'y a rien à te signaler le mail ne t'est pas envoyé.</p>

<p>tu peux ici activer la surveillance de tes contacts, ce qui te permet :</p>
<ul>
  <li>d'être notifié lorsque tes contacts changent leur fiche</li>
  <li>d'être notifié lorsque un de tes contacts décède</li>
</ul>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Événements à surveiller</legend>
    {foreach from=$watch->cats() item=s key=i}
    <input type='checkbox' name='sub[{$i}]' {if $watch->subs($i)}checked="checked"{/if} />
    {$s.short} {if $s.frequent}*{/if}<br />
    {/foreach}
    <span class='smaller'>(*): ne concerne pas les promos (évènements très fréquents)</span>
  </fieldset>
  <div class='center'>
    <input type='submit' name='subs' value='valider' />
  </div>
</form>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Contacts</legend>
    <input type='checkbox' name='contacts' {if $watch->watch_contacts}checked="checked"{/if} /> Surveiller mes contacts<br />
  </fieldset>
  <div class='center'>
    <input type='submit' name='flags' value='valider' />
  </div>
</form>

<br />
<h1>Surveiller des promos</h1>

<p>
Pour les promos, tu es notifié lorsque un camarade de cette promo s'inscrit, et lorsque un camarade de cette promo décède.
</p>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Ajouter une promo</legend>
    Tu peux surveiller des promos (mettre la promo sur 4 chiffres),
    ou des plages de promos (par ex. 1990-1992) : <br />
    <input type='text' name='promo' />
    <input type='submit' name='add_promo' value='ajouter' />
    <input type='submit' name='del_promo' value='retirer' />
    <br />
    {if $watch->promos()|@count eq 0}
    <p>Tu ne surveilles actuellement aucune promo.</p>
    {else}
    <p>Tu surveilles les les promos suivantes :</p>
    <ul>
      {foreach from=$watch->promos() item=p}
      <li>{if $p.0 eq $p.1}{$p.0}{else}{$p.0} à {$p.1}{/if}</li>
      {/foreach}
    </ul>
    {/if}
  </fieldset>
</form>

<h1>Surveiller des non inscrits</h1>

<p>
Pour les non-inscrits, tu es notifié lorsqu'il s'inscrit, ou lorsque ce camarade décède.
</p>

<p>
Si un non-inscrit que tu surveille s'inscrit, il sera automatiquement ajouté à tes contacts.
</p>

<p>
Pour surveiller des membres non-inscrits, il faut passer par la <a href="{"search.php"|url}" onclick='return popup(this)'>recherche</a>
et cliquer sur les icones <img src="{"images/ajouter.gif"|url}" alt="Ajouter" /> pour les ajouter à cette liste
</p>

<table class='tinybicol' cellpadding="0" cellspacing="0">
  <tr>
    <td>
      {if $watch->nonins()|@count eq 0}
      <p>Tu ne surveilles actuellement aucun non-inscrit.</p>
      {elseif $watch->nonins()|@count}
      <p>Tu surveilles {if $watch->nonins()|@count eq 1}le non-inscrit{else}les non-inscrits{/if} :</p>
      <ul>
        {foreach from=$watch->nonins() item=p}
        <li>
        {$p.prenom} {$p.nom} ({$p.promo}) <a href="?del_nonins={$p.user_id}"><img src="{"images/retirer.gif"|url}" alt="retirer" /></a>
        </li>
        {/foreach}
      </ul>
      {/if}
    </td>
  </tr>
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
