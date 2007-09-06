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
var subgrades = new array();
var names     = new array();

function update()
{
  var val = document.forms.prof_annu['medal_sel'].value;
  if (val == '' || document.getElementById('medal_' + val) != null) {
    document.getElementById('medal_add').style.display = 'none';
  } else {
    document.getElementById('medal_add').style.display = '';
  }
}

function getMedalName(id)
{
  document.getElementById('medal_name_' + id).innerHTML = names[id];
}

function buildGrade(id, current)
{
  var grade;
  var subg = subgrades[id];
  var obj  = $('#medal_grade_' + id);
  if (!subg) {
    obj.prepend('<input type="hidden" name="medals[' + id + '][grade]" value="0" />');
  } else {
    var html = 'Agrafe : <select name="medals[' + id + '][grade]">';
    html += '<option value="0">Non précisée</option>';
    for (grade = 0 ; grade < subg.length ; grade++) {
      html += '<option value="' + subg[grade][0] + '">' + subg[grade][1] + '</option>';
    }

    html += '</select>';
    obj.prepend(html);
  }
}

function makeAddProcess(id)
{
  return function(data)
         {
           $('#medals').after(data);
           update();
           getMedalName(id);
           buildGrade(id, 0);
         };
}

function add()
{
  var id = document.forms.prof_annu['medal_sel'].value;
  $.get(platal_baseurl + 'profile/ajax/medal/' + id, makeAddProcess(id));
}

function remove(id)
{
  $("#medal_" + id).remove();
  update();
}

{/literal}
{foreach from=$medal_list key=type item=list}
  {foreach from=$list item=m}
  names[{$m.id}] = "{$m.text}";
  {if $grades[$m.id]|@count}
    names[{$m.id}] = "{$m.text}";
    subgrades[{$m.id}] = new array({$grades[$m.id]|@count});
    {foreach from=$grades[$m.id] item=g}
      subgrades[{$m.id}][{$g.gid-1}] = [{$g.gid},"{$g.text}"];
    {/foreach}
  {/if}{/foreach}
{/foreach}
</script>

<table class="bicol">
  <tr>
    <th>
      Médailles, Décorations, Prix, ...
    </th>
  </tr>
  <tr>
    <td>
      <div class="flags">
        <div class="vert" style="float: left">
          <input type="checkbox" name="medals_pub"{if $medals_pub eq 'public'} checked="checked"{/if} />
        </div>
        <div class="texte">
          ces informations sont normalement publiques (JO, ...) mais tu peux choisir de les associer a ta fiche publique
        </div>
      </div>
      <div style="clear: both; margin-top: 0.2em" id="medals">
        <select name="medal_sel" onchange="update()">
          <option value=''></option>
          {foreach from=$medal_list key=type item=list}
          <optgroup label="{$trad[$type]}...">
            {foreach from=$list item=m}
            <option value="{$m.id}">{$m.text}</option>
            {/foreach}
          </optgroup>
          {/foreach}
        </select>
        <span id="medal_add">
          <a href="javascript:add();">{icon name=add title="Ajouter cette médaille"}</a>
        </span>
      </div>
      {foreach from=$medals item=medal key=id}
      {include file="profile/deco.medal.tpl" medal=$medal id=$id}
      {/foreach}
    </td>
  </tr>
</table>

<script type="text/javascript">
update();
</script>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
