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


{literal}
<script type="text/javascript">//<![CDATA[
  var gdt = new Date();
  var year = gdt.getYear();
  if (year < 1000) {
    year += 1900;
  }

  var oldMate = year >= {/literal}{$smarty.session.promo_sortie}{literal};

  function printTitle(text)
  {
    if (oldMate) {
      document.write("ex-" + text);
    } else {
      document.write(text);
    }
  }

  function update(type)
  {
    var val = document.forms.prof_annu[type + '_sel'].value;
    if (val == '0' || document.getElementById(type + '_' + val) != null) {
      document.getElementById(type + '_add').style.display = 'none';
    } else {
      document.getElementById(type + '_add').style.display = '';
    }
  }

  function remove(cat, id)
  {
    $('#' + cat + '_' + id).remove();
    update(cat);
  }

  function add(cat)
  {
    var cb   = document.forms.prof_annu[cat + '_sel'];
    var id   = cb.value;
    var text = cb.options[cb.selectedIndex].text;
    var html = '<tr id="' + cat + '_' + id + '">'
             + '  <td>'
             + '    <input type="hidden" name="' + cat + '[' + id + ']" value="' + text + '" />'
             + '  </td>'
             + '  <td>'
             + '    <div style="float: left; width: 70%">'
             +        text
             + '    </div>'
             + '    <a href="javascript:remove(\'' + cat + '\', ' + id + ')">'
             + '      <img src="images/icons/cross.gif" alt="cross" title="Supprimer ce groupe" />'
             + '    </a>'
             + '  </td>'
             + '</tr>';
    $('#' + cat).after(html);
    update(cat);
  }

  //]]>
</script>
{/literal}

<table class="bicol" summary="Profil: Informations Polytechniciennes">
  <tr>
    <th colspan="2">
      Informations polytechniciennes
    </th>
  </tr>
  <tr>
    <td colspan="2" class="flags">
      <span class="rouge"><input type="checkbox" name="accesX" checked="checked" disabled="disabled" /></span>
      <span class="texte">privé</span>
    </td>
  </tr>
  <tr class="top">
    <td class="titre">
      <script type="text/javascript">printTitle("Section")</script>
    </td>
    <td>
      <select name="section">
        {select_db_table table="sections" valeur=$section}
      </select>
    </td>
  </tr>
  <!-- Binets -->
  <tr id="binets">
    <td class="titre">
      <script type="text/javascript">printTitle("Binet(s)")</script>
    </td>
    <td>
      <select name="binets_sel" onchange="update('binets')">
        {select_db_table table="binets_def" valeur=0 champ="text" pad='1'}
      </select>
      <a id="binets_add" href="javascript:add('binets')">{icon name="add" title="Ajouter ce binet"}</a>
    </td>
  </tr>
  {foreach item=text key=bid from=$binets}
  <tr id="binets_{$bid}">
    <td>
      <input type="hidden" name="binets[{$bid}]" value="{$text}" />
    </td>
    <td>
      <div style="float: left; width: 70%">
        {$text}
      </div>
      <a href="javascript:remove('binets', {$bid})">{icon name="cross" title="Supprimer ce binet"}</a>
    </td>
  </tr>
  {/foreach}
  <!-- Groupes X -->
  <tr id="groupesx">
    <td class="titre">Groupe(s) X</td>
    <td>
      <select name="groupesx_sel" onchange="update('groupesx')">
        {select_db_table table="groupesx_def" valeur=0 champ="text" pad='1'}
      </select>
      <a id="groupesx_add" href="javascript:add('groupesx')">{icon name="add" title="Ajouter ce groupe X"}</a>
    </td>
  </tr>
  {foreach item=text key=bid from=$groupesx}
  <tr id="groupesx_{$bid}">
    <td>
      <input type="hidden" name="groupesx[{$bid}]" value="{$text}" />
    </td>
    <td>
      <div style="float: left; width: 70%">
        {$text}
      </div>
      <a href="javascript:remove('groupesx', {$bid})">{icon name="cross" title="Supprimer ce groupe X"}</a>
    </td>
  </tr>
  {/foreach}
</table>

<script type="text/javascript">
update('groupesx');
update('binets');
</script>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
