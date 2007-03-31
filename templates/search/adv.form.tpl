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

<h1>Recherche dans l'annuaire</h1>

{javascript name="jquery"}
{javascript name="jquery.autocomplete"}
<script type="text/javascript">{literal}
	// <!--
	function launch_form(url) {
	  var f = document.getElementById('recherche');
	  f.action = url;
	  f.submit();
	}
	function format_autocomplete(row) {
	  if (row[1] == 1) {
	    return row[0];
	  }
	  return row[0] + ' ('+ row[1] + ')';
	}
	$(document).ready(function() {
	      $(".autocomplete").each(function() {
	        $(this).autocomplete("search/autocomplete/"+this.name,{selectOnly:1,formatItem:format_autocomplete,matchSubset:0,width:$(this).width()});
	      });
	});
	-->
{/literal}</script>
<form id="recherche" action="search/adv" method="get">
  <table class="bicol" cellpadding="3" summary="Recherche">
    <tr>
      <th colspan="2">
        Recherche avancée [<a href="search">&lt;&lt;&lt;&nbsp;Recherche simple</a>]
      </th>
    </tr>
    <tr>
      <td>Nom</td>
      <td>
    	<input type="hidden" name="rechercher" value="Chercher"/>
    	<input type="submit" style="display:none"/>
        <input type="text" class="autocomplete" name="name" size="32" value="{$smarty.request.name}" />
      </td>
    </tr>
    <tr>
      <td>Prénom</td>
      <td>
        <input class="autocomplete" type="text" name="firstname" size="32" value="{$smarty.request.firstname}" />
      </td>
    </tr>
    <tr>
      <td>Surnom</td>
      <td>
        <input class="autocomplete" type="text" name="nickname" size="32" value="{$smarty.request.nickname}" />
      </td>
    </tr>
    <tr>
      <td>Promotion</td>
      <td>
        <select name="egal1">
          <option value="=" {if $smarty.request.egal1 eq "="}selected="selected"{/if}>&nbsp;=&nbsp;</option>
          <option value="&gt;=" {if $smarty.request.egal1 eq "&gt;="}selected="selected"{/if}>&nbsp;&gt;=&nbsp;</option>
          <option value="&lt;=" {if $smarty.request.egal1 eq "&lt;="}selected="selected"{/if}>&nbsp;&lt;=&nbsp;</option>
        </select>
        <input type="text" name="promo1" size="4" maxlength="4" value="{$smarty.request.promo1}" />
        &nbsp;ET&nbsp;
        <select name="egal2">
          <option value="=" {if $smarty.request.egal2 eq "="}selected="selected"{/if}>&nbsp;=&nbsp;</option>
          <option value="&gt;=" {if $smarty.request.egal2 eq "&gt;="}selected="selected"{/if}>&nbsp;&gt;=&nbsp;</option>
          <option value="&lt;=" {if $smarty.request.egal2 eq "&lt;="}selected="selected"{/if}>&nbsp;&lt;=&nbsp;</option>
        </select>
        <input type="text" name="promo2" size="4" maxlength="4" value="{$smarty.request.promo2}" />
      </td>
    </tr>
    <tr>
      <td>Sexe</td>
      <td>
        <table>
          <tr>
            <td style="width:100px">
              <input type="radio" name="woman" value="0" {if !$smarty.request.woman}checked="checked"{/if} />Indifférent
            </td>
            <td style="width:100px">
              <input type="radio" name="woman" value="1" {if $smarty.request.woman eq 1}checked="checked"{/if} />Homme
            </td>
            <td style="width:100px">
              <input type="radio" name="woman" value="2" {if $smarty.request.woman eq 2}checked="checked"{/if} />Femme
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>Sur polytechnique.org</td>
      <td>
        <table>
          <tr>
            <td style="width:100px">
              <input type="radio" name="subscriber" value="0" {if !$smarty.request.subscriber}checked="checked"{/if} />Indifférent
            </td>
            <td style="width:100px">
              <input type="radio" name="subscriber" value="1" {if $smarty.request.subscriber eq 1}checked="checked"{/if} />Inscrit
            </td>
            <td style="width:100px">
              <input type="radio" name="subscriber" value="2" {if $smarty.request.subscriber eq 2}checked="checked"{/if} />Non inscrit
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
              <input type="radio" name="alive" value="0" {if !$smarty.request.alive}checked="checked"{/if} />Indifférent
            </td>
            <td style="width:100px">
              <input type="radio" name="alive" value="1" {if $smarty.request.alive eq 1}checked="checked"{/if} />Vivant
            </td>
            <td style="width:100px">
              <input type="radio" name="alive" value="2" {if $smarty.request.alive eq 2}checked="checked"{/if} />Décédé
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <input type="checkbox" name="with_soundex" value="1" {if $smarty.request.with_soundex}checked="checked"{/if} id="sdxn" />
        <label for="sdxn">étendre par proximité sonore (uniquement sur nom et prénom)</label>
      </td>
    </tr>
    <tr>
      <th colspan="2">Géographie</th>
    </tr>
    <tr>
      <td>Ville</td>
      <td><input type="text" class="autocomplete" name="city" size="32" value="{$smarty.request.city}" /></td>
    </tr>
    <tr>
      <td>Pays</td>
      <td>
        <select name="country" onchange="return Ajax.update_html('region', '{#globals.baseurl#}/search/ajax/region/' + this.value);">
        {if $smarty.request.country}
          {assign var="country" value=$smarty.request.country}
        {else}
          {assign var="country" value=""}
        {/if}
        {geoloc_country country=$country available=true}
        </select>
      </td>
    </tr>
    <tr>
      <td>Région ou département</td>
      <td id="region">
        {if $smarty.request.region}
          {assign var="region" value=$smarty.request.region}
        {else}
          {assign var="region" value=""}
        {/if}
        {include file="search/adv.region.form.tpl" country=$smarty.request.country}
      </td>
    </tr>
    <tr>
      <td colspan="2">
      <label for="only_current"><input name="only_current" id="only_current" type="checkbox"{if $smarty.request.only_current}  
checked="checked"{/if}/>chercher uniquement les adresses où les camarades sont actuellement.</label></td>
    </tr>
    <tr>
      <th colspan="2">Activité</th>
    </tr>
    <tr>
      <td>Entreprise</td>
      <td><input type="text" class="autocomplete" name="entreprise" size="32" value="{$smarty.request.entreprise}" /></td>
    </tr>
    <tr>
      <td>Fonction</td>
      <td>
        <select name="fonction">
          <option value="0"></option>
          {iterate from=$choix_postes item=cp}
          <option value="{$cp.id}" {if $smarty.request.fonction eq $cp.id}selected{/if}>
            {$cp.fonction_fr}
          </option>
          {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td>Poste</td>
      <td><input type="text" class="autocomplete" name="poste" size="32" value="{$smarty.request.poste}" /></td>
    </tr>
    <tr>
      <td>Secteur</td>
      <td>
        <select name="secteur">
          <option value="0"></option>
          {iterate item=cs from=$choix_secteurs}
          <option value="{$cs.id}" {if $smarty.request.secteur eq $cs.id}selected{/if}>
            {$cs.label}
          </option>
          {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td>CV contient</td>
      <td><input type="text" name="cv" size="32" value="{$smarty.request.cv}" /></td>
    </tr>
    <tr>
      <td colspan="2">
        <input type='checkbox' name='only_referent' {if $smarty.request.only_referent}checked='checked'{/if} />
        chercher uniquement parmi les camarades se proposant comme référents
      </td>
    </tr>
    <tr>
      <th colspan="2">Divers</th>
    </tr>
    <tr>
      <td>Nationalité</td>
      <td>
        <select name="nationalite">
          {iterate from=$choix_nats item=cn}
          <option value="{$cn.id}" {if $smarty.request.nationalite eq $cn.id}selected="selected"{/if}>
            {$cn.text}
          </option>
          {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td>Binet</td>
      <td>
        <select name="binet">
        <option value="0"></option>
        {iterate item=cb from=$choix_binets}
          <option value="{$cb.id}" {if $smarty.request.binet eq $cb.id}selected="selected"{/if}>
            {$cb.text|htmlspecialchars}
          </option>
        {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td>Groupe X</td>
      <td>
        <select name="groupex">
        <option value="0"></option>
        {iterate item=cg from=$choix_groupesx}
          <option value="{$cg.id}" {if $smarty.request.groupex eq $cg.id}selected="selected"{/if}>
            {$cg.text}
          </option>
        {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td>Section</td>
      <td>
        <select name="section">
          {iterate item=cs from=$choix_sections}
          <option value="{$cs.id}" {if $smarty.request.section eq $cs.id}selected="selected"{/if}>
            {$cs.text}
          </option>
          {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td>Formation</td>
      <td>
        <select name="school" onchange="return Ajax.update_html('grade', '{#globals.baseurl#}/search/ajax/grade/' + this.value);">
          <option value="0"></option>
          {iterate item=cs from=$choix_schools}
          <option value="{$cs.id}" {if $smarty.request.school eq $cs.id}selected="selected"{/if}>
            {$cs.text|htmlspecialchars}
          </option>
          {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td></td>
      <td id="grade">
        {include file="search/adv.grade.form.tpl" grade=$smarty.request.diploma}
      </td>
    </tr>
    <tr>
      <td>Commentaire contient</td>
      <td><input type="text" name="free" size="32" value="{$smarty.request.free}" /></td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top: 1.5em">
        <div style="float: right">
          <input type="button" value="Chercher" onclick="launch_form('search/adv')"/>
        </div>
        {if $smarty.session.auth ge AUTH_COOKIE}
          <input type='checkbox' name='order' value='date_mod' {if $smarty.request.order eq "date_mod"}checked='checked'{/if} />
          mettre les fiches modifiées récemment en premier
        {/if}
      </td>
    </tr>
  </table>
</form>
<p class="center">
  <input type="button" value="Voir les résultats sur une carte" onclick="launch_form('geoloc/')"/>
</p>
<p>
  <strong>N.B.</strong> Le caractère joker * peut remplacer une ou plusieurs lettres dans les recherches.
</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
