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


{if $plset_count}
{include core=plset.tpl}
{else}
{include wiki=Docs.Deltaten}
{/if}

<a id="deltaten"></a>

<script type="text/javascript">// <!--
  var baseurl = $.plURL("deltaten/");
  var baseurl_search = $.plURL("search/");
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

  // when changing country, open up administrativearea choice
  function changeCountry(a2) {
    $(".autocompleteTarget[name='country']").attr('value',a2);

    if (a2) {
      $(".autocomplete[name='countryTxt']").addClass('hidden_valid');

      $("[name='administrativearea']").parent().load(baseurl_search + 'list/administrativearea/', { country:a2 }, function() {
          if ($("select[name='administrativearea']").children("option").size() > 1) {
            $("select[name='administrativearea']").attr('value', '{/literal}{$smarty.request.administrativearea}{literal}');

            $("tr#administrativearea_list").show();
          } else {
            $("select[name='administrativearea']").attr('value', '');

            $("tr#administrativearea_list").hide();
          }
        });
    } else {
      $(".autocomplete[name='countryTxt']").removeClass('hidden_valid');

      $("select[name='administrativearea']").attr('value', '');
      $("select[name='subadministrativearea']").attr('value', '');

      $("tr#administrativearea_list").hide();
      $("tr#subadministrativearea_list").hide();
    }
  }

  // when changing administrativearea, open up subadministrativearea choice
  function changeAdministrativeArea(id) {
    if (id) {
      $("[name='subadministrativearea']").parent().load(baseurl_search + 'list/subadministrativearea/', { administrativearea:id }, function() {
          if ($("select[name='subadministrativearea']").children("option").size() > 1) {
            $("select[name='subadministrativearea']").attr('value', '{/literal}{$smarty.request.subadministrativearea}{literal}');
            $("tr#subadministrativearea_list").show();
          } else {
            $("select[name='subadministrativearea']").attr('value', '');
            $("tr#subadministrativearea_list").hide();
          }
        });
    } else {
      $("select[name='subadministrativearea']").attr('value', '');
      $("tr#subadministrativearea_list").hide();
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

    $("[name='diploma']").parent().load(baseurl_search + 'list/diploma/', { school:schoolId }, function() {
        $("select[name='diploma']").attr('value', '{/literal}{$smarty.request.diploma}{literal}');
      });
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

      // if changing country, might want to open administrativearea choice
      if (nameRealField == 'country')
        return function(i) {
            if (i.extra[0] < 0) {
              cancel_autocomplete('countryTxt', 'country');
              i.extra[1] = '';
            }
            changeCountry(i.extra[1]);
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

          $.get(baseurl_search + 'list/'+ targeted.name +'/'+targeted.value, {},function(textValue) {
            me.attr('value', textValue);
            me.addClass('hidden_valid');
          });
        }

        $(this).autocomplete(baseurl_search + "autocomplete/"+this.name,{
          selectOnly:1,
          formatItem:make_format_autocomplete(this),
          field:this.name,
          onItemSelect:select_autocomplete(this.name),
          matchSubset:0,
          width:$(this).width()});
        });

      $(".autocomplete").change(function() { $(this).removeClass('hidden_valid'); });

      $(".autocomplete[name='countryTxt']").change(function() { changeCountry(''); });

      changeCountry({/literal}'{$smarty.request.country}'{literal});
      changeAdministrativeArea({/literal}'{$smarty.request.administrativearea}'{literal});

      $(".autocomplete[name='schoolTxt']").change(function() { changeSchool(''); });

      changeSchool({/literal}'{$smarty.request.school}'{literal});

      $(".autocompleteToSelect").each(function() {
          var fieldName = $(this).attr('href');

          $(this).attr('href', baseurl_search + 'list/'+fieldName).click(function() {
              var oldval = $("input.autocompleteTarget[name='"+fieldName+"']")[0].value;

              $(".autocompleteTarget[name='"+fieldName+"']").parent().load(baseurl_search + 'list/'+fieldName,{},
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
    });
/** Regexps to wipe out from search queries */
var default_form_values = [ /&woman=0(&|$)/, /&[^&=]+=(&|$)/g ];
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
  document.location = baseurl + 'search?' + query;
  return false;
}
-->
{/literal}</script>
<form id="recherche" action="deltaten/search" method="get" onsubmit="return cleanForm(this)">
  <table class="bicol" cellpadding="3" summary="Recherche">
    <tr>
      <th colspan="2">
        Opération N N-10
      </th>
    </tr>
    <tr>
      <td colspan="2" class="titre">
      Cette recherche est effectuée uniquement au sein des membres de la promotion {$deltaten_promo_old} participant à l'opération N N-10.
      </td>
    </tr>
    <tr>
      <th colspan="2">Géographie</th>
    </tr>
    <tr>
      <td>Ville ou code postal</td>
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
    <tr id="administrativearea_list">
      <td>Région, province, état&hellip;</td>
      <td>
        <input name="administrativearea" type="hidden" size="32" value="{$smarty.request.administrativearea}" />
      </td>
    </tr>
    <tr id="subadministrativearea_list">
      <td>Département, comté&hellip;</td>
      <td>
        <input name="subadministrativearea" type="hidden" size="32" value="{$smarty.request.subadministrativearea}" />
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <label for="only_current">
          <input name="only_current" id="only_current" type="checkbox"{if $smarty.request.only_current} checked="checked"{/if}/>
          Chercher uniquement les adresses où les camarades sont actuellement.
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
      <td><input type="text" class="autocomplete" name="description" size="32" value="{$smarty.request.description}" /></td>
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
    <tr>
      <th colspan="2">Divers</th>
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
      <th colspan="2">Formation</th>
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
    <tr>
      <td>Diplôme</td>
      <td>
        <input name="diploma" size="32" value="{$smarty.request.diploma}"/>
      </td>
    </tr>
    <tr>
      <th colspan="2">Opération N N-10</th>
    </tr>
    <tr>
      <td>
        Message spécifique (recherche texte)&nbsp;:
      </td>
      <td>
        <input type="text" value="{$smarty.request.deltaten_message}" size="30" name="deltaten_message" />
      </td>
    </tr>
    <tr><td colspan="2"></td></tr>
    <tr>
      <td colspan="2" style="text-align: center">
          <input type="submit" value="Chercher" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
