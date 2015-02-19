{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<h1>Recherche dans l'annuaire</h1>

{if hasPerm('edit_directory,admin') && t($suggestAddresses)}
<p class="center"><strong>Voulez-vous télécharger le <a href="{$globals->baseurl}/search/adv/addresses{$plset_args}">tableau des adresses postales</a> pour la recette précédente&nbsp;?</strong></p>
{/if}

<script type="text/javascript">//<![CDATA[
  {literal}$(function() { load_advanced_search({{/literal}{foreach from=$smarty.request key=key item=item name="load"}"{$key}":"{$item}"{if not $smarty.foreach.load.last},{/if}{/foreach}{literal}}); });{/literal}
//]]></script>

<p class="center">[<a href="search">Revenir à la recherche simple</a>]</p>
<form id="recherche" action="search/adv" method="get" onsubmit="return cleanForm(this, 'search/adv')">
  <table class="bicol" cellpadding="3" summary="Recherche">
    <tr>
      <th colspan="2">
        Recherche avancée
      </th>
    </tr>
    <tr>
      <td>Nom, prénom, surnom...</td>
      <td>
        <input type="hidden" name="rechercher" value="Chercher"/>
        <input type="submit" style="display:none"/>
        <input type="text" name="name" size="32" value="{$smarty.request.name}" />
        <select name="name_type">
          <option value="" {if $smarty.request.name_type eq ''}selected="selected"{/if}>&nbsp;-&nbsp;</option>
          <option value="lastname" {if $smarty.request.name_type eq 'lastname'}selected="selected"{/if}>nom</option>
          <option value="firstname" {if $smarty.request.name_type eq 'firstname'}selected="selected"{/if}>prénom</option>
          <option value="nickname" {if $smarty.request.name_type eq 'nickname'}selected="selected"{/if}>surnom</option>
        </select>
      </td>
    </tr>
    <tr>
      <td>Promotion</td>
      <td>
        {include file="include/select_promo.tpl" promo_data=$smarty.request egal1="egal1" egal2="egal2" promo1="promo1" promo2="promo2" edu_type="edu_type"}
      </td>
    </tr>
    <tr>
      <td>Sexe</td>
      <td>
        <table>
          <tr>
            <td style="width:100px">
              <input type="radio" name="woman" value="0" {if !$smarty.request.woman}checked="checked"{/if} id="woman0"/><label for="woman0">indifférent</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="woman" value="1" {if $smarty.request.woman eq 1}checked="checked"{/if} id="woman1"/><label for="woman1">homme</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="woman" value="2" {if $smarty.request.woman eq 2}checked="checked"{/if} id="woman2"/><label for="woman2">femme</label>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>Sur Polytechnique.org</td>
      <td>
        <table>
          <tr>
            <td style="width:100px">
              <input type="radio" name="subscriber" value="0" {if !$smarty.request.subscriber}checked="checked"{/if} id="subscriber0"/><label for="subscriber0">indifférent</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="subscriber" value="1" {if $smarty.request.subscriber eq 1}checked="checked"{/if} id="subscriber1"/><label for="subscriber1">inscrit</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="subscriber" value="2" {if $smarty.request.subscriber eq 2}checked="checked"{/if} id="subscriber2"/><label for="subscriber2">non inscrit</label>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>A une redirection active</td>
      <td>
        <table>
          <tr>
            <td style="width:100px">
              <input type="radio" name="has_email_redirect" value="0" {if !$smarty.request.has_email_redirect}checked="checked"{/if}
                id="has_email_redirect0" /><label for="has_email_redirect0">indifférent</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="has_email_redirect" value="1" {if $smarty.request.has_email_redirect eq 1}checked="checked"{/if}
                id="has_email_redirect1" /><label for="has_email_redirect1">oui</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="has_email_redirect" value="2" {if $smarty.request.has_email_redirect eq 2}checked="checked"{/if}
                id="has_email_redirect2" /><label for="has_email_redirect2">non</label>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>En vie</td>
      <td>
        <table>
          <tr>
            <td style="width:100px">
              <input type="radio" name="alive" value="0" {if !$smarty.request.alive}checked="checked"{/if} id="alive0"/><label for="alive0">indifférent</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="alive" value="1" {if $smarty.request.alive eq 1}checked="checked"{/if} id="alive1"/><label for="alive1">vivant</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="alive" value="2" {if $smarty.request.alive eq 2}checked="checked"{/if} id="alive2"/><label for="alive2">décédé</label>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <input type="checkbox" name="with_soundex" value="1" {if $smarty.request.with_soundex}checked="checked"{/if} id="sdxn" />
        <label for="sdxn">Étendre par proximité sonore (uniquement sur nom et prénom).</label>
      </td>
    </tr>
    <tr>
      <th colspan="2">Géographie</th>
    </tr>
    <tr>
      <td colspan="2" class="center"><small>Seuls les lieux où résident des camarades sont proposés ci-dessous.</small></td>
    </tr>
    {include file="search/adv.form.autocomplete_select.tpl" description="Pays" name="country"
      value_text=$smarty.request.country_text value=$smarty.request.country title="Tous les pays"}
    {include file="search/adv.form.address_component.tpl" description="Région, province, état…" name="administrative_area_level_1"
      value=$smarty.request.administrative_area_level_1}
    {include file="search/adv.form.address_component.tpl" description="Département, comté…" name="administrative_area_level_2"
      value=$smarty.request.administrative_area_level_2}
    <tr id="locality_text">
      <td>Ville</td>
      <td><input type="text" class="autocomplete" name="locality_text" size="32" value="{$smarty.request.locality_text}" /></td>
    </tr>
    {include file="search/adv.form.address_component.tpl" description="Ville" name="locality" value=$smarty.request.locality}
    {include file="search/adv.form.address_component.tpl" description="Code postal" name="postal_code" value=$smarty.request.postal_code}
    <tr>
      <td colspan="2">
        <label for="only_current">
          <input name="only_current" id="only_current" type="checkbox"{if $smarty.request.only_current} checked="checked"{/if}/>
          Chercher uniquement parmi les adresses actuelles.
        </label>
      </td>
    </tr>
    {if hasPerm('admin,edit_directory')}
    <tr>
      <td colspan="2">
        <label for="only_best_mail">
          <input name="only_best_mail" id="only_best_mail" type="checkbox"{if $smarty.request.only_best_mail} checked="checked"{/if}/>
          Chercher uniquement parmi les adresses postales utilisées lors de l'envoi de courrier.
        </label>
      </td>
    </tr>
    {/if}
    <tr>
      <th colspan="2">Activité</th>
    </tr>
    <tr>
      <td>Entreprise</td>
      <td><input type="text" class="autocomplete" name="entreprise" size="32" value="{$smarty.request.entreprise}" /></td>
    </tr>
    <tr>
      <td>Description</td>
      <td><input type="text" class="autocomplete" name="jobdescription" size="32" value="{$smarty.request.jobdescription}" /></td>
    </tr>
    {include file="search/adv.form.autocomplete_select.tpl" description="Mots-clefs" name="jobterm"
      value_text=$smarty.request.jobterm_text value=$smarty.request.jobterm title="Tous les mots-clefs"}
    {if hasPerm('directory_private')}
    <tr>
      <td>CV contient</td>
      <td><input type="text" name="cv" size="32" value="{$smarty.request.cv}" /></td>
    </tr>
    {/if}
    <tr>
      <td colspan="2">
        <input type='checkbox' name='only_referent' {if $smarty.request.only_referent}checked='checked'{/if} id="only_referent"/>
        <label for="only_referent">Chercher uniquement parmi les camarades se proposant comme référents.</label>
      </td>
    </tr>
    <tr>
      <th colspan="2">Divers</th>
    </tr>
    {include file="search/adv.form.autocomplete_select.tpl" description="Nationalité" name="nationalite"
      value_text=$smarty.request.nationalite_text value=$smarty.request.nationalite title="Toutes les nationalités"}
    {if hasPerm('directory_private')}
    {include file="search/adv.form.autocomplete_select.tpl" description="Binet" name="binet"
      value_text=$smarty.request.binet_text value=$smarty.request.binet title="Tous les binets"}
    {/if}
    {include file="search/adv.form.autocomplete_select.tpl" description="Groupe X" name="groupex"
      value_text=$smarty.request.groupex_text value=$smarty.request.groupex title="Tous les groupes X"}
    {if hasPerm('directory_private')}
    {include file="search/adv.form.autocomplete_select.tpl" description="Section" name="section"
      value_text=$smarty.request.section_text value=$smarty.request.section title="Toutes les sections"}
    {/if}
    {include file="search/adv.form.autocomplete_select.tpl" description="Formation" name="school"
      value_text=$smarty.request.school_text value=$smarty.request.school title="Toutes les formations"}
    <tr>
      <td>Diplôme</td>
      <td>
        <input name="diploma" size="32" value="{$smarty.request.diploma}"/>
      </td>
    </tr>
    <tr>
      <td>Corps d'origine</td>
      <td>
        <select name="origin_corps">
        {foreach from=$origin_corps_list key=id item=corps}
          <option value="{$id}" {if $smarty.request.origin_corps eq $id}selected="selected"{/if}>{$corps}</option>
        {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <td>Corps actuel</td>
      <td>
        <select name="current_corps">
        {foreach from=$current_corps_list key=id item=corps}
          <option value="{$id}" {if $smarty.request.current_corps eq $id}selected="selected"{/if}>{$corps}</option>
        {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <td>Grade</td>
      <td>
        <select name="corps_rank">
        {foreach from=$corps_rank_list key=id item=corps}
          <option value="{$id}" {if $smarty.request.corps_rank eq $id}selected="selected"{/if}>{$corps}</option>
        {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <td>Commentaire contient</td>
      <td><input type="text" name="free" size="32" value="{$smarty.request.free}" /></td>
    </tr>
    <tr>
      <td>Numéro de téléphone</td>
      <td><input type="text" name="phone_number" size="32" value="{$smarty.request.phone_number}"/></td>
    </tr>
    <tr>
      <td style="vertical-align: middle">
        <span>Networking et sites webs</span>
      </td>
      <td>
        <table>
          <tr>
            <td style="padding-left: 0px;">
              <input type="text" name="networking_address" size="32" value="{$smarty.request.networking_address}" />
            </td>
            <td>
              <select name="networking_type">
              {foreach from=$networking_types key=id item=network}
                <option value="{$id}" {if $smarty.request.networking_type eq $id}selected="selected"{/if}>{$network}</option>
              {/foreach}
              </select>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    {if hasPerm('admin,edit_directory')}
    <tr>
      <td>Matricule AX</td>
      <td>
        <textarea name="schoolid_ax" rows="10" cols="12">{$smarty.request.schoolid_ax}</textarea>
        <br />
        <i>Entrer une liste de matricules AX (un par ligne)</i>
      </td>
    </tr>
    {/if}
    {if $smarty.session.auth ge AUTH_COOKIE}
    <tr>
      <td colspan="2">
          <input type='checkbox' name='order' value='date_mod' {if $smarty.request.order eq "date_mod"}checked='checked'{/if} id="order"/>
          <label for="order">Mettre les fiches modifiées récemment en premier.</label>
      </td>
    </tr>
    <tr>
      <td colspan="2">
           <input type='checkbox' name='exact' id="exact" {if $smarty.request.exact}checked='checked'{/if} value='1'/>
           <label for="exact">Faire une recherche exacte.</label>
      </td>
    </tr>
    {/if}
    {if hasPerm('admin,edit_directory')}
    <tr>
      <td colspan="2">
           <label><input type="checkbox" id="addresses_dump" onclick="addressesDump();" />Tableau des adresses postales.</label>
      </td>
    </tr>
    {/if}
    <tr><td colspan="2"></td></tr>
    <tr>
      <td colspan="2" style="text-align: center">
          <input type="submit" value="Chercher" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
