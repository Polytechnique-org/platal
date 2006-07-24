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


<div class="blocunite_tab">
  <table class="bicol" cellspacing="0" cellpadding="0" summary="Profil: Adresses personnelles">
    <tr>
      <th colspan="5">
        Adresses personnelles
      </th>
    </tr>

    {section name=i loop=$nb_adr start=1 max=$nb_adr}
    {*
    $adrid = $ordre_adrid[$i];
    $adr = &$adresses[$adrid];
    *}
    {assign var='adrid' value=$ordre_adrid[i]}
    {assign var='adr' value=$adresses.$adrid}
    <tr>
      <th colspan="5">
        <a id='adr{$adrid}'></a>
        {if $adr.nouvelle != 'new'}Adresse n°{$smarty.section.i.index}{else}Rentre ici une nouvelle adresse{/if}
        <input type="hidden" name="adrid[{$adrid}]" value="{$adrid}" />
        {if $adr.nouvelle == 'new'}
        <input type="hidden" name="numero_formulaire[{$adrid}]" value="0" />
        {else}
        <input type="hidden" name="numero_formulaire[{$adrid}]" value="{$smarty.section.i.index}" />
        {/if}
        {if $adr.nouvelle != 'new'}
        [<a href="profile/edit/{$onglet}?adrid_del[{$adrid}]=1" style="color:inherit">La supprimer !</a>]
        {/if}
      </th>
    </tr>
    {include file="include/flags.radio.tpl" name="pub[$adrid]" val=$adr.pub}
    <tr>
      <td class="left">
        &nbsp;
      </td>
      <td colspan="4" class="right">
        <em>c'est à cette adresse que je suis actuellement</em>
        <input name="adrid_active" type="radio" value="{$adrid}" {if $adr.active}checked="checked"{/if} />
      </td>
    </tr>
    {if $adr.nouvelle != 'new'}
    {assign var="titre" value="Adresse n°`$smarty.section.i.index`&nbsp;:"}
    {else}
    {assign var="titre" value="Nouvelle adresse&nbsp;:"}
    {/if}
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
        {if $tel.new_tel}
          <input type="hidden" name="new_tel{$tel.telid}[{$adrid}]" value="1"/>
        {/if}
        <span class="titre" onclick="this.style.display='none';var d = document.getElementById('tel_type{$adrid}_{$tel.telid}');d.style.display='inline';d.select();d.focus();">{$tel.tel_type}&nbsp;:</span>
        <input id="tel_type{$adrid}_{$tel.telid}" style="display:none" type="text" size="5" maxlength="20" name="tel_type{$tel.telid}[{$adrid}]" value="{$tel.tel_type}"/>
      </td>
      <td>
        <input type="text" size="19" maxlength="28" name="tel{$tel.telid}[{$adrid}]" value="{$tel.tel}" />
      </td>
      {include file="include/flags.radio.tpl" name="tel_pub`$tel.telid`[$adrid]" val=$tel.tel_pub display="mini"}
    </tr>
    {/foreach}
    <tr><td colspan="5">&nbsp;</td></tr>
    {/section}
    <tr><td colspan="5">&nbsp;</td></tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
