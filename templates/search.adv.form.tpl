{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: search.adv.form.tpl,v 1.3 2004-11-27 15:41:03 x2000bedo Exp $
 ***************************************************************************}

<h1>Recherche avancée</h1>

{if $error}
<p class="error">{$error}</p>
{/if}

<p>[<a href="search.php">Recherche simple</a>]</p>

<form id="recherche" action="{$smarty.server.PHP_SELF}" method="get">
  <table class="bicol" cellpadding="3" summary="Recherche">
    <tr>
      <td>Nom</td>
      <td>
        <input type="text" name="name" size="32" value="{$smarty.request.name}" />
        {if $smarty.request.name && !$with_soundex && $smarty.request.recherche}
        <a class='smaller' href="{$smarty.server.PHP_SELF}?with_soundex=1&amp;rechercher=1&amp;{$url_args}&amp;mod_date_sort={$mod_date_sort}">
          étendre par proximité sonore
        </a>
        {/if}
      </td>
    </tr>
    <tr>
      <td>Prénom</td>
      <td>
        <input type="text" name="firstname" size="32" value="{$smarty.request.firstname}" />
        {if $smarty.request.firstname && !$with_soundex && $smarty.request.recherche}
        <a class='smaller' href="{$smarty.server.PHP_SELF}?with_soundex=1&amp;rechercher=1&amp;{$url_args}&amp;mod_date_sort={$mod_date_sort}">
          étendre par proximité sonore
        </a>
        {/if}
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
        <input type="radio" name="woman" value="0" {if !$smarty.request.woman}checked="checked"{/if} />Homme ou femme&nbsp;
        <input type="radio" name="woman" value="1" {if $smarty.request.woman eq 1}checked="checked"{/if} />Homme&nbsp;
        <input type="radio" name="woman" value="2" {if $smarty.request.woman eq 2}checked="checked"{/if} />Femme
      </td>
    </tr>
    <tr>
      <th colspan="2">Géographie</th>
    </tr>
    <tr>
      <td>Ville</td>
      <td><input type="text" name="ville" size="32" value="{$smarty.request.ville}" /></td>
    </tr>
    <tr>
      <td>Pays</td>
      <td>
        <select name="pays" onchange="javascript:document.forms.recherche.submit();">
        {if $smarty.request.pays}
          {assign var="pays" value=$smarty.request.pays}
        {else}
          {assign var="pays" value=""}
        {/if}
        {geoloc_pays pays=$pays}
        </select>
      </td>
    </tr>
    <tr>
      <td>Région ou département</td>
      <td>
        <select name="region">
        {if $smarty.request.region}
          {assign var="region" value=$smarty.request.region}
        {else}
          {assign var="region" value=""}
        {/if}
        {if $smarty.request.pays neq ""}
        {geoloc_region pays=$smarty.request.pays region=$region}
        {else}
        <option value=""></option>
        {/if}
        </select>
      </td>
    </tr>
    <tr>
      <th colspan="2">Activité</th>
    </tr>
    <tr>
      <td colspan="2">
        <input type='checkbox' name='only_referent' {if $smarty.request.only_referent}checked='checked'{/if} />
        chercher uniquement parmis les camarades se proposant comme référent
      </td>
    </tr>
    <tr>
      <td>Entreprise</td>
      <td><input type="text" name="entreprise" size="32" value="{$smarty.request.entreprise}" /></td>
    </tr>
    <tr>
      <td>Poste</td>
      <td>
        <select name="poste">
          <option value="0"></option>
          {section name=poste loop=$choix_postes}
          <option value="{$choix_postes[poste].id}" {if $smarty.request.poste eq $choix_postes[poste].id}selected{/if}>
            {$choix_postes[poste].fonction_fr}
          </option>
          {/section}
        </select>
      </td>
    </tr>
    <tr>
      <td>Secteur</td>
      <td>
        <select name="secteur">
          <option value="0"></option>
          {section name=secteur loop=$choix_secteurs}
          <option value="{$choix_secteurs[secteur].id}" {if $smarty.request.secteur eq $choix_secteurs[secteur].id}selected{/if}>
            {$choix_secteurs[secteur].label}
          </option>
          {/section}
        </select>
      </td>
    </tr>
    <tr>
      <td>CV contient</td>
      <td><input type="text" name="cv" size="32" value="{$smarty.request.cv}" /></td>
    </tr>
    <tr>
      <th colspan="2">Divers</th>
    </tr>
    <tr>
      <td>Nationalité</td>
      <td>
        <select name="nationalite">
        {section name=nationalite loop=$choix_nationalites}
          <option value="{$choix_nationalites[nationalite].id}" {if $smarty.request.nationalite eq
          $choix_nationalites[nationalite].id}selected="selected"{/if}>
            {$choix_nationalites[nationalite].text}
          </option>
        {/section}
        </select>
      </td>
    </tr>
    <tr>
      <td>Binet</td>
      <td>
        <select name="binet">
        <option value="0"></option>
        {section name=binet loop=$choix_binets}
          <option value="{$choix_binets[binet].id}" {if $smarty.request.binet eq
          $choix_binets[binet].id}selected="selected"{/if}>
            {$choix_binets[binet].text}
          </option>
        {/section}
        </select>
      </td>
    </tr>
    <tr>
      <td>Groupe X</td>
      <td>
        <select name="groupex">
        <option value="0"></option>
        {section name=groupex loop=$choix_groupesx}
          <option value="{$choix_groupesx[groupex].id}" {if $smarty.request.groupex eq
          $choix_groupesx[groupex].id}selected="selected"{/if}>
            {$choix_groupesx[groupex].text}
          </option>
        {/section}
        </select>
      </td>
    </tr>
    <tr>
      <td>Section</td>
      <td>
        <select name="section">
        {section name=section loop=$choix_sections}
          <option value="{$choix_sections[section].id}" {if $smarty.request.section eq
          $choix_sections[section].id}selected="selected"{/if}>
            {$choix_sections[section].text}
          </option>
        {/section}
        </select>
      </td>
    </tr>
    <tr>
      <td>Formation</td>
      <td>
        <select name="school" onchange="javascript:document.forms.recherche.submit();">
          <option value="0"></option>
          {section name=school loop=$choix_schools}
          <option value="{$choix_schools[school].id}" {if $smarty.request.school eq
          $choix_schools[school].id}selected="selected"{/if}>
            {$choix_schools[school].text}
          </option>
          {/section}
        </select>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <select name="diploma">
          <option value="0"></option>
          {section name=diploma loop=$choix_diplomas}
          <option value="{$choix_diplomas[diploma]}" {if $smarty.request.diploma eq
          $choix_diplomas[diploma]}selected="selected"{/if}>
            {$choix_diplomas[diploma]}
          </option>
          {/section}
        </select>
      </td>
    </tr>
  </table>
  <div class="center">
    <br />
    {min_auth level='cookie'}
    <input type='checkbox' name='mod_date_sort' {if $smarty.request.mod_date_sort}checked='checked'{/if} />
    mettre les fiches modifiées récemment en premier <br /> <br />
    {/min_auth}
    <input type="submit" name="rechercher" value="Chercher" />
  </div>
</form>
<p>
  <strong>N.B.</strong> Le caractère joker * peut remplacer une ou plusieurs lettres dans les recherches.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
