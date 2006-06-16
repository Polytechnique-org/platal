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
  function medal_add()
  {
    var selid = document.forms.prof_annu.medal_sel.selectedIndex;
    document.forms.prof_annu.medal_id.value = document.forms.prof_annu.medal_sel.options[selid].value;
    document.forms.prof_annu.medal_op.value = "ajouter";
    document.forms.prof_annu.submit();
  }

  function medal_del( id )
  {
    document.forms.prof_annu.medal_id.value = id;
    document.forms.prof_annu.medal_op.value = "retirer";
    document.forms.prof_annu.submit();
  }
  //]]>
</script>
{/literal}

<div class="blocunite_tab">
  <input type="hidden" value="" name="medal_op" />
  <input type="hidden" value="" name="medal_id" />
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
        <img src='{rel}/images/medals/{$m.img}' width="32" alt="{$m.medal}" title="{$m.medal}" />
      </td>
      <td class="colm">
        <span class="valeur">{$m.medal}</span><br />
        {if $grades[$m.id]|@count}
        <select name="grade[{$m.id}]">
          <option value='0'>-- non précisé --</option>
          {foreach from=$grades[$m.id] item=g}
          <option value='{$g.gid}' {if $g.gid eq $m.gid}selected='selected'{/if}>{$g.text}</option>
          {/foreach}
        </select>
        {else}
        -- non précisé --
        {/if}
      </td>
      <td class="cold">
        <span class="lien">
          <a href="javascript:medal_del({$m.id});">retirer</a>
        </span>
      </td>
    </tr>
    {/foreach}
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="colm">
        <select name="medal_sel">
          <option value=''></option>
          {foreach from=$medal_list key=type item=list}
          <optgroup label="{$trad[$type]}">
            {foreach from=$list item=m}
            <option value="{$m.id}">{$m.text}</option>
            {/foreach}
          </optgroup>
          {/foreach}
        </select>
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
