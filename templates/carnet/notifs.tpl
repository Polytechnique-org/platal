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


<h1>Notifications automatiques</h1>

<p>Les mails sont hebdomadaires (pour éviter une trop grosse charge du serveur de mails et de ta boite mail).
S'il n'y a rien à te signaler le mail ne t'est pas envoyé.</p>

<form action="carnet/notifs" method="post">
  <fieldset>
    <legend>Mail</legend>
    <input type='checkbox' name='mail' onclick="this.form.submit();" {if $watch->watch_mail}checked="checked"{/if} />
    Recevoir un mail hebdomadaire des événements que je n'ai pas déjà vus sur le site<br />
    <input type='hidden' name='flags_mail' value='valider' />
  </fieldset>
</form>

<form action="carnet/notifs" method="post">
  <fieldset>
    <legend>Événements à surveiller</legend>
    {foreach from=$watch->cats() item=s key=i}
    <input type='checkbox' name='sub[{$i}]' {if $watch->subs($i)}checked="checked"{/if} />
    {$s.short} {if $s.type eq near}<sup>o</sup>{elseif $s.type eq often}<sup>*</sup>{/if}<br />
    {/foreach}
    <span class='smaller'><sup>*</sup>: ne concerne pas les promos (événements très fréquents).</span><br />
    <span class='smaller'><sup>o</sup>: ne concerne que les promos entre {$smarty.session.promo-1} et {$promo_sortie-2} que tu surveilles.</span>
  </fieldset>
  <div class='center'>
    <input type='submit' name='subs' value='valider' />
  </div>
</form>

<h1 id='middle'>Qui/Que surveiller ?</h1>

<h2>Surveiller ses contacts</h2>

<form action="carnet/notifs#middle" method="post">
  <fieldset>
    <legend>Contacts</legend>
    <input type='checkbox' name='contacts' onclick="this.form.submit();" {if $watch->watch_contacts}checked="checked"{/if} /> Surveiller mes contacts<br />
    <input type='hidden' name='flags_contacts' value='valider' />
  </fieldset>
</form>

<br />

<h2>Surveiller des promos</h2>

<p>
Attention&nbsp;: pour les promos, tu n'es pas notifié des événements trop fréquents (par exemple les changements de fiche).
</p>

<form action="carnet/notifs/" method="post">
  <fieldset>
    <legend>Ajouter une promo</legend>
    Tu peux surveiller des promos (mettre la promo sur 4 chiffres),
    ou des plages de promos (par ex. 1990-1992)&nbsp;: <br />
    <input type='text' name='promo' />
    <input type='submit' name='add_promo' value='ajouter'
      onclick="this.form.action += 'add_promo/' + this.form.promo.value;" />
    <input type='submit' name='del_promo' value='retirer'
      onclick="this.form.action += 'del_promo/' + this.form.promo.value;" />
    <br />
    {if $watch->promos()|@count eq 0}
    <p>Tu ne surveilles actuellement aucune promo.</p>
    {else}
    <p>Tu surveilles les promos suivantes&nbsp;:</p>
    <ul>
      {foreach from=$watch->promos() item=p}
      <li>{if $p.0 eq $p.1}{$p.0}{else}{$p.0} à {$p.1}{/if}</li>
      {/foreach}
    </ul>
    {/if}
  </fieldset>
</form>

<h2>Surveiller des non inscrits</h2>

<p>
Si un non-inscrit que tu surveilles s'inscrit, il sera automatiquement ajouté à tes contacts.
</p>

<p>
Pour surveiller des membres non-inscrits, il faut passer par la <a href="search" class='popup'>recherche</a>
et cliquer sur les icones {icon name=add} pour les ajouter à cette liste.
</p>

<fieldset>
  <legend>Non-inscrits</legend>
    {if $watch->nonins()|@count eq 0}
    Tu ne surveilles actuellement aucun non-inscrit.
    {elseif $watch->nonins()|@count}
    Tu surveilles {if $watch->nonins()|@count eq 1}le non-inscrit{else}les non-inscrits{/if}&nbsp;:
    <ul>
    {foreach from=$watch->nonins() item=p}
    <li>
      {$p.prenom} {$p.nom} ({$p.promo}) <a href="carnet/notifs/del_nonins/{$p.user_id}">{icon name='cross' title='retirer'}/<>
    </li>
    {/foreach}
  </ul>
  {/if}
</fieldset>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
