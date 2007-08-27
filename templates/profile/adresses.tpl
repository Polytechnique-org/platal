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
function removeAddress(id, pref)
{
  document.getElementById(id).style.display = "none";
  document.forms.prof_annu[pref + "[removed]"].value = "1";
}

{/literal}
//]]></script>

{foreach key=i item=adr from=$addresses}
{assign var=adpref value="addresses[$i]"}
{assign var=adid value="addresses_$i"}
<table class="bicol" id="{$adid}" style="margin-bottom: 1em">
  <tr>
    <th>
      <div style="float: left">
        <input name="{$adpref}[active]" type="radio" value="{$adr.id}" {if $adr.current}checked="checked"{/if}
               id="{$adid}_active"/>
        <label for="{$adid}_active" class="smaller" style="font-weight: normal">actuelle</label>
      </div>
      <div style="float: right">
        <a href="javascript:removeAddress('{$adid}', '{$adpref}')">{icon name=cross title="Supprimer l'adresse"}</a>
      </div>
      Adresse n°{$i + 1}
    </th>
  </tr>
  <tr>
    <td>
      <div>{include file="include/flags.radio.tpl" name="$adpref[pub]" notable=true val=$adr.pub}</div>
      <div {if !$adr.geoloc}class="center"{/if}>{include file="geoloc/form.address.tpl" name=$adpref id=$adid adr=$adr}</div>
    </td>
  </tr>
</table>
<input type="hidden" name="{$adpref}[removed]" value="0"/>
{/foreach}

{*
    {section name=i loop=$nb_adr start=1 max=$nb_adr}
    {assign var='adrid' value=$ordre_adrid[i]}
    {assign var='adr' value=$adresses.$adrid}
    {include file="geoloc/form.address.tpl" adr=$adr titre=$titre}
    <tr>
      <td class="colg">
        <span class="titre">Adresse:</span>
      </td>
      <td class="cold" colspan="4">
        <input type="radio" name="temporaire[{$adrid}]" value="0" {if !$adr.temporaire}checked="checked"{/if} />
        permanente
        <input type="radio" name="temporaire[{$adrid}]" value="1" {if $adr.temporaire}checked="checked"{/if} />
        temporaire
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold" colspan="4">
        <input type="radio" name="secondaire[{$adrid}]" value="0" {if !$adr.secondaire}checked="checked"{/if} />
        ma résidence principale
        <input type="radio" name="secondaire[{$adrid}]" value="1" {if $adr.secondaire}checked="checked"{/if} />
        une résidence secondaire
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold" colspan="4">
        <input type="checkbox" name="courrier[{$adrid}]" value="1" {if $adr.courrier}checked="checked"{/if} /> on peut m'y envoyer du courrier par la poste
      </td>
    </tr>
    {foreach from=$adr.tels item="tel"}
    <tr class="flags">
      <td class="colg">
        <input type="hidden" name="telid{$tel.telid}[{$adrid}]" value="{$tel.telid}"/>
        {if $tel.new_tel && !$tel.tel}
          <input type="hidden" name="new_tel{$tel.telid}[{$adrid}]" value="1"/>
        {/if}
        <span class="titre" onclick="this.style.display='none';var d = document.getElementById('tel_type{$adrid}_{$tel.telid}');d.style.display='inline';d.select();d.focus();">{$tel.tel_type}&nbsp;:</span>
        <input id="tel_type{$adrid}_{$tel.telid}" style="display:none" type="text" size="5" maxlength="30" name="tel_type{$tel.telid}[{$adrid}]" value="{$tel.tel_type}"/>
      </td>
      <td>
        <input type="text" size="19" maxlength="28" name="tel{$tel.telid}[{$adrid}]" value="{$tel.tel}" />
        {if $tel.tel}
        	<a href="profile/edit/{$onglet}?adrid={$adrid}&telid={$tel.telid}&deltel=1">{icon name=cross title="Supprimer ce tél."}</a>
    	{/if}
      </td>
      {include file="include/flags.radio.tpl" name="tel_pub`$tel.telid`[$adrid]" val=$tel.tel_pub display="mini"}
    </tr>
    {/foreach}
    <tr><td colspan="5">&nbsp;</td></tr>
    {/section}
    <tr><td colspan="5">&nbsp;</td></tr>
  </table>
*}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
