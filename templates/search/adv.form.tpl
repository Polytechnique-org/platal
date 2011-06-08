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

<h1>Recherche dans l'annuaire</h1>

{if hasPerm('edit_directory,admin') && t($suggestAddresses)}
<p class="center"><strong>Voulez-vous télécharger le <a href="{$globals->baseurl}/search/adv/addresses{$plset_args}">tableau des adresses postales</a> pour la recette précédente&nbsp;?</strong></p>
{/if}

<script type="text/javascript">// <!--
  var baseurl = $.plURL("search/");
  {literal}

  // display an autocomplete row : blabla (nb of found matches)
  function make_format_autocomplete(block) {
    return function(row) {
        regexp = new RegExp('(' + RegExp.escape(block.value) + ')', 'i');

        name = row[0].htmlEntities().replace(regexp, '<strong>$1<\/strong>');

        if (row[1] === "-1") {
          return '&hellip;';
        }

        if (row[1] === "-2") {
          return '<em>aucun camarade trouvé pour '+row[0].htmlEntities()+'<\/em>';
        }

        camarades = (row[1] > 1) ? "camarades" : "camarade";

        return name + '<em>&nbsp;&nbsp;-&nbsp;&nbsp;' + row[1].htmlEntities() + '&nbsp;' + camarades + '<\/em>';
      };
  }

  function setAddress(i, j, values)
  {
    var types = new Array('country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'locality', 'sublocality');
    var prev_type = types[i];
    var next_type = types[j];
    var next_list = next_type + '_list';

    if (j == 3) {
      $('tr#locality_text').hide()
      $("select[name='localityTxt']").attr('value', '');
    }

    $("[name='" + next_type + "']").parent().load(baseurl + 'list/' + next_type, { previous:prev_type, value:values[i] }, function() {
      if ($("select[name='" + next_type + "']").children("option").size() > 1) {
        $("tr#" + next_list).show();
        $("select[name='" + next_type + "']").attr('value', values[j]);
        if (j < 6) {
          setAddress(j, j + 1, values);
        }
      } else {
        $("tr#" + next_list).hide();
        $("select[name='" + next_type + "']").attr('value', '');
        if (j < 6) {
          setAddress(i, j + 1, values);
        }
      }
    });

  }

  function displayNextAddressComponent(i, j, value)
  {
    var types = new Array('country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'locality', 'sublocality');
    var prev_type = types[i];
    var next_type = types[j];
    var next_list = next_type + '_list';

    if (j == 3) {
      $('tr#locality_text').hide()
      $("select[name='localityTxt']").attr('value', '');
    }

    $("[name='" + next_type + "']").parent().load(baseurl + 'list/' + next_type, { previous:prev_type, value:value }, function() {
      $("select[name='" + next_type + "']").attr('value', '');
      if ($("select[name='" + next_type + "']").children("option").size() > 1) {
        $("tr#" + next_list).show();
      } else {
        $("tr#" + next_list).hide();
        if (j < 6) {
          displayNextAddressComponent(i, j + 1, value);
        }
      }
    });
  }

  function changeAddressComponents(type, value)
  {
    var types = new Array('country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'locality', 'sublocality');
    var i = 0, j = 0;

    while (types[i] != type && i < 6) {
      ++i;
    }

    j = i + 1;
    while (j < 6) {
      $("select[name='" + types[j] + "']").attr('value', '');
      $("tr#" + types[j] + "_list").hide();
      ++j;
    }

    if (value != '' && i < 5) {
      $("select[name='" + type + "']").attr('value', value);
      displayNextAddressComponent(i, i + 1, value);
    }
  }

  // when changing school, open diploma choice
  function changeSchool(schoolId) {
    $(".autocompleteTarget[name='school']").attr('value',schoolId);

    if (schoolId) {
      $(".autocomplete[name='schoolTxt']").addClass('hidden_valid');
    } else {
      $(".autocomplete[name='schoolTxt']").removeClass('hidden_valid');
    }

    $("[name='diploma']").parent().load(baseurl + 'list/diploma/', { school:schoolId }, function() {
        $("select[name='diploma']").attr('value', '{/literal}{$smarty.request.diploma}{literal}');
      });
  }

  // when checking/unchecking "only_referent", disable/enable some fields
  function changeOnlyReferent() {
    if ($("#only_referent").is(':checked')) {
      $("input[name='entreprise']").attr('disabled', true);
    } else {
      $("input[name='entreprise']").removeAttr('disabled');
    }
  }

  // when choosing a job term in tree, hide tree and set job term field
  function searchForJobTerm(treeid, jtid, full_name) {
    $(".term_tree").remove();
    $("input[name='jobtermTxt']").val(full_name).addClass("hidden_valid").show();
    $("input[name='jobterm']").val(jtid);
  }

  function cancel_autocomplete(field, realfield) {
    $(".autocomplete[name='"+field+"']").removeClass('hidden_valid').val('').focus();
    if (typeof(realfield) != "undefined") {
      $(".autocompleteTarget[name='"+realfield+"']").val('');
    }
    return;
  }

  // when choosing autocomplete from list, must validate
  function select_autocomplete(name) {
      nameRealField = name.replace(/Txt$/, '');

      // nothing to do if field is not a text field for a list
      if (nameRealField == name)
        return null;

      // When changing country or locality, open next address component.
      if (nameRealField == 'country' || nameRealField == 'locality') {
        return function(i) {
            nameRealField = name.replace(/Txt$/, '');
            if (i.extra[0] < 0) {
              cancel_autocomplete(name, nameRealField);
              i.extra[1] = '';
            }
            $("[name='" + nameRealField + "']").parent().load(baseurl + 'list/' + nameRealField, function() {
              $("select[name='" + nameRealField + "']").attr('value', i.extra[1]);
            });
            changeAddressComponents(nameRealField, i.extra[1]);
          }
      }

      if (nameRealField == 'school')
        return function(i) {
            if (i.extra[0] < 0) {
              cancel_autocomplete('schoolTxt', 'school');
              i.extra[1] = '';
            }
            changeSchool(i.extra[1]);
          }

      // change field in list and display text field as valid
      return function(i) {
        nameRealField = this.field.replace(/Txt$/, '');

        if (i.extra[0] < 0) {
          cancel_autocomplete(this.field, nameRealField);
          return;
        }

        $(".autocompleteTarget[name='"+nameRealField+"']").attr('value',i.extra[1]);

        $(".autocomplete[name='"+this.field+"']").addClass('hidden_valid');
      }
    }

  $(function() {
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

      if ({/literal}'{$smarty.request.country}'{literal} != '') {
        $("[name='country']").parent().load(baseurl + 'list/country', function() {
          $("select[name='country']").attr('value', {/literal}'{$smarty.request.country}'{literal});
        });
        setAddress(0, 1, new Array({/literal}'{$smarty.request.country}'{literal},
                                   {/literal}'{$smarty.request.administrative_area_level_1}'{literal},
                                   {/literal}'{$smarty.request.administrative_area_level_2}'{literal},
                                   {/literal}'{$smarty.request.administrative_area_level_3}'{literal},
                                   {/literal}'{$smarty.request.locality}'{literal},
                                   {/literal}'{$smarty.request.sublocality}'{literal})
        );
      } else {
        var types = new Array('administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'locality', 'sublocality');
        for (var i = 0; i < 5; ++i) {
          $("tr#" + types[i] + '_list').hide();
        }
      }

      $(".autocomplete[name='schoolTxt']").change(function() { changeSchool(''); });

      changeSchool({/literal}'{$smarty.request.school}'{literal});

      $(".autocompleteToSelect").each(function() {
          var fieldName = $(this).attr('href');

          $(this).attr('href', baseurl + 'list/'+fieldName).click(function() {
              var oldval = $("input.autocompleteTarget[name='"+fieldName+"']")[0].value;

              $(".autocompleteTarget[name='"+fieldName+"']").parent().load(baseurl + 'list/'+fieldName,{},
                function(selectBox) {
                  $(".autocompleteTarget[name='"+fieldName+"']").remove();
                  $(".autocomplete[name='"+fieldName+"Txt']").remove();
                  $("select[name='"+fieldName+"']").attr('value', oldval);
                });

              return false;
            });
        }).parent().find('.autocomplete').change(function() {
          // If we change the value in the type="text" field, then the value in the 'integer id' field must not be used,
          // to ensure that, we unset it
          $(this).parent().find('.autocompleteTarget').val('');
        });

      $("#only_referent").change(function() { changeOnlyReferent(); });

    });
/** Regexps to wipe out from search queries */
var default_form_values = [ /&woman=0(&|$)/, /&subscriber=0(&|$)/, /&alive=0(&|$)/, /&egal[12]=[^&]*&promo[12]=(&|$)/g, /&networking_type=0(&|$)/, /&[^&=]+=(&|$)/g ];
/** Uses javascript to clean form from all empty fields */
function cleanForm(f) {
  var query = $(f).formSerialize();
  var old_query;
  for (var i in default_form_values) {
    var reg = default_form_values[i];
    if (typeof(reg) != "undefined") {
      do {
        old_query = query;
        query = query.replace(reg, '$1');
      } while (old_query != query);
    }
  }
  query = query.replace(/^&*(.*)&*$/, '$1');
  if (query == "rechercher=Chercher") {
    alert("Aucun critère n'a été spécifié");
    return false;
  }
  document.location = baseurl + 'adv?' + query;
  return false;
}
-->
{/literal}</script>
<p class="center">[<a href="search">Revenir à la recherche simple</a>]</p>
<form id="recherche" action="search/adv" method="get" onsubmit="return cleanForm(this)">
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
      <td>Pays</td>
      <td>
        <input name="countryTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.countryTxt}"/>
        <input name="country" class="autocompleteTarget" type="hidden" value="{$smarty.request.country}"/>
        <a href="country" class="autocompleteToSelect">{icon name="table" title="Tous les pays"}</a>
      </td>
    </tr>
    <tr id="administrative_area_level_1_list">
      <td>Région, province, état&hellip;</td>
      <td>
        <input name="administrative_area_level_1" type="hidden" size="32" value="{$smarty.request.administrative_area_level_1}" />
      </td>
    </tr>
    <tr id="administrative_area_level_2_list">
      <td>Département, comté&hellip;</td>
      <td>
        <input name="administrative_area_level_2" type="hidden" size="32" value="{$smarty.request.administrative_area_level_2}" />
      </td>
    </tr>
    <tr id="administrative_area_level_3_list">
      <td>Canton&hellip;</td>
      <td>
        <input name="administrative_area_level_3" type="hidden" size="32" value="{$smarty.request.administrative_area_level_3}" />
      </td>
    </tr>
    <tr id="locality_text">
      <td>Ville</td>
      <td><input type="text" class="autocomplete" name="localityTxt" size="32" value="{$smarty.request.localityTxt}" /></td>
    </tr>
    <tr id="locality_list">
      <td>Ville</td>
      <td>
        <input name="locality" type="hidden" size="32" value="{$smarty.request.locality}" />
      </td>
    </tr>
    <tr id="sublocality_list">
      <td>Arrondissement, quartier&hellip;</td>
      <td>
        <input name="sublocality" type="hidden" size="32" value="{$smarty.request.sublocality}" />
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <label for="only_current">
          <input name="only_current" id="only_current" type="checkbox"{if $smarty.request.only_current} checked="checked"{/if}/>
          Chercher uniquement les adresses actuelles.
        </label>
      </td>
    </tr>
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
    <tr>
      <td>Mots-clefs</td>
      <td>
        <input name="jobtermTxt" type="text" class="autocomplete{if $smarty.request.jobterm} hidden_valid{/if}" style="display:none" size="32"
               value="{$smarty.request.jobtermTxt}"/>
        <input name="jobterm" class="autocompleteTarget" type="hidden" value="{$smarty.request.jobterm}"/>
        <a href="jobterm" class="autocompleteToSelect">{icon name="table" title="Tous les mots-clefs"}</a>
      </td>
    </tr>
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
    <tr>
      <td>Nationalité</td>
      <td>
        <input name="nationaliteTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.nationaliteTxt}"/>
        <input name="nationalite" class="autocompleteTarget" type="hidden" value="{$smarty.request.nationalite}"/>
        <a href="nationalite" class="autocompleteToSelect">{icon name="table" title="Toutes les nationalités"}</a>
      </td>
    </tr>
    {if hasPerm('directory_private')}
    <tr>
      <td>Binet</td>
      <td>
        <input name="binetTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.binetTxt}"/>
        <input name="binet" class="autocompleteTarget" type="hidden" value="{$smarty.request.binet}"/>
        <a href="binet" class="autocompleteToSelect">{icon name="table" title="Tous les binets"}</a>
      </td>
    </tr>
    {/if}
    <tr>
      <td>Groupe X</td>
      <td>
        <input name="groupexTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.groupexTxt}"/>
        <input name="groupex" class="autocompleteTarget" type="hidden" value="{$smarty.request.groupex}"/>
        <a href="groupex" class="autocompleteToSelect">{icon name="table" title="Tous les groupes X"}</a>
      </td>
    </tr>
    {if hasPerm('directory_private')}
    <tr>
      <td>Section</td>
      <td>
        <input name="sectionTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.sectionTxt}"/>
        <input name="section" class="autocompleteTarget" type="hidden" value="{$smarty.request.section}"/>
        <a href="section" class="autocompleteToSelect">{icon name="table" title="Toutes les sections"}</a>
      </td>
    </tr>
    {/if}
    <tr>
      <td>Formation</td>
      <td>
        <input name="schoolTxt" type="text" class="autocomplete" style="display:none" size="32"
               value="{$smarty.request.schoolTxt}"/>
        <input name="school" class="autocompleteTarget" type="hidden" value="{$smarty.request.school}"/>
        <a href="school" class="autocompleteToSelect">{icon name="table" title="Toutes les formations"}</a>
      </td>
    </tr>
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
        {/if}
        {if $smarty.session.auth ge AUTH_COOKIE}
    </tr>
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
    <tr><td colspan="2"></td></tr>
    <tr>
      <td colspan="2" style="text-align: center">
          <input type="submit" value="Chercher" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
