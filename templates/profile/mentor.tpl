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

{javascript name=jobtermstree}

<div>{icon name=information title="Afficher ma fiche référent"}Tu peux consulter ta <a class="popup2" href="referent/{$hrpid}">fiche référent</a> qui n'est accessible que par les X.
</div>
{if !$expertise || !t($sectors) || !($sectors|@count)}
  <br /><div>
    <strong>{icon name=error title="Attention"} Attention&nbsp;: pour figurer dans la base de données des mentors, il faut remplir la
    dernière case en bas de cette page et avoir au moins un secteur d'activité de prédilection.</strong><br />
  </div>
{/if}
<p>
  Si tu acceptes que des camarades te contactent afin de te demander
  conseil, dans les domaines que tu connais bien, et pour lesquels tu pourrais
  les aider, remplis cette rubrique.
</p>
<p>
  Tu peux mentionner ici les domaines de compétences, les expériences
  notamment internationales sur la base desquels tu seras identifiable depuis
  <a href="referent/search#mentors">la page de recherche d'un conseil professionnel</a>.<br />
</p>
<p>Le mentoring est particulièrement important pour les camarades&nbsp;:</p>
<ul>
  <li>encore jeunes, qui sont en train de bâtir leur projet professionnel&nbsp;;</li>
  <li>ou bien, plus âgés, qui souhaitent réorienter leur carrière.</li>
</ul>

<table class="bicol" id="countries_table" style="margin-bottom: 1em" summary="Profil&nbsp;: Mentoring">
  <tr>
    <th>
      <div class="flags" style="float: left">
        <input type="checkbox" name="accesX" checked="checked" disabled="disabled" />
        {icon name="flag_red" title="privé"}
      </div>
      Pays dont tu connais bien la culture professionnelle
    </th>
  </tr>
  <tr class="impair">
    <td>
      <div style="float: left; width: 30%" class="titre">Pays</div>
      <div id="countries_add" style="display: none; float: right">
        <a href="javascript:addCountry()">{icon name=add title="Ajouter ce pays"}</a>
      </div>
      <select name="countries_sel" onchange="updateElement('countries')">
        <option value="">&nbsp;</option>
        {iterate from=$countryList item=country}
        <option value="{$country.iso_3166_1_a2}">{$country.countryFR|default:"&nbsp;"}</option>
        {/iterate}
      </select>
    </td>
  </tr>
  <tr class="pair">
    <td id="countries">
      {foreach from=$countries item=country key=i}
      <div id="countries_{$i}" style="clear: both; margin-bottom: 0.7em">
        <a style="display: block; float: right"
           href="javascript:removeElement('countries','{$i}')">{icon name=cross title="Supprimer ce pays"}</a>
        <div class="titre">{$country}</div>
        <input type="hidden" name="countries[{$i}]" value="{$country}" />
      </div>
      {/foreach}
    </td>
  </tr>
</table>

<script type="text/javascript" src="javascript/jquery.jstree.js"></script>

<table class="bicol" style="margin-bottom: 1em" summary="Profil&nbsp;: Mentoring">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <input type="checkbox" name="accesX" checked="checked" disabled="disabled" />
        {icon name="flag_red" value="privé"}
      </div>
      Mots clefs qui représentent le mieux ton expérience
    </th>
  </tr>
  <tr>
    <td colspan="2">
      Il est préférable de mentionner des notions précises : <em>Pizzaïolo</em> plutôt que <em>Hôtellerie</em>.
      En effet Les recherches sur le mot-clef <em>Hôtellerie</em> te trouveront dans les deux cas mais une
      recherche sur <em>Production culinaire</em> ou <em>Pizzaïolo</em> non.
    <td/>
  </tr>
  <tr>
    <td class="titre" style="width:30%">Mots-clefs</td>
    <td class="job_terms">
      <input type="text" class="term_search" size="35"/>
      <a href="javascript:toggleJobTermsTree(-1)">{icon name="table" title="Tous les mots-clefs"}</a>
      <script type="text/javascript">
      /* <![CDATA[ */
      $(function() {ldelim}
        {foreach from=$terms item=term}
        addJobTerm(-1, "{$term.jtid}", "{$term.full_name|replace:'"':'\\"'}");
        {/foreach}
        $('.term_search').autocomplete(platal_baseurl + 'profile/jobterms',
          {ldelim}
            "formatItem" : displayJobTerm,
            "extraParams" : {ldelim} "jobid" : "-1" {rdelim},
            "width" : $('.term_search').width()*2,
            "onItemSelect" : selectJobTerm,
            "matchSubset" : false
          {rdelim});
      {rdelim});
      /* ]]> */
      </script>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="term_tree">
    </td>
  </tr>
</table>

<table class="bicol" summary="Profil&nbsp;: Mentoring">
  <tr>
    <th>
      <div class="flags" style="float: left">
        <input type="checkbox" name="accesX" checked="checked" disabled="disabled" />
        {icon name="flag_red" title="privé"}
      </div>
      Expériences et expertises que tu acceptes de faire partager
    </th>
  </tr>
  <tr>
    <td>
      Dans cette case il te faut indiquer en quelques mots ce qui t'a
      amené à acquérir l'expérience indiquée, et dans quelle mesure tu
      veux bien que ceux de nos camarades qui seraient intéressés par un
      contact avec toi, en prennent l'initiative. <strong>Il est obligatoire de
      remplir cette dernière case pour apparaître dans la base de données
      des "Mentors".</strong>
    </td>
  </tr>
  <tr>
    <td>
      <textarea rows="8" cols="60" name="expertise">{$expertise}</textarea>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
