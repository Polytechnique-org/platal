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
        $Id: fiche.tpl,v 1.28 2004-11-17 18:16:26 x2000habouzit Exp $
 ***************************************************************************}

{literal}
<script type="text/javascript">
function chgMainWinLoc( strPage ) {
  parent.opener.document.location = strPage;
}
</script>
{/literal}
{dynamic}

<table id="fiche" cellpadding="0" cellspacing="0">
  <tr>
    <td id="fiche_identite">
      <div class="civilite">
        {$prenom} {if $epouse eq ""}{$nom}{else}{$epouse} ({$nom}){/if}&nbsp;
        <a href="vcard.php/{$forlife}.vcf?x={$user_id}">
          <img src="images/vcard.png" alt="Afficher la carte de visite" title="Afficher la carte de visite"/>
        </a>
        {if !$is_contact}
        <a href="javascript:x()"  onclick="chgMainWinLoc('{"carnet/mescontacts.php"|url}?action=ajouter&amp;user={$forlife}')">
          <img src="images/ajouter.gif" alt="Ajouter à mes contacts" title="Ajouter à mes contacts" />
        </a>
        {else}
        <a href="javascript:x()"  onclick="chgMainWinLoc('{"carnet/mescontacts.php"|url}?action=retirer&amp;user={$forlife}')">
          <img src="images/retirer.gif" alt="Retirer de mes contacts" title="Retirer de mes contacts" />
        </a>
        {/if}
        {perms level=admin}
        <a href="{"admin/utilisateurs.php"|url}?login={$forlife}">
          <img src="images/admin.png" alt='admin' title="administrer user" />
        </a>
        {/perms}
      </div>
      <div class='maj'>
        Fiche mise à jour<br />
        le {$date|date_format:"%d %b. %Y"}
      </div>
      <div class="contact">
        <div class='email'>
          <a href="mailto:{$bestalias}@polytechnique.org">{$bestalias}@polytechnique.org</a>
          {if $bestalias neq $forlife}<br />
          <a href="mailto:{$forlife}@polytechnique.org">{$forlife}@polytechnique.org</a>
          {/if}
        </div>
        <div class="mob">
          {if $mobile}<em class="intitule">Mobile : </em>{$mobile}<br />{/if}
        </div>
        <div class='spacer'></div>
      </div>
      <div class='formation'>
        {if $iso3166}
        <img src='{"images/"|url}flags/{$iso3166}.gif' alt='{$nationalite}' height='14' title='{$nationalite}' />&nbsp;
        {/if}
        X {$promo}&nbsp;-&nbsp;Formation&nbsp;: {$applis|smarty:nodefaults}
        {if $is_referent}
        <a href="fiche_referent.php?user={$forlife}" class='popup'>Ma fiche référent</a>
        {/if}
      </div>
    </td>
    <td rowspan="4" id='photo'>
      <img alt="Photo de {$forlife}" src="{$photo_url}" width="{$size_x}" height="{$size_y}" />
      {if $section}<em class="intitule">Section : </em><span>{$section}</span><br />{/if}
      {if $binets}<em class="intitule">Binet(s) : </em><span>{$binets}</span><br />{/if}
      {if $groupes}<em class="intitule">Groupe(s) X : </em><span>{$groupes|smarty:nodefaults}</span><br />{/if}
      {if $web}<em class="intitule">Site Web : </em><a href="{$web}" class='popup'>{$web}</a>{/if}
      {if $libre}<br /><em class="intitule">Commentaires : </em><br /><span>{$libre|nl2br}</span>{/if}
    </td>
  </tr>
  {if $adr|@count > 0}
  <tr>
    <td>
      <h2>Contact : </h2>
      {foreach from=$adr item="address" key="i"}
      <div class="adresse">
        <div class="titre">{$address.title}</div>
        {if $address.adr1 || $address.pays || $address.ville}
        <div>
          {if $address.adr1}<strong>{$address.adr1}</strong><br />{/if}
          {if $address.adr2}<strong>{$address.adr2}</strong><br />{/if}
          {if $address.adr3}<strong>{$address.adr3}</strong><br />{/if}
          {if $address.ville}<strong>{$address.cp} {$address.ville}</strong><br />{/if}
          {if $address.pays}
          <strong>{$address.pays}{if $address.region} ({$address.region}){/if}</strong>
          {/if}
        </div>
        {/if}

        {if $address.tel}
        <div>
          <em class="small">Tél :</em> <strong class="value">{$address.tel}</strong>
        </div>
        {/if}

        {if $address.fax}
        <div>
          <em class="small">Fax :</em> <strong class="value">{$address.fax}</strong>
        </div>
        {/if}
      </div>
      {/foreach}
      <div class="spacer">&nbsp;</div>
    </td>
  </tr>
  {/if}
  {if $adr_pro|@count > 0}
  <tr>
    <td>
      <h2>Informations professionnelles :</h2>
      {foreach from=$adr_pro item="address" key="i"}
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

      <div class="adresse">
        {if $address.adr1 || $address.pays || $address.ville}
        <em>Adresse : </em><br />
        {if $address.adr1}<strong>{$address.adr1}</strong><br />{/if}
        {if $address.adr2}<strong>{$address.adr2}</strong><br />{/if}
        {if $address.adr3}<strong>{$address.adr3}</strong><br />{/if}
        {if $address.ville}<strong>{$address.cp} {$address.ville}</strong><br />{/if}
        {if $address.pays}
        <strong>{$address.pays}{if $address.region} ({$address.region}){/if}</strong>
        {/if}
        {/if}

        {if $address.tel}
        <div>
          <em>Tél : </em>
          <strong>{$address.tel}</strong>
        </div>
        {/if}

        {if $address.fax}
        <div>
          <em>Fax : </em>
          <strong>{$address.fax}</strong>
        </div>
        {/if}
      </div>
      <div class="spacer">&nbsp;</div>
      {/foreach}
    </td>
  </tr>
  {/if}
  {if $cv}
  <tr>
    <td>
      <h2>Curriculum Vitae :</h2>
      {$cv|nl2br}
    </td>
  </tr>
  {/if}
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
