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

{literal}
<script type="text/javascript">
function chgMainWinLoc( strPage ) {
  parent.opener.document.location = strPage;
}
</script>
{/literal}

<table id="fiche" cellpadding="0" cellspacing="0">
  <tr>
    <td id="fiche_identite">
      <div class="civilite">
        {if $x.sexe}&bull;{/if}
        {$x.prenom} {if $x.epouse eq ""}{$x.nom}{else}{$x.epouse} ({$x.nom}){/if}
        {if $x.nickname} (aka {$x.nickname}){/if}&nbsp;
        <a href="vcard.php/{$x.forlife}.vcf?x={$x.forlife}">
          <img src="images/vcard.png" alt="Afficher la carte de visite" title="Afficher la carte de visite"/>
        </a>
        {if !$x.is_contact}
        <a href="javascript:x()"  onclick="chgMainWinLoc('{rel}/carnet/mescontacts.php?action=ajouter&amp;user={$x.forlife}')">
          <img src="images/ajouter.gif" alt="Ajouter à mes contacts" title="Ajouter à mes contacts" />
        </a>
        {else}
        <a href="javascript:x()"  onclick="chgMainWinLoc('{rel}/carnet/mescontacts.php?action=retirer&amp;user={$x.forlife}')">
          <img src="images/retirer.gif" alt="Retirer de mes contacts" title="Retirer de mes contacts" />
        </a>
        {/if}
        {perms level=admin}
        <a href="{rel}/admin/utilisateurs.php?login={$x.forlife}">
          <img src="images/admin.png" alt='admin' title="administrer user" />
        </a>
        {/perms}
      </div>
      <div class='maj'>
        Fiche mise à jour<br />
        le {$x.date|date_format}
      </div>
      <div class="contact">
        <div class='email'>
          {if $x.dcd}
          Décédé{if $x.sexe}e{/if} le {$x.deces|date_format}
          {elseif !$x.inscrit}
          Le compte de cette personne n'est pas actif (personne non inscrite ou exclue).
          {else}
          {if $virtualalias}
          <a href="mailto:{$virtualalias}">{$virtualalias}</a><br />
          {/if}
          <a href="mailto:{$x.bestalias}@polytechnique.org">{$x.bestalias}@polytechnique.org</a>
          {if $bestalias neq $x.forlife}<br />
          <a href="mailto:{$x.forlife}@polytechnique.org">{$x.forlife}@polytechnique.org</a>
          {/if}
          {/if}
        </div>
        <div class="mob">
          {if $x.mobile}<em class="intitule">Mobile : </em>{$x.mobile}<br />{/if}
        </div>
        <div class='spacer'></div>
      </div>
      <div class='formation'>
        {if $x.iso3166}
        <img src='{rel}/images/flags/{$x.iso3166}.gif' alt='{$x.nationalite}' height='14' title='{$x.nationalite}' />&nbsp;
        {/if}
        X {$x.promo}{if $x.applis_join}&nbsp;-&nbsp;Formation&nbsp;: {$x.applis_join|smarty:nodefaults}{/if}
        {if $x.is_referent}
        [<a href="fiche_referent.php?user={$x.forlife}" class='popup2'>Ma fiche référent</a>]
        {/if}
      </div>
    </td>
    <td rowspan="4" id='photo'>
      <img alt="Photo de {$x.forlife}" src="{$photo_url}" width="{$x.x}" height="{$x.y}" />
      {if $x.section}<em class="intitule">Section : </em><span>{$x.section}</span><br />{/if}
      {if $x.binets_join}<em class="intitule">Binet(s) : </em><span>{$x.binets_join}</span><br />{/if}
      {if $x.gpxs_join}<em class="intitule">Groupe(s) X : </em><span>{$x.gpxs_join|smarty:nodefaults}</span><br />{/if}
      {if $x.web}<em class="intitule">Site Web : </em><a href="{$x.web}" class='popup'>{$x.web}</a>{/if}
      {if $x.libre}<br /><em class="intitule">Commentaires : </em><br /><span>{$x.libre|nl2br}</span>{/if}
    </td>
  </tr>
  {if $x.adr}
  <tr>
    <td>
      <h2>Contact : </h2>
      {foreach from=$x.adr item="address" key="i"}
        {if $address.active}
          {assign var="address_titre" value="Mon adresse actuelle :"}
        {elseif $address.secondaire}
          {assign var="address_titre" value="Adresse secondaire :"}
        {else}
          {assign var="address_titre" value="Adresse principale :"}
        {/if}
        {include file="geoloc/address.tpl" address=$address titre_div=true titre=$address_titre}
      {/foreach}
      <div class="spacer">&nbsp;</div>
    </td>
  </tr>
  {/if}
  {if $x.adr_pro}
  <tr>
    <td>
      <h2>Informations professionnelles :</h2>
      {foreach from=$x.adr_pro item="address" key="i"}
      <div class="adresse">
        {if $address.entreprise}
        <div>
          <em>Entreprise/Organisme : </em> <strong>{$address.entreprise}</strong>
        </div>
        {/if}
        {if $address.secteur}
        <div>
          <em>Secteur : </em>
          <strong>{$address.secteur}{if $address.ss_secteur} ({$address.ss_secteur}){/if}</strong>
        </div>
        {/if}

        {if $address.fonction}
        <div>
          <em>Fonction : </em> <strong>{$address.fonction}</strong>
        </div>
        {/if}
        {if $address.poste}
        <div>
          <em>Poste : </em> <strong>{$address.poste}</strong>
        </div>
        {/if}
      </div>

      {include file="geoloc/address.tpl" address=$address titre="Adresse : "}
      <div class="spacer">&nbsp;</div>
      {/foreach}
    </td>
  </tr>
  {/if}
  {if $x.cv}
  <tr>
    <td>
      <h2>Curriculum Vitae :</h2>
      {$x.cv|nl2br}
    </td>
  </tr>
  {/if}
</table>


{* vim:set et sw=2 sts=2 sws=2: *}
