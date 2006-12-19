{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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
  var valid = new array();
  function medal_add()
  {
    var selid = document.forms.prof_annu.medal_sel.selectedIndex;
    document.forms.prof_annu.medal_id.value = document.forms.prof_annu.medal_sel.options[selid].value;
    document.forms.prof_annu.grade_id.value = document.forms.prof_annu.grade_sel.value;
    document.forms.prof_annu.medal_op.value = "ajouter";
    document.forms.prof_annu.submit();
  }

  function medal_del( id )
  {
    document.forms.prof_annu.medal_id.value = id;
    document.forms.prof_annu.medal_op.value = "retirer";
    document.forms.prof_annu.submit();
  }

  function medal_cancel(stamp)
  {
    document.forms.prof_annu.medal_id.value = stamp;
    document.forms.prof_annu.medal_op.value = "annuler";
    document.forms.prof_annu.submit();
  }
  var subgrades = new array();
  function getoption( select_input, j)
  {
    if (!document.all)
    {
      return select_input.options[j];
    }
    else
    {
      return j;
    }
  }
  function medal_grades( sel_medal )
  {
    var subg = subgrades[sel_medal.selectedIndex];
    document.getElementById("grade_sel_div").style.display = subg?"inline":"none";
    if (!subg) return;
    var select = document.getElementById("grade_sel");
    while (select.length > 1)
    {
      select.remove(1);
    }

    for (i=0; i < subg.length; i++)
    {
      var dmc = document.createElement("option");
      dmc.text= subg[i][1];
      dmc.value = subg[i][0];
      select.add(dmc,getoption(select,i));
    }
    var vide = document.createElement("option");
    vide.text = "";
    vide.value = 0;
    select.add(vide,getoption(select,0));
    select.remove(subg.length+1);
  }
  //]]>
{/literal}
{foreach from=$medal_list key=type item=list}
  {foreach from=$list item=m}{if $grades[$m.id]|@count}
    subgrades[{$m.id}] = new array({$grades[$m.id]|@count});
    i = 0;
    {foreach from=$grades[$m.id] item=g}
      subgrades[{$m.id}][i] = [{$g.gid},"{$g.text}"];
      i++;
    {/foreach}
  {/if}{/foreach}
{/foreach}

</script>

{if $smarty.request.medal_op eq "ajouter"}
<div class="erreur">
	Ta demande a bien été prise en compte, elle sera validée prochainement par un administrateur.
</div>
{/if}
<div class="blocunite_tab">
  <input type="hidden" value="" name="medal_op" />
  <input type="hidden" value="" name="medal_id" />
  <input type="hidden" value="" name="grade_id" />
  <table class="bicol" cellspacing="0" cellpadding="0">
    <tr>
      <th colspan="3">
        Médailles, Décorations, Prix, ...
      </th>
    </tr>
    <tr>
      <td colspan="3" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="medals_pub"{if $medals_pub eq 'public'} checked="checked"{/if} />
            </td>
            <td class="texte">
              ces informations sont normalement publiques (JO, ...) mais tu peux choisir de les associer a ta fiche publique
            </td>
          </tr>
        </table>
      </td>
    </tr>
    {foreach from=$medals item=m}
    <tr>
      <td class="colg">
        <img src='images/medals/{$m.img}' width="32" alt="{$m.medal}" title="{$m.medal}" />
      </td>
      <td class="colm">
        <span class="valeur">{$m.medal}</span><br />
        {if $grades[$m.id]|@count}
          {foreach from=$grades[$m.id] item=g}
            {if $g.gid eq $m.gid}{$g.text}{/if}
          {/foreach}
        {/if}
      </td>
      <td class="cold">
        <span class="lien">
          <a href="javascript:medal_del({$m.id});">retirer</a>
        </span>
      </td>
    </tr>
    {/foreach}
    {foreach from=$medals_valid item=v}
    <tr>
      <td class="colg">
        <img
          {foreach from=$medal_list item=list}
            {foreach from=$list item=m}
            {if $m.id eq $v->mid}src="images/medals/{$m.img}"{/if}
            {/foreach}
          {/foreach}
        title="Validation" alt="Validation" width="32" />
      <td class="colm">
        <span class="valeur">
          {foreach from=$medal_list item=list}
            {foreach from=$list item=m}
            {if $m.id eq $v->mid}{$m.text}&nbsp;<em>(en attente de validation)</em>{/if}
            {/foreach}
          {/foreach}
        </span><br />
        {foreach from=$grades key=mid item=grd}
          {if $mid eq $v->mid}
          {foreach from=$grd item=g}
            {if $g.gid eq $v->gid}{$g.text}{/if}
          {/foreach}
          {/if}
        {/foreach}
      </td>
      <td class="cold">
        <span class="lien">
          <a href="javascript:medal_cancel({$v->stamp});">annuler</a>
        </span>
      </tr>
    </tr>
    {/foreach}
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="colm">
        <select name="medal_sel" onchange="medal_grades(this)">
          <option value=''></option>
          {foreach from=$medal_list key=type item=list}
          <optgroup label="{$trad[$type]}">
            {foreach from=$list item=m}
            <option value="{$m.id}">{$m.text}</option>
            {/foreach}
          </optgroup>
          {/foreach}
        </select>
        <div id="grade_sel_div" style="display:none"><br/>
          <select name="grade_sel" id="grade_sel">
            <option value="0"></option>
          </select>
        </div>
      </td>
      <td class="cold">
        <span class="lien">
          <a href="javascript:medal_add();">ajouter</a>
        </span>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
