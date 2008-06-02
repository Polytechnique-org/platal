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

<p>{icon name=information title="Afficher ma fiche référent"}Tu peux consulter ta <a class="popup2" href="referent/{$smarty.session.forlife}">fiche référent</a> qui n'est accessible que par les X.
</p>
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
  <li>encore jeunes, qui sont en train de bâtir leur projet professionnel ;</li>
  <li>ou bien, plus âgés, qui souhaitent réorienter leur carrière.</li>
</ul>

<table class="bicol" style="margin-bottom: 1em" summary="Profil: Mentoring">
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
      <select name="countries_sel" onchange="updateCountry()">
        {geoloc_country country='00'}
      </select>
    </td>
  </tr>
  <tr class="pair">
    <td id="countries">
      {foreach from=$countries item=country key=i}
      <div id="countries_{$i}" style="clear: both; margin-bottom: 0.7em">
        <a style="display: block; float: right"
           href="javascript:removeCountry('{$i}')">{icon name=cross title="Supprimer ce pays"}</a>
        <div class="titre">{$country}</div>
        <input type="hidden" name="countries[{$i}]" value="{$country}" />
      </div>
      {/foreach}
    </td>
  </tr>
</table>

<table class="bicol" style="margin-bottom: 1em" summary="Profil: Mentoring">
  <tr>
    <th>
      <div class="flags" style="float: left">
        <input type="checkbox" name="accesX" checked="checked" disabled="disabled" />
        {icon name="flag_red" value="privé"}
      </div>
      Secteurs d'activité dans lesquels tu as beaucoup exercé
    </th>
  </tr>
  <tr>
    <td id="secteur_sel">
      <div style="float: left; width: 30%" class="titre">Secteur</div>
      <select name="secteur_sel" onchange="updateSecteur()">
        <option value="">&nbsp;</option>
        {iterate from=$secteurs_sel item=secteur}
        <option value="{$secteur.id}">{$secteur.label}</option>
        {/iterate}
      </select>
    </td>
  </tr>
  <tr>
    <td>
      <div style="float: left; width: 30%" class="titre">Sous-secteur</div>
      <span id="ss_secteur_sel"></span>
    </td>
  </tr>
  <tr class="pair">
    <td id="secteurs">
      {if $secteurs|@count}
      {foreach from=$secteurs item=secteur key=s}
      {foreach from=$secteur item=ss_sect key=ss}
      <div id="secteurs_{$s}_{$ss}" style="clear: both; margin-top: 0.5em" class="titre">
        <a href="javascript:removeSecteur('{$s}', '{$ss}')" style="display: block; float: right">
          {icon name=cross title="Supprimer ce secteur"}
        </a>
        <input type="hidden" name="secteurs[{$s}][{$ss}]" value="{$ss_sect}" />
        {$ss_sect}
      </div>
      {/foreach}
      {/foreach}
      {/if}
    </td>
  </tr>
</table>

<table class="bicol" summary="Profil: Mentoring">
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
