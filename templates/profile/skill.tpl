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

function update(cat)
{
  var val  = document.forms.prof_annu[cat + '_sel'].value;
  var show = true;
  if (val == '') {
    show = false;
  }
  if (document.getElementById(cat + '_' + val) != null) {
    show = false;
  }
  document.getElementById(cat + '_add').style.display = show ? '' : 'none';
}

function add(cat)
{
  var sel  = document.forms.prof_annu[cat + '_sel'];
  var val  = sel.value;
  var text = sel.options[sel.selectedIndex].text;
  $.get(platal_baseurl + 'profile/ajax/skill/' + cat + '/' + val,
        function(data) {
          $('#' + cat).append(data);
          document.getElementById(cat + '_' + val + '_title').innerHTML = text;
          update(cat);
        });
}

function remove(cat, id)
{
  $('#' + cat + '_' + id).remove();
  update(cat);
}

{/literal}
//]]></script>

<table class="bicol" style="margin-bottom: 1em">
  <tr>
    <th>
      Compétences professionnelles
    </th>
  </tr>
  <tr>
    <td>
      <div class="flags">
        <span class="rouge"><input type="checkbox" name="accesX" checked="checked" disabled="disabled" /></span>
        <span class="texte">privé</span>
      </div>
      <div>
        <span class="titre">Domaine&nbsp;:</span>
        <select name="competences_sel" onchange="update('competences')">
          <option value=""></option>
          {assign var=ingroup value=false}
          {iterate from=$comp_list item=comp}
          {if $comp.title}
          {if $ingroup}</optgroup>{/if}
          <optgroup label="{$comp.text_fr}">
          {/if}
          <option value="{$comp.id}">{$comp.text_fr}</option>
          {/iterate}
          {if $ingroup}</optgroup>{/if}
        </select>
        <span id="competences_add" style="display: none">
          <a href="javascript:add('competences')">{icon name=add title="Ajouter cette compétence"}</a>
        </span>
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td id="competences">
      {foreach from=$competences item=competence key=id}
      {include file="profile/skill.skill.tpl" cat='competences' skill=$competence id=$id levels=$comp_level}
      {/foreach}
    </td>
  </tr>
</table>

<table class="bicol">
  <tr>
    <th>Compétences linguistiques</th>
  </tr>
  <tr>
    <td>
      <div class="flags">
        <span class="rouge"><input type="checkbox" name="accesX" checked="checked" disabled="disabled" /></span>
        <span class="texte">privé</span>
      </div>
      <div>
        <span class="titre">Domaine&nbsp;:</span>
        <select name="langues_sel" onchange="update('langues')">
          <option value=""></option>
          {iterate from=$lang_list item=lang}
          <option value="{$lang.id}">{$lang.langue_fr}</option>
          {/iterate}
        </select>
        <span id="langues_add" style="display: none">
          <a href="javascript:add('langues')">{icon name=add title="Ajouter cette langue"}</a>
        </span>
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td id="langues">
      {foreach from=$langues item=langue key=id}
      {include file="profile/skill.skill.tpl" cat='langues' skill=$langue id=$id levels=$lang_level}
      {/foreach}
    </td>
  </tr>
 </table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
