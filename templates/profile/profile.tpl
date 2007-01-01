{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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
  if (parent.opener) {
    parent.opener.document.location = strPage;
  } else {
    document.location = strPage;
  }
}
</script>
{/literal}

{if $logged and $x.forlife eq $smarty.session.forlife}
[<a href="javascript:x()" onclick="chgMainWinLoc('profile/edit')">Modifier ma fiche</a>]
{/if}

<table id="fiche" cellpadding="0" cellspacing="0">
  <tr>
    <td id="fiche_identite">
      <div class="civilite">
        {if $x.sexe}&bull;{/if}
        {$x.prenom} {if $x.nom_usage eq ""}{$x.nom}{else}{$x.nom_usage} ({$x.nom}){/if}
        {if $logged}
        {if $x.nickname} (alias {$x.nickname}){/if}&nbsp;
        <a href="vcard/{$x.forlife}.vcf">{*
          *}{icon name=vcard title="Afficher la carte de visite"}</a>
        {if !$x.is_contact}
        <a href="javascript:x()"  onclick="chgMainWinLoc('carnet/contacts?action=ajouter&amp;user={$x.forlife}')">
          {icon name=add title="Ajouter à mes contacts"}</a>
        {else}
        <a href="javascript:x()"  onclick="chgMainWinLoc('carnet/contacts?action=retirer&amp;user={$x.forlife}')">
          {icon name=cross title="Retirer de mes contacts"}</a>
        {/if}
        {if $smarty.session.perms eq admin}
        <a href="javascript:x()" onclick="chgMainWinLoc('admin/user/{$x.forlife}')">
          {icon name=wrench title="administrer user"}</a>
        {/if}
        {/if}
      </div>
      {if $logged}
      <div class='maj'>
        Fiche mise à jour<br />
        le {$x.date|date_format}
      </div>
      {/if}
      {if $logged || $x.mobile}
      <div class="contact">
        {if $logged}
        <div class='email'>
          {if $x.dcd}
          Décédé{if $x.sexe}e{/if} le {$x.deces|date_format}
          {elseif !$x.actif}
          Ce camarade n'a plus d'adresse redirection valide,<br />
          <a href="marketing/broken/{$x.user_id}" class="popup">clique ici si tu connais son adresse email !</a>
          {elseif !$x.inscrit}
          Cette personne n'est pas inscrite à Polytechnique.org,<br />
          <a href="marketing/public/{$x.user_id}" class="popup">clique ici si tu connais son adresse email !</a>
          {else}
          {if $virtualalias}
          <a href="mailto:{$virtualalias}">{$virtualalias}</a><br />
          {/if}
          <a href="mailto:{$x.bestalias}@{#globals.mail.domain#}">{$x.bestalias}@{#globals.mail.domain#}</a>
          {if $x.bestalias neq $x.forlife}<br />
          <a href="mailto:{$x.forlife}@{#globals.mail.domain#}">{$x.forlife}@{#globals.mail.domain#}</a>
          {/if}
          {/if}
        </div>
        {/if}
        <div class="mob">
          {if $x.mobile}<em class="intitule">Mobile : </em>{$x.mobile}<br />{/if}
        </div>
        <div class='spacer'></div>
      </div>
      {/if}
      <div class='formation'>
        {if $x.iso3166}
        <img src='images/flags/{$x.iso3166}.gif' alt='{$x.nationalite}' height='11' title='{$x.nationalite}' />&nbsp;
        {/if}
        X {$x.promo}
        {if ($x.promo_sortie-3 > $x.promo)}
          - X {math equation="a-b" a=$x.promo_sortie b=3}
        {/if}
        {if $x.applis_join}
          &nbsp;-&nbsp;Formation&nbsp;: {$x.applis_join|smarty:nodefaults}
        {/if}
        {if $logged}
        {if $x.is_referent}
        [<a href="referent/{$x.forlife}" class='popup2'>Ma fiche référent</a>]
        {/if}
        {/if}
      </div>
    </td>
    <td rowspan="4" id='photo'>
      {if $photo_url}<img alt="Photo de {$x.forlife}" src="{$photo_url}" width="{$x.x}" height="{$x.y}" />{/if}
      {if $logged}
      {if $x.section}<div><em class="intitule">Section : </em><span>{$x.section}</span></div>{/if}
      {if $x.binets_join}<div><em class="intitule">Binet(s) : </em><span>{$x.binets_join}</span></div>{/if}
      {if $x.gpxs_join}<div><em class="intitule">Groupe(s) X : </em><span>{$x.gpxs_join|smarty:nodefaults}</span></div>{/if}
      {/if}
      {if $x.web}<div><em class="intitule">Site Web : </em><a href="{$x.web}" class='popup'>{$x.web}</a></div>{/if}
      {if $x.freetext}<div><em class="intitule">Commentaires : </em><br /><span>{$x.freetext|nl2br|smarty:nodefaults}</span></div>{/if}
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
            <img src="images/medals/{$m.img}" width="24" alt="{$m.medal}" title="{$m.medal}" style='float: left;' />
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
  {if $logged}
  {if $x.cv}
  <tr>
    <td>
      <h2>Curriculum Vitae :</h2>
      {$x.cv|nl2br}
    </td>
  </tr>
  {/if}
  {/if}
{if !$logged}
<tr><td colspan="2">Cette fiche est publique et visible par tout internaute,
vous pouvez aussi voir <a href="profile/private/{$x.forlife}">celle&nbsp;réservée&nbsp;aux&nbsp;X</a>.
</td></tr>
{/if}

</table>

{* vim:set et sw=2 sts=2 sws=2: *}
