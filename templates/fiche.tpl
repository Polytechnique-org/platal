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
        {$x.prenom} {if $x.nom_usage eq ""}{$x.nom}{else}{$x.nom_usage} ({$x.nom}){/if}
        {min_auth level="cookie"}
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
        <a href="javascript:x()" onclick="chgMainWinLoc('{rel}/admin/utilisateurs.php?login={$x.forlife}')">
          <img src="images/admin.png" alt='admin' title="administrer user" />
        </a>
        {/perms}
        {/min_auth}
      </div>
      {min_auth level="cookie"}
      <div class='maj'>
        Fiche mise à jour<br />
        le {$x.date|date_format}
      </div>
      {/min_auth}
      {if $logged || $c.mobile}
      <div class="contact">
        {min_auth level="cookie"}
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
        {/min_auth}
        <div class="mob">
          {if $x.mobile}<em class="intitule">Mobile : </em>{$x.mobile}<br />{/if}
        </div>
        <div class='spacer'></div>
      </div>
      {/if}
      <div class='formation'>
        {if $x.iso3166}
        <img src='{rel}/images/flags/{$x.iso3166}.gif' alt='{$x.nationalite}' height='14' title='{$x.nationalite}' />&nbsp;
        {/if}
        X {$x.promo}
        {if ($x.promo_sortie-3 > $x.promo)}
          - X {math equation="a-b" a=$x.promo_sortie b=3}
        {/if}
        {if $x.applis_join}
          &nbsp;-&nbsp;Formation&nbsp;: {$x.applis_join|smarty:nodefaults}
        {/if}
        {min_auth level="cookie"}
        {if $x.is_referent}
        [<a href="fiche_referent.php?user={$x.forlife}" class='popup2'>Ma fiche référent</a>]
        {/if}
        {/min_auth}
      </div>
    </td>
    <td rowspan="4" id='photo'>
      {if $photo_url}<img alt="Photo de {$x.forlife}" src="{$photo_url}" width="{$x.x}" height="{$x.y}" />{/if}
      {min_auth level="cookie"}
      {if $x.section}<div><em class="intitule">Section : </em><span>{$x.section}</span></div>{/if}
      {if $x.binets_join}<div><em class="intitule">Binet(s) : </em><span>{$x.binets_join}</span></div>{/if}
      {if $x.gpxs_join}<div><em class="intitule">Groupe(s) X : </em><span>{$x.gpxs_join|smarty:nodefaults}</span></div>{/if}
      {/min_auth}
      {if $x.web}<div><em class="intitule">Site Web : </em><a href="{$x.web}" class='popup'>{$x.web}</a></div>{/if}
      {if $x.freetext}<div><em class="intitule">Commentaires : </em><br /><span>{$x.freetext|nl2br}</span></div>{/if}
    </td>
  </tr>
  {if $x.adr}
  <tr>
    <td>
      <h2>Contact : </h2>
      {foreach from=$x.adr item="address" key="i"}
        {if $address.active}
        {include file="geoloc/address.tpl" address=$address titre_div=true titre="Mon adresse actuelle :"}
        {elseif $address.secondaire}
        {include file="geoloc/address.tpl" address=$address titre_div=true titre="Adresse secondaire :"}
        {else}
        {include file="geoloc/address.tpl" address=$address titre_div=true titre="Adresse principale :"}
        {/if}
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
      {include file="include/emploi.tpl" address=$address}
      {include file="geoloc/address.tpl" address=$address titre="Adresse : "}
      <div class="spacer">&nbsp;</div>
      {/foreach}
    </td>
  </tr>
  {/if}
  {if $x.medals}
  <tr>
    <td>
      <h2>Distinctions : </h2>
      {foreach from=$x.medals item=m}
      <table style="float: left; width: 33%;">
        <tr>
          <td>
            <img src="{rel}/images/medals/{$m.img}" width="24" alt="{$m.medal}" title="{$m.medal}" style='float: left;' />
          </td>
          <td>
            <strong>{$m.medal}</strong>
            <br />{$m.grade}
          </td>
        </tr>
      </table>
      {/foreach}
      <div class="spacer">&nbsp;</div>
    </td>
  </tr>
  {/if}
  {min_auth level="cookie"}
  {if $x.cv}
  <tr>
    <td>
      <h2>Curriculum Vitae :</h2>
      {$x.cv|nl2br}
    </td>
  </tr>
  {/if}
  {/min_auth}
</table>


{* vim:set et sw=2 sts=2 sws=2: *}
