{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

<script type="text/javascript">//<![CDATA[
  {literal}$(function() { load_advanced_search({{/literal}{foreach from=$smarty.request key=key item=item name="load"}"{$key}":"{$item}"{if not $smarty.foreach.load.last},{/if}{/foreach}{literal}}); });{/literal}
//]]></script>

<form id="recherche" action="deltaten/search" method="get" onsubmit="return cleanForm(this, 'deltaten/search')">
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
