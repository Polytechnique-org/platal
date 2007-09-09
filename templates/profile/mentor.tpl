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

<script type="text/javascript">//<![CDATA[
{literal}

function updateCountry()
{
  var val = document.forms.prof_annu.countries_sel.value;
  var show = true;
  if (val == '') {
    show = false;
  }
  if (document.getElementById('countries_' + val) != null) {
    show = false;
  }
  document.getElementById('countries_add').style.display = show ? '' : 'none';
}

function addCountry()
{
  var cb   = document.forms.prof_annu.countries_sel;
  var val  = cb.value;
  var text = cb.options[cb.selectedIndex].text;
  var html = '<div id="countries_' + val + '" style="clear: both; margin-bottom: 0.7em">'
           + '  <div style="float: left; width: 50%">' + text + '</div>'
           + '  <input type="hidden" name="countries[' + val + ']" value="' + text + '" />'
           + '  <a href="javascript:removeCountry(\'' + val + '\')">'
           + '    <img src="images/icons/cross.gif" alt="" title="Supprimer ce pays" />'
           + '  </a>'
           + '</div>';
  $('#countries').append(html);
  updateCountry();
}

function removeCountry(id)
{
  $('#countries_' + id).remove();
  updateCountry();
}

function updateSSecteur()
{
  var s  = document.forms.prof_annu.secteur_sel.value;
  var ss = document.forms.prof_annu['jobs[-1][ss_secteur]'].value;
  var show = true;
  if (s == '' || ss == '') {
    show = false;
  }
  if (document.getElementById('secteurs_' + s + '_' + ss) != null) {
    show = false;
  }
  document.getElementById('secteurs_add').style.display = show ? 'block' : 'none';
}

function updateSecteur()
{
  var secteur = document.forms.prof_annu.secteur_sel.value;
  if (secteur == '') {
    secteur = '-1';
  }
  $.get(platal_baseurl + 'profile/ajax/secteur/-1/' + secteur,
        function(data) {
          data = '<a href="javascript:addSecteur()" style="display: none; float: right" id="secteurs_add">'
               +  '  <img src="images/icons/add.gif" alt="" title="Ajouter ce secteur" />'
               +  '</a>' + data;
          document.getElementById('ss_secteur_sel').innerHTML = data;
          attachEvent(document.forms.prof_annu['jobs[-1][ss_secteur]'], 'change', updateSSecteur);
        });
}

function addSecteur()
{
  var scb = document.forms.prof_annu.secteur_sel;
  var s  = scb.value;
  var st = scb.options[scb.selectedIndex].text;

  var sscb = document.forms.prof_annu['jobs[-1][ss_secteur]'];
  var ss = sscb.value;
  var sst = sscb.options[sscb.selectedIndex].text;

  var html = '<div id="secteurs_' + s + '_' + ss + '" style="clear: both; margin-top: 0.5em" class="titre">'
           + '  <a href="javascript:removeSecteur(\'' + s + '\', \'' + ss + '\')" style="display: block; float: right">'
           + '    <img src="images/icons/cross.gif" alt="" title="Supprimer ce secteur" />'
           + '  </a>'
           + '  <input type="hidden" name="secteurs[' + s + '][' + ss + ']" value="' + sst + '" />'
           + '  ' + sst
           + '</div>';
  $('#secteurs').append(html);
  updateSSecteur();
}

function removeSecteur(s, ss)
{
  $('#secteurs_' + s + '_' + ss).remove();
  updateSSecteur();
}

{/literal}
//]]></script>

<p>
  Si tu acceptes que ceux des camarades te contactent afin de te demander
  conseil, dans les domaines que tu connais bien, et pour lesquels tu pourrais
  les aider, remplis cette rubrique.
</p>
<p>
  Tu peux mentionner ici les domaines de compétences, les expériences
  notamment internationales sur la base desquels tu seras identifiable depuis
  <a href="referent/search">la page de recherche d'un conseil professionnel</a>.<br />
</p>
<p>Le mentoring est particulièrement important pour les camarades&nbsp;:</p>
<ul>
  <li>encore jeunes, sont en train de bâtir leur projet professionnel,</li>
  <li>ou bien, plus âgés, souhaitent réorienter leur carrière,</li>
</ul>

<table class="bicol" style="margin-bottom: 1em" summary="Profil: Mentoring">
  <tr>
    <th>
      Pays dont tu connais bien la culture professionnelle
    </th>
  </tr>
  <tr>
    <td class="flags">
      <span class="rouge"><input type="checkbox" name="accesX" checked="checked" disabled="disabled" /></span>
      <span class="texte">privé</span>
    </td>
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
        <div style="float: left; width: 50%">{$country}</div>
        <input type="hidden" name="countries[{$i}]" value="{$country}" />
        <a href="javascript:removeCountry('{$i}')">{icon name=cross title="Supprimer ce pays"}</a>
      </div>
      {/foreach}
    </td>
  </tr>
</table>

<table class="bicol" style="margin-bottom: 1em" summary="Profil: Mentoring">
  <tr>
    <th>
      Secteurs d'activité dans lesquels tu as beaucoup exercé
    </th>
  </tr>
  <tr>
    <td class="flags">
      <span class="rouge"><input type="checkbox" name="accesX" checked="checked" disabled="disabled" /></span>
      <span class="texte">privé</span>
    </td>
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
        <input type="hidden" name="secteurs[' + s + '][' + ss + ']" value="{$ss_sect}" />
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
      Expérience et expertises que tu acceptes de faire partager
    </th>
  </tr>
  <tr>
    <td class="flags">
      <span class="rouge"><input type="checkbox" name="accesX" checked="checked" disabled="disabled" /></span>
      <span class="texte">privé</span>
    </td>
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
