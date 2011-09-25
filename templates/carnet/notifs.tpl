{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

<p>Les emails sont hebdomadaires (pour éviter une trop grosse charge du serveur d'envoi et de ta boite email).
S'il n'y a rien à te signaler l'email ne t'est pas envoyé.</p>

<form action="carnet/notifs" method="post">
  {xsrf_token_field}
  <fieldset>
    <legend>{icon name="email"} Email</legend>
    <label>
      <input type='checkbox' name='mail' onclick="this.form.submit();" {if $flags->hasFlag('mail')}checked="checked"{/if} />
      Recevoir un email hebdomadaire des événements que je n'ai pas déjà vus sur le site.
    </label><br />
    <input type='hidden' name='flags_mail' value='valider' />
  </fieldset>
</form>

<form action="carnet/notifs" method="post">
  {xsrf_token_field}
  <fieldset>
    <legend>{icon name="bell"} Événements à surveiller</legend>
    <label>
      <input type="checkbox" name='sub[profile]' {if $actions->hasFlag('profile')}checked="checked"{/if} />
      Mise à jour de fiche<sup>*</sup>
    </label><br />
    <label>
      <input type="checkbox" name='sub[registration]' {if $actions->hasFlag('registration')}checked="checked"{/if} />
      Nouveaux inscrits
    </label><br />
    <label>
      <input type="checkbox" name='sub[death]' {if $actions->hasFlag('death')}checked="checked"{/if} />
      Décès
    </label><br />
    <label>
      <input type="checkbox" name='sub[birthday]' {if $actions->hasFlag('birthday')}checked="checked"{/if} />
      Anniversaires<sup>o</sup>
    </label><br />
    <span class='smaller'><sup>*</sup>: ne concerne pas les promos (événements très fréquents).</span><br />
    {assign var="profile" value=$smarty.session.user->profile()}
    <span class='smaller'><sup>o</sup>: ne concerne que les promos entre {$profile->yearpromo()-1} et {$profile->yearpromo()+1} que tu surveilles.</span>
  </fieldset>
  <div class='center'>
    <input type='submit' name='subs' value='valider' />
  </div>
</form>

<h1 id='middle'>Qui/Que surveiller&nbsp;?</h1>

<h2>Surveiller ses contacts</h2>

<form action="carnet/notifs#middle" method="post">
  {xsrf_token_field}
  <fieldset>
    <legend>{icon name="user_suit"} Contacts</legend>
    <label>
      <input type='checkbox' name='contacts' onclick="this.form.submit();" {if $flags->hasFlag('contacts')}checked="checked"{/if} />
      Surveiller mes contacts
    </label><br />
    <input type='hidden' name='flags_contacts' value='valider' />
  </fieldset>
</form>

<br />

<h2>Surveiller des promos</h2>

<p>
Attention&nbsp;: pour les promos, tu n'es pas notifié des événements trop fréquents (par exemple les changements de fiche).
</p>

<form action="carnet/notifs/" method="post">
  {xsrf_token_field}
  <fieldset>
    <legend>{icon name="group"} Ajouter une promo</legend>
    Tu peux surveiller des promos (mettre la promo sur 4 chiffres),
    ou des plages de promos (par ex. 1990-1992)&nbsp;:<br />
    <input type='text' name='promo' />
    <input type='submit' name='add_promo' value='ajouter'
      onclick="this.form.action += 'add_promo/' + this.form.promo.value;" />
    <input type='submit' name='del_promo' value='retirer'
      onclick="this.form.action += 'del_promo/' + this.form.promo.value;" />
    <br />
    {if $promo_count eq 0}
    <p>Tu ne surveilles actuellement aucune promo.</p>
    {else}
    <p>Tu surveilles {if $promo_count eq 1}la promotion suivante&nbsp;:{else}les promotions suivantes&nbsp;:{/if}</p>
    <ul>
    {foreach from=$promo_ranges item=promos}
      <li>{$promos[0]}{if $promos[0] neq $promos[1]} à {$promos[1]}{/if}</li>
    {/foreach}
    </ul>
    {/if}
  </fieldset>
</form>

<h2>Surveiller des groupes X</h2>

<p>
Attention&nbsp;: comme pour les promos, pour les groupes X, tu n'es pas notifié des événements trop fréquents (par exemple les changements de fiche).
</p>

<form action="carnet/notifs/" method="post">
  {xsrf_token_field}
  <fieldset>
    <legend>{icon name="group"} Ajouter un groupe X</legend>
    Tu peux surveiller des groupes X (mettre le nom ou le diminutif du groupe).<br />
    <input type='text' name='group' />
    <input type='submit' name='add_group' value='ajouter'
      onclick="this.form.action += 'add_group/' + this.form.group.value;" />
    <input type='submit' name='del_group' value='retirer'
      onclick="this.form.action += 'del_group/' + this.form.group.value;" />
    <br />
    {if $groups_count eq 0}
    <p>Tu ne surveilles actuellement aucun groupe X.</p>
    {else}
    <p>Tu surveilles {if $groups_count eq 1}le groupe suivant&nbsp;:{else}les groupes suivants&nbsp;:{/if}</p>
    <ul>
    {foreach from=$groups item=group}
      <li>{$group}</li>
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
  <legend>{icon name="status_offline" text="Non inscrit"} Non-inscrits</legend>
    {if $nonins|@count eq 0}
    Tu ne surveilles actuellement aucun non-inscrit.
    {else}
    Tu surveilles {if $nonins|@count eq 1}le non-inscrit{else}les non-inscrits{/if}&nbsp;:
    <ul>
    {foreach from=$nonins item=p}
    <li>
      {profile user=$p promo=true sex=true}
      <a href="carnet/notifs/del_nonins/{$p->login()}?token={xsrf_token}">{icon name='cross' title='retirer'}</a>
    </li>
    {/foreach}
  </ul>
  {/if}
</fieldset>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
