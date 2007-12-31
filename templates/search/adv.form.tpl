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

<h1>Recherche dans l'annuaire</h1>

{javascript name="jquery"}
{javascript name="jquery.autocomplete"}
<script type="text/javascript">// <!-- 
        var baseurl = platal_baseurl + "search/";
        {literal}
        // display an autocomplete row&nbsp;: blabla (nb of found matches)
        function make_format_autocomplete(block) {
          return function(row) {
              regexp = new RegExp('(' + RegExp.escape(block.value) + ')', 'i');
              name = row[0].replace(regexp, '<strong>$1</strong>');
              if (row[1] == 1) {
                return name;
              }
              return name + '<em>&nbsp;&nbsp;-&nbsp;&nbsp;'+ row[1] + ' camarades</em>';
            };
        }
        
        // when changing country, open up region choice
        function changeCountry(a2) {
          $(".autocompleteTarget[@name='country']").attr('value',a2);
          if (a2) {
            $(".autocomplete[@name='countryTxt']").addClass('hidden_valid');
            $("[@name='region']").parent().load(baseurl + 'list/region/', { country:a2 }, function() {
              if ($("select[@name='region']").children("option").size() > 1) {
                $("select[@name='region']").attr('value', '{/literal}{$smarty.request.region}{literal}');
                $("tr#region_ln").show();
              } else {
                $("select[@name='region']").attr('value', '');
                $("tr#region_ln").hide();
              }
            });
          } else {
            $(".autocomplete[@name='countryTxt']").removeClass('hidden_valid');
            $("select[@name='region']").attr('value', '');
            $("tr#region_ln").hide();
          }
        }
        
        // when changing school, open diploma choice
        function changeSchool(schoolId) {
          $(".autocompleteTarget[@name='school']").attr('value',schoolId);
          if (schoolId) {
            $(".autocomplete[@name='schoolTxt']").addClass('hidden_valid');
            $("[@name='diploma']").parent().load(baseurl + 'list/diploma/', { school:schoolId }, function() {
              if ($("select[@name='diploma']").children("option").size() > 1) {
                $("select[@name='diploma']").attr('value', '{/literal}{$smarty.request.diploma}{literal}');
                $("tr#diploma_ln").show();
              } else {
                $("select[@name='diploma']").attr('value', '');
                $("tr#diploma_ln").hide();
              }
            });
          } else {
            $(".autocomplete[@name='schoolTxt']").removeClass('hidden_valid');
            $("select[@name='diploma']").attr('value', '');
            $("tr#diploma_ln").hide();
          }
        }
        
        // when choosing autocomplete from list, must validate
        function select_autocomplete(name) {
          nameRealField = name.replace(/Txt$/, '');
          // nothing to do if field is not a text field for a list
          if (nameRealField == name)
            return null;
          // if changing country, might want to open region choice
          if (nameRealField == 'country')
            return function(i) {
                changeCountry(i.extra[1]);
              }
          if (nameRealField == 'school')
            return function(i) {
                changeSchool(i.extra[1]);
              }
          // change field in list and display text field as valid
          return function(i) {
              nameRealField = this.field.replace(/Txt$/, '');
              $(".autocompleteTarget[@name='"+nameRealField+"']").attr('value',i.extra[1]);
              $(".autocomplete[@name='"+this.field+"']").addClass('hidden_valid');
            }
          }
          $(document).ready(function() {
            $(".autocompleteTarget").hide();
            $(".autocomplete").show().each(function() {
              targeted = $("../.autocompleteTarget",this)[0];
              if (targeted && targeted.value) {
                me = $(this);
                $.get(baseurl + 'list/'+ targeted.name +'/'+targeted.value, {},function(textValue) {
                  me.attr('value', textValue);
                  me.addClass('hidden_valid');
                });
              }
              $(this).autocomplete(baseurl + "autocomplete/"+this.name,{
                selectOnly:1,
                formatItem:make_format_autocomplete(this),
                field:this.name,
                onItemSelect:select_autocomplete(this.name),
                matchSubset:0,
                width:$(this).width()});
              });
              $(".autocomplete").change(function() { $(this).removeClass('hidden_valid'); });
              $(".autocomplete[@name='countryTxt']").change(function() { changeCountry(''); });
              changeCountry({/literal}'{$smarty.request.country}'{literal});
              $(".autocomplete[@name='schoolTxt']").change(function() { changeSchool(''); });
              changeSchool({/literal}'{$smarty.request.school}'{literal});
              $(".autocompleteToSelect").each(function() {
                var fieldName = $(this).attr('href');
                $(this).attr('href', baseurl + 'list/'+fieldName).click(function() {
                  var oldval = $("input.autocompleteTarget[@name='"+fieldName+"']")[0].value;
                  $(".autocompleteTarget[@name='"+fieldName+"']").parent().load(baseurl + 'list/'+fieldName,{},function(selectBox) {
                    $(".autocompleteTarget[@name='"+fieldName+"']").remove();
                    $(".autocomplete[@name='"+fieldName+"Txt']").remove();
                    $("select[@name='"+fieldName+"']").attr('value', oldval);
                  });
                  return false;
                });
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
          <option value="&lt;=" {if $smarty.request.egal2 neq "&gt;=" && $smarty.request.egal2 neq "="}selected="selected"{/if}>&nbsp;&lt;=&nbsp;</option>
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
              <input type="radio" name="woman" value="0" {if !$smarty.request.woman}checked="checked"{/if} id="woman0"/><label for="woman0">Indifférent</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="woman" value="1" {if $smarty.request.woman eq 1}checked="checked"{/if} id="woman1"/><label for="woman1">Homme</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="woman" value="2" {if $smarty.request.woman eq 2}checked="checked"{/if} id="woman2"/><label for="woman2">Femme</label>
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
              <input type="radio" name="subscriber" value="0" {if !$smarty.request.subscriber}checked="checked"{/if} id="subscriber0"/><label for="subscriber0">Indifférent</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="subscriber" value="1" {if $smarty.request.subscriber eq 1}checked="checked"{/if} id="subscriber1"/><label for="subscriber1">Inscrit</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="subscriber" value="2" {if $smarty.request.subscriber eq 2}checked="checked"{/if} id="subscriber2"/><label for="subscriber2">Non inscrit</label>
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
              <input type="radio" name="alive" value="0" {if !$smarty.request.alive}checked="checked"{/if} id="alive0"/><label for="alive0">Indifférent</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="alive" value="1" {if $smarty.request.alive eq 1}checked="checked"{/if} id="alive1"/><label for="alive1">Vivant</label>
            </td>
            <td style="width:100px">
              <input type="radio" name="alive" value="2" {if $smarty.request.alive eq 2}checked="checked"{/if} id="alive2"/><label for="alive2">Décédé</label>
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
        <input name="countryTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.countryTxt}"/>
        <input name="country" class="autocompleteTarget" type="hidden" value="{$smarty.request.country}"/>
        <a href="country" class="autocompleteToSelect">{icon name="table" title="Tous les pays"}</a>
      </td>
    </tr>
    <tr id="region_ln">
      <td>Région ou département</td>
      <td>
        <input name="region" type="hidden" size="32" value="{$smarty.request.region}"/>
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
        <input name="fonctionTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.fonctionTxt}"/>
        <input name="fonction" class="autocompleteTarget" type="hidden" value="{$smarty.request.fonction}"/>
        <a href="fonction" class="autocompleteToSelect">{icon name="table" title="Toutes les fonctions"}</a>
      </td>
    </tr>
    <tr>
      <td>Poste</td>
      <td><input type="text" class="autocomplete" name="poste" size="32" value="{$smarty.request.poste}" /></td>
    </tr>
    <tr>
      <td>Secteur</td>
      <td>
        <input name="secteurTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.secteurTxt}"/>
        <input name="secteur" class="autocompleteTarget" type="hidden" value="{$smarty.request.secteur}"/>
        <a href="secteur" class="autocompleteToSelect">{icon name="table" title="Tous les secteurs"}</a>
      </td>
    </tr>
    <tr>
      <td>CV contient</td>
      <td><input type="text" name="cv" size="32" value="{$smarty.request.cv}" /></td>
    </tr>
    <tr>
      <td colspan="2">
        <input type='checkbox' name='only_referent' {if $smarty.request.only_referent}checked='checked'{/if} id="only_referent"/>
        <label for="only_referent">chercher uniquement parmi les camarades se proposant comme référents</label>
      </td>
    </tr>
    <tr>
      <th colspan="2">Divers</th>
    </tr>
    <tr>
      <td>Nationalité</td>
      <td>
        <input name="nationaliteTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.nationaliteTxt}"/>
        <input name="nationalite" class="autocompleteTarget" type="hidden" value="{$smarty.request.nationalite}"/>
        <a href="nationalite" class="autocompleteToSelect">{icon name="table" title="Toutes les nationalités"}</a>
      </td>
    </tr>
    <tr>
      <td>Binet</td>
      <td>
        <input name="binetTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.binetTxt}"/>
        <input name="binet" class="autocompleteTarget" type="hidden" value="{$smarty.request.binet}"/>
        <a href="binet" class="autocompleteToSelect">{icon name="table" title="Tous les binets"}</a>
      </td>
    </tr>
    <tr>
      <td>Groupe X</td>
      <td>
        <input name="groupexTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.groupexTxt}"/>
        <input name="groupex" class="autocompleteTarget" type="hidden" value="{$smarty.request.groupex}"/>
        <a href="groupex" class="autocompleteToSelect">{icon name="table" title="Tous les groupes X"}</a>
      </td>
    </tr>
    <tr>
      <td>Section</td>
      <td>
        <input name="sectionTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.sectionTxt}"/>
        <input name="section" class="autocompleteTarget" type="hidden" value="{$smarty.request.section}"/>
        <a href="section" class="autocompleteToSelect">{icon name="table" title="Toutes les sections"}</a>
      </td>
    </tr>
    <tr>
      <td>Formation</td>
      <td>
        <input name="schoolTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.schoolTxt}"/>
        <input name="school" class="autocompleteTarget" type="hidden" value="{$smarty.request.school}"/>
        <a href="school" class="autocompleteToSelect">{icon name="table" title="Toutes les formations"}</a>
      </td>
    </tr>
    <tr id="diploma_ln">
      <td>Diplôme</td>
      <td>
        <input name="diploma" type="hidden" size="32" value="{$smarty.request.diploma}"/>
      </td>
    </tr>
    <tr>
      <td>Commentaire contient</td>
      <td><input type="text" name="free" size="32" value="{$smarty.request.free}" /></td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top: 1.5em">
        <div style="float: right">
          <input type="submit" value="Chercher" />
        </div>
        {if $smarty.session.auth ge AUTH_COOKIE}
          <input type='checkbox' name='order' value='date_mod' {if $smarty.request.order eq "date_mod"}checked='checked'{/if} id="order"/>
          <label for="order">mettre les fiches modifiées récemment en premier</label>
        {/if}
      </td>
    </tr>
  </table>
</form>
<p>
  <strong>N.B.</strong> Le caractère joker * peut remplacer une ou plusieurs lettres dans les recherches.
</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
