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
<script type="text/javascript">//<![CDATA[
function chgMainWinLoc(strPage)
{
  strPage = platal_baseurl + strPage;
  if (parent.opener) {
    try {
      parent.opener.document.location = strPage;
      window.close();
    } catch(e) {
      window.open(strPage);
    }
  } else {
    document.location = strPage;
  }
}
//]]></script>
{/literal}

{if $logged and $x.forlife eq $smarty.session.forlife}
[<a href="javascript:chgMainWinLoc('profile/edit')">Modifier ma fiche</a>]
{/if}

<div id="fiche">
  <div id="photo" class="part">
    {if $photo_url}<img alt="Photo de {$x.forlife}" src="{$photo_url}" width="{$x.x}"/>{/if}
    {if $logged}
      {if $x.section}<div><em class="intitule">Section : </em><span>{$x.section}</span></div>{/if}
      {if $x.binets_join}<div><em class="intitule">Binet(s) : </em><span>{$x.binets_join}</span></div>{/if}
      {if $x.gpxs_join}<div><em class="intitule">Groupe(s) X : </em><span>{$x.gpxs_join|smarty:nodefaults}</span></div>{/if}
    {/if}
    {if $x.web}<div><em class="intitule">Site Web : </em><br /><a href="{$x.web}" class='popup'>{$x.web}</a></div>{/if}
    {if $x.freetext}<div><em class="intitule">Commentaires : </em><br /><span>{$x.freetext|smarty:nodefaults}</span></div>{/if}
  </div>
  <div id="fiche_identite" class="part">
    <div class="civilite">
      {if $x.sexe}&bull;{/if}
      {$x.prenom} {if $x.nom_usage eq ""}{$x.nom}{else}{$x.nom_usage} ({$x.nom}){/if}
      {if $logged}
      {if $x.nickname} (alias {$x.nickname}){/if}&nbsp;
      <a href="vcard/{$x.forlife}.vcf">{*
        *}{icon name=vcard title="Afficher la carte de visite"}</a>
      {if !$x.is_contact}
      <a href="javascript:chgMainWinLoc('carnet/contacts?action=ajouter&amp;user={$x.forlife}')">
        {icon name=add title="Ajouter à mes contacts"}</a>
      {else}
      <a href="javascript:chgMainWinLoc('carnet/contacts?action=retirer&amp;user={$x.forlife}')">
        {icon name=cross title="Retirer de mes contacts"}</a>
      {/if}
      {if $smarty.session.perms->hasFlag('admin')}
      <a href="javascript:chgMainWinLoc('admin/user/{$x.forlife}')">
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
        <a href="marketing/broken/{$x.forlife}" class="popup">clique ici si tu connais son adresse email !</a>
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
      {if $x.mobile}
      <div class="mob">
        <em class="intitule">Mobile : </em>{$x.mobile}
      </div>
      {/if}
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
      {if $logged && $x.is_referent}
      [<a href="referent/{$x.forlife}" class='popup2'>Ma fiche référent</a>]
      {/if}
    </div>
  </div>
  {if $x.adr}
  <div class="part">
    <h2>Contact : </h2>
    {foreach from=$x.adr item="address" key="i" name=adresses}
      {if $i is odd}
        {assign var=pos value="right"}
      {else}
        {assign var=pos value="left"}
      {/if}
      {if $address.active}
      {include file="geoloc/address.tpl" address=$address titre_div=true titre="Mon adresse actuelle :"
               for="`$x.prenom` `$x.nom`" pos=$pos}
      {elseif $address.secondaire}
      {include file="geoloc/address.tpl" address=$address titre_div=true titre="Adresse secondaire :"
               for="`$x.prenom` `$x.nom`" pos=$pos}
      {else}
      {include file="geoloc/address.tpl" address=$address titre_div=true titre="Adresse principale :"
               for="`$x.prenom` `$x.nom`" pos=$pos}
      {/if}
      {if $i is odd}<div class="spacer"></div>{/if}
    {/foreach}
  </div>
  {/if}
  {if $x.adr_pro}
  <div class="part">
    <h2>Informations professionnelles :</h2>
    {foreach from=$x.adr_pro item="address" key="i"}
      {if $i neq 0}<hr />{/if}
      {include file="include/emploi.tpl" address=$address}
      {include file="geoloc/address.tpl" address=$address titre="Adresse : " for=$address.entreprise pos="left"}
      <div class="spacer">&nbsp;</div>
    {/foreach}
  </div>
  {/if}
  {if $x.medals}
  <div class="part">
    <h2>Distinctions : </h2>
    {foreach from=$x.medals item=m}
    <div class="medal_frame">
      <img src="images/medals/{$m.img}" width="24" alt="{$m.medal}" title="{$m.medal}" style='float: left;' />
      <div class="medal_text">
        {$m.medal}<br />{$m.grade}
      </div>
    </div>
    {/foreach}
    <div class="spacer">&nbsp;</div>
  </div>
  {/if}
  {if $logged && $x.cv}
  <div class="part">
    <h2>Curriculum Vitae :</h2>
    {$x.cv|smarty:nodefaults}
  </div>
  {/if}
  {if !$logged}
  <div class="part">
    Cette fiche est publique et visible par tout internaute,<br />
    vous pouvez aussi voir <a href="profile/private/{$x.forlife}?display=light">celle&nbsp;réservée&nbsp;aux&nbsp;X</a>.
  </div>
  {/if}
  <div class="spacer"></div>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
