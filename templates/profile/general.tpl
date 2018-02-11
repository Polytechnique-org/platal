{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<table class="bicol" style="margin-bottom: 1em" summary="Profil : Noms">
  <tr>
    <th colspan="3">Noms{if t($validation)} <small>(validations en attente de modération)</small>{/if}</th>
  </tr>
  <tr>
    <td class="titre">
      {icon name="flag_green" title="site public"}&nbsp;Affichage public
    </td>
    <td id="public_name">
      {$public_name}
    </td>
    <td>
      <a href="javascript:toggleNamesAdvanced({$viewPrivate});">
        {icon name="page_edit" title="Plus de détail"}
      </a>
    </td>
  </tr>
  {if $viewPrivate}
  <tr>
    <td class="titre">
      {icon name="flag_red" title="site privé"}&nbsp;Affichage privé
    </td>
    <td id="private_name">
      {$private_name}
    </td>
    <td></td>
  </tr>
  {/if}
  {if $isMe}
  <tr>
    <td>
      <span class="titre">Comment t'appeler</span><br />
      <span class="smaller">sur le site, dans la lettre mensuelle...</span>
    </td>
    <td>
      <input type="text" name="yourself" value="{$yourself}" size="25"/>
    </td>
    <td></td>
  </tr>
  {/if}
  <tr class="names_advanced_public" {if !$errors.search_names}style="display: none"{/if}>
    <td colspan="3">
      <span class="titre">Gestion des noms, prénoms, surnoms...</span>
      <span class="smaller">Ils déterminent la façon dont
      {if $isMe}ton{else}son{/if} nom apparaît sur les annuaires
      en ligne et papier et ta fiche apparaitra quand on cherche un de ces noms.</span><br/>
    </td>
  </tr>
  {include file="profile/general.public_names.tpl" names=$search_names.public_names}
  {foreach from=$search_names.private_names key=id item=name}
    {include file="profile/general.private_name.tpl"}
  {/foreach}
  <tr class="names_advanced_private" id="searchname" {if !$errors.search_names}style="display: none"{/if}>
    <td colspan="3">
      <div id="sn_add" class="center">
        <a href="javascript:addSearchName({$isFemale});">
          {icon name=add title="Ajouter un nom"} Ajouter un nom
        </a>
      </div>
    </td>
  </tr>
  <tr class="names_advanced_private" {if !$errors.search_names}style="display: none"{/if}>
    <td class="center" colspan="2">
      <small>Si la casse de ton nom est erronée et que tu n'arrives pas à la corriger,
      <a href="mailto:support@{#globals.mail.domain#}">contacte-nous</a>.</small>
    </td>
  </tr>
</table>

<table class="bicol" style="margin-bottom: 1em"
  summary="Profil&nbsp;: Informations générales">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <input type="checkbox" disabled="disabled" checked="checked" />
        {icon name="flag_green" title="site public"}
      </div>
      Informations générales
    </th>
  </tr>
  <tr>
    <td>
      <span class="titre">Promotion</span>
    </td>
    <td>
      {if !t($promo_choice)}
        <span class="nom">{$profile->promo()}</span>
        <input type="hidden" name="promo_display" value="{$profile->promo()}"/>
      {else}
        <select name="promo_display">
        {foreach from=$promo_choice item="promo_to_display"}
          <option value="{$promo_to_display}" {if $promo_to_display eq $promo_display}selected="selected"{/if}>{$promo_to_display}</option>
        {/foreach}
        </select>
      {/if}
      <span class="lien"><a href="javascript:togglePromotionEdition();" {popup text="pour les oranges"}>{icon name="page_edit" title="modifier"}</a></span>
    </td>
  </tr>
  <tr class="promotion_edition" style="display: none">
    <td colspan="2">
      {if $isMe}
      Afin de pouvoir être considéré{""|sex:"e":$profile} à la fois dans ta promotion d'origine et ta
      ou tes promotions d'adoption tu peux entrer ici ta promotion d'adoption.
      {else}
      Afin que ce{""|sex:"tte":$profile} camarade soit considé{""|sex:"e":$profile} à la fois dans sa 
      promotion d'origine et sa promotion d'adoption, tu peux entrer ici sa promotion d'adoption.
      {/if}
      <br /><span class="smaller"><span class="titre">Attention&nbsp;:</span>
      cette modification ne sera prise en compte qu'après validation par les administrateurs du site.</span>
    </td>
  </tr>
  <tr class="promotion_edition" style="display: none">
    <td class="titre">Promotion d'adoption</td>
    <td>
      {$profile->mainEducation()}<input type="text" name="promo" size="4" maxlength="4" value="{$promo}" />
      <span class="smaller"> (que les chiffres)</span>
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Date de naissance</span>
    </td>
    <td><input type="text" {if $errors.birthdate}class="error"{/if} name="birthdate" value="{$birthdate}" /></td>
  </tr>
  {if !$isMe}
  <tr>
    <td>
      <span class="titre">Date de décès</span>
    </td>
    <td><input type="text" {if $errors.deathdate}class="error"{/if} name="deathdate" value="{$deathdate}" /></td>
  </tr>
  <tr>
    <td>
      <span class="titre">Date de naissance de référence</span>
    </td>
    <td>
    {if hasPerm('admin') && !$is_registered}
      <input type="text" {if $errors.birthdate_ref}class="error"{/if} name="birthdate_ref" value="{$birthdate_ref}" />
    {else}
      {$birthdate_ref}
      <input type="hidden" name="birthdate_ref" value="{$birthdate_ref}" />
    {/if}
    </td>
  </tr>
  {/if}
  <tr>
    <td>
      <span class="titre">Nationalité</span>
    </td>
    <td>
      <select name="nationality1">
        {select_nat valeur=$nationality1 pad=1}
      </select>
      <a href="javascript:addNationality();">{icon name=add title="Ajouter une nationalité"}</a>
    </td>
  </tr>
  <tr id="nationality2" {if !$nationality2}style="display: none"{/if}>
    <td></td>
    <td>
      <select name="nationality2">
        {select_nat valeur=$nationality2 pad=1}
      </select>
      <a href="javascript:delNationality('2');">{icon name=cross title="Supprimer cette nationalité"}</a>
    </td>
  </tr>
  <tr id="nationality3" {if !$nationality3}style="display: none"{/if}>
    <td></td>
    <td>
      <select name="nationality3">
        {select_nat valeur=$nationality3 pad=1}
      </select>
      <a href="javascript:delNationality('3');">{icon name=cross title="Supprimer cette nationalité"}</a>
    </td>
  </tr>
  <tr>
    <td><span class="titre">Civilité</span></td>
    <td>
      <select name="profile_title">
        <option value="M" {if $profile_title eq "M"}selected="selected"{/if}>M</option>
        <option value="MLLE" {if $profile_title eq "MLLE"}selected="selected"{/if}>MLLE</option>
        <option value="MME" {if $profile_title eq "MME"}selected="selected"{/if}>MME</option>
      </select>
    </td>
  </tr>
</table>

<table class="bicol" style="margin-bottom: 1em" summary="Profil&nbsp;: Formations à l'X">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <input type="checkbox" disabled="disabled" checked="checked" />
        {icon name="flag_green" title="site public"}
      </div>
      Formations à l'École polytechnique
    </th>
  </tr>
  {foreach from=$main_edus key=eduid item=main_edu}
  {cycle values="impair, pair" assign=class}
  <tr class="{$class}">
    <td><span class="titre">Cycle&nbsp;:</span></td>
    <td>{$main_edu.cycle}</td>
  </tr>
  <tr class="{$class}">
    <td><span class="titre">Promotion&nbsp;:</span></td>
    <td>{if t($main_edu.promo_year)}{$main_edu.promo_year}{/if}</td>
  </tr>
  <tr class="{$class}">
    <td><span class="titre">Domaine de formation&nbsp;:</span></td>
    <td>
      <select name="main_edus[{$eduid}][fieldid]">
        <option value="">&nbsp;</option>
        {foreach from=$edu_fields item=field}
        <option value="{$field.id}" {if $field.id eq $main_edu.fieldid}selected="selected"{/if}>{$field.field}</option>
        {/foreach}
      </select>
    </td>
  </tr>
  <tr class="{$class}">
    <td><span class="titre">Description de la formation&nbsp;:</span></td>
    <td>
      <input type="text" name="main_edus[{$eduid}][program]" value="{$main_edu.program}" size="30" maxlength="255" />
      <input type="hidden" name="main_edus[{$eduid}][degreeid]" value="{$main_edu.degreeid}" />
      <input type="hidden" name="main_edus[{$eduid}][promo_year]" value="{$main_edu.promo_year}" />
      <input type="hidden" name="main_edus[{$eduid}][cycle]" value="{$main_edu.cycle}" />
    </td>
  </tr>
  {/foreach}
</table>

<table class="bicol" style="margin-bottom: 1em" summary="Profil&nbsp;: Formations">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <input type="checkbox" disabled="disabled" checked="checked" />
        {icon name="flag_green" title="site public"}
      </div>
      Formations
    </th>
  </tr>
  {foreach from=$edus key=eduid item=edu}
    {cycle values="impair, pair" assign=class}
    {include file="profile/general.edu.tpl" eduid=$eduid edu=$edu edu_fields=$edu_fields class=$class}
  {/foreach}
  {cycle values="impair, pair" assign=class}
  {assign var=eduaddid value=$edus|@count}
  <tr id="edu_add" class="edu_{$eduaddid} {$class}">
    <td colspan="2">
      <div class="center" style="clear: both; padding-top: 4px;">
        <a href="javascript:addEdu();">
          {icon name=add title="Ajouter une formation"} Ajouter une formation
        </a>
      </div>
    </td>
  </tr>
  <tr class="{$class}">
    <td class="center" colspan="2">
      <small>Si la formation que tu cherches ne figure pas dans la liste,
      <a href="mailto:support@{#globals.mail.domain#}">contacte-nous</a>.</small>
    </td>
  </tr>
 </table>

{if $viewPrivate || $isMe}
<table class="bicol"  style="margin-bottom: 1em"
  summary="Profil&nbsp;: Trombinoscope">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <label><input type="checkbox" name="photo_pub" {if $photo_pub eq 'public'}checked="checked" {/if}/>
        {icon name="flag_green" title="site public"}</label>
      </div>
      Trombinoscope
    </th>
  </tr>
  <tr>
    <td {if !$nouvellephoto}colspan="2"{/if} class="center" style="width: 49%">
      <div class="titre">Photo actuelle</div>
      <img src="photo/{$profile->hrid()}" alt=" [ PHOTO ] " style="max-height: 250px; margin-top: 1em" />
    </td>
    {if $nouvellephoto}
    <td class="center" style="width: 49%">
      <div class="titre">Photo en attente de validation</div>
      <div>
        <a href="profile/{$profile->hrid()}?modif=new" class="popup2">
          Ta fiche avec cette photo
        </a>
      </div>
      <img src="photo/{$profile->hrid()}/req" alt=" [ PHOTO ] " style="max-height: 250px; margin-top: 1em" />
    </td>
    {/if}
  </tr>
  <tr class="pair">
    <td colspan="2">
      Pour profiter de cette fonction intéressante, tu dois disposer
      quelque part (sur ton ordinateur ou sur Internet) d'une photo
      d'identité (dans un fichier au format JPEG, PNG ou GIF).<br />
      <div class="center">
        <a href="photo/change/{$profile->hrid()}">Éditer ta photo</a>
      </div>
    </td>
  </tr>
</table>
{/if}

<table class="bicol" style="margin-bottom: 1em"
  summary="Profil&nbsp;: Divers">
  <tr>
    <th colspan="2">
      Divers
    </th>
  </tr>
  <tr>
    <td colspan="2">
      <span class="titre">Téléphones personnels</span>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      {foreach from=$tels key=telid item=tel}
        <div id="tels_{$telid}" style="clear: both; padding-top: 4px; padding-bottom: 4px">
          {include file="profile/phone.tpl" prefname='tels' prefid='tels' telid=$telid tel=$tel}
        </div>
      {/foreach}
      {if $tels|@count eq 0}
        <div id="tels_0" style="clear: both; padding-top: 4px; padding-bottom: 4px">
          {include file="profile/phone.tpl" prefname='tels' preid='tels' telid=0 tel=0}
        </div>
      {/if}
      <div id="tels_add" class="center" style="clear: both; padding-top: 4px;">
        <a href="javascript:addTel('tels','tels',null,null,null);">
          {icon name=add title="Ajouter un téléphone"} Ajouter un téléphone
        </a>
      </div>
    </td>
  </tr>
  {if t($email_error)}
    {include file="include/emails.combobox.tpl" name="email_directory" val=$email_directory_error error=$email_error i="0"}
  {else}{include file="include/emails.combobox.tpl" name="email_directory" val=$email_directory error=false i="0"}{/if}
  <tr>
    <td colspan="2">
      <span class="titre">Messageries, networking et sites web</span>
    </td>
  </tr>
  {foreach from=$networking item=network key=id}
    {include file="profile/general.networking.tpl" nw=$network i=$id}
  {/foreach}
  <tr id="networking">
    <td colspan="2">
      <script type="text/javascript">//<![CDATA[
        var nw_list = new Array();
        {foreach from=$network_list item=network}
          nw_list['{$network.name}'] = {$network.type};
        {/foreach}
      //]]></script>
      <div id="nw_add" class="center">
        <a href="javascript:addNetworking();">
          {icon name=add title="Ajouter une adresse"} Ajouter une adresse
        </a>
      </div>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <span class="titre">Sports, loisirs, hobbies&hellip;</span>
    </td>
  </tr>
  {foreach from=$hobbies item=hobby key=id}
    {include file="profile/general.hobby.tpl" hobby=$hobby i=$id}
  {/foreach}
  <tr id="hobby">
    <td colspan="2">
      <div id="hobby_add" class="center">
        <a href="javascript:addHobby();">
          {icon name=add title="Ajouter un hobby"} Ajouter un hobby
        </a>
      </div>
    </td>
  </tr>
  {if $viewPrivate || $isMe}
  <tr class="pair">
    <td>
      <div>
        <span class="flags">
          <label><input type="checkbox" name="freetext_pub" {if $freetext_pub eq 'public'}checked="checked"{/if} />
          {icon name="flag_green" title="site public"}</label>
        </span>&nbsp;
        <span class="titre">Commentaire</span>
      </div>
      <div class="smaller" style="margin-top: 30px">
        <a href="wiki_help/notitle" class="popup3">
          {icon name=information title="Syntaxe wiki"} Voir la syntaxe wiki autorisée
        </a>
        <div class="center">
          <input type="submit" name="preview" value="Aperçu"
                  onclick="previewWiki('freetext', 'ft_preview', true, 'ft_preview'); return false;" />
        </div>
      </div>
    </td>
    <td>
      <div id="ft_preview" style="display: none"></div>
      <textarea name="freetext" {if $errors.freetext}class="error"{/if}
                id="freetext" rows="8" cols="50" >{$freetext}</textarea>
    </td>
  </tr>
  {/if}
  {if !t($isMe)}
  <tr class="pair">
    <td>
      <div>
        <span class="titre">Commentaire AX</span>
      </div>
    </td>
    <td>
     <div id="axft_preview" style="display: none"></div>
     <textarea name="axfreetext" {if $errors.axfreetext}class="error"{/if}
     id="axfreetext" rows="8" cols="50" >{$axfreetext}</textarea>
    </td>
  </tr>
  {/if}
</table>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
