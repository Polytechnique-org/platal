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
function removeObject(id, pref)
{
  document.getElementById(id).style.display = "none";
  document.forms.prof_annu[pref + "[removed]"].value = "1";
}

{/literal}
//]]></script>

{foreach key=i item=adr from=$addresses}
{assign var=adpref value="addresses[$i]"}
{assign var=adid value="addresses_$i"}
<input type="hidden" name="{$adpref}[removed]" value="0"/>
<table class="bicol" id="{$adid}" style="margin-bottom: 1em">
  <tr>
    <th>
      <div style="float: left">
        <input name="{$adpref}[active]" type="radio" value="{$adr.id}" {if $adr.current}checked="checked"{/if}
               id="{$adid}_active"/>
        <label for="{$adid}_active" class="smaller" style="font-weight: normal">actuelle</label>
      </div>
      <div style="float: right">
        <a href="javascript:removeObject('{$adid}', '{$adpref}')">{icon name=cross title="Supprimer l'adresse"}</a>
      </div>
      Adresse n°{$i + 1}
    </th>
  </tr>
  <tr>
    <td>
      <div>{include file="include/flags.radio.tpl" name="$adpref[pub]" notable=true val=$adr.pub}</div>
      <div style="clear: both"></div>
      <div style="float: left">{include file="geoloc/form.address.tpl" name=$adpref id=$adid adr=$adr}</div>
      <div style="float: right">
        <div>
          <input type="radio" name="{$adpref}[temporary]" id="{$adid}_temp_0" value="0"
                 {if !$adr.temporary}checked="checked"{/if} /><label for="{$adid}_temp_0">permanente</label>
          <input type="radio" name="{$adpref}[temporary]" id="{$adid}_temp_1" value="1"
                 {if $adr.temporary}checked="checked"{/if} /><label for="{$adid}_temp_1">temporaire</label>
        </div>
        <div>
          <input type="radio" name="{$adpref}[secondaire]" id="{$adid}_sec_0" value="0"
                 {if !$adr.secondaire}checked="checked"{/if} /><label for="{$adid}_sec_0">ma résidence principale</label>
          <input type="radio" name="{$adpref}[secondaire]" id="{$adid}_sec_1" value="1"
                 {if $adr.secondaire}checked="checked"{/if} /><label for="{$adid}_sec_1">une résidence secondaire</label>
        </div>
        <div>
          <input type="checkbox" name="{$adpref}[mail]" id="{$adid}_mail"
                 {if $adr.mail}checked="checked"{/if} />
          <label for="{$adid}_mail">on peut m'y envoyer du courrier par la poste</label>
        </div>
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td>
      {foreach from=$adr.tel key=t item=tel}
      {assign var=telpref value="`$adpref`[tel][`$t`]"}
      {assign var=telid   value="`$adid`_tel_`$t`"}
      <div id="{$telid}" style="clear: both">
        <div style="float: right" class="flags">
          {include file="include/flags.radio.tpl" name="`$telpref`[pub]" val=$tel.pub display="div"}
        </div>
        <span class="titre">N°{$t}</span>
        <input type="hidden" name="{$telpref}[removed]" value="0" />
        <input type="text" size="10" maxlength="30" name="{$telpref}[type]" value="{$tel.type}" />
        <input type="text" size="19" maxlength="28" name="{$telpref}[tel]" value="{$tel.tel}" />
        <a href="javascript:removeObject('{$telid}', '{$telpref}')">
          {icon name=cross title="Supprimer ce numéro de téléphone"}
        </a>
      </div>
      {/foreach}
    </td>
  </tr>
</table>
{/foreach}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
