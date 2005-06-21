{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

    {if $adr.geoloc}
    <tr>
      <td class="cold" colspan="5">
        <input type="hidden" name="change{$adrid}" value="0"/>
	<span class="erreur" wrap>La geolocalisation n'a pas donné un résultat certain, valide la nouvelle adresse ou modifie l'ancienne pour que ton adresse puisse être prise en compte.</span><br />
        <textarea name="txt[{$adrid}]" cols="30" rows="4" onchange="form.change{$adrid}.value=1"
	{if !$adr.cityid}style="background:#FAA"{/if}
	>{$adr.txt}</textarea>
	  <textarea cols="30" rows="4"
	  style="border:inherit;background:#AFA"
	  onclick="blur()"
	>{$adr.geoloc}</textarea><p class="right">
	[<a href="{$smarty.server.PHP_SELF}?old_tab={$smarty.request.old_tab}&amp;parsevalid[{$adrid}]=1&amp;modifier=1">Valider</a>]
	</p>
      </td>
    </tr>
    {else}
    <tr class="center">
      <td class="cold" colspan="2">
        <input type="hidden" name="change{$adrid}" />
        <textarea name="txt[{$adrid}]" cols="43" rows="4" onchange="form.change{$adrid}.value=1"
	{if $adr.nouvelle != 'new' && !$adr.cityid}style="background:#FAA"{/if}
	>{$adr.txt}</textarea>
      </td>
    </tr>
    {/if}
    <tr style="display:none">
      <td class="colg">
        &nbsp;
      </td>
      <td>
        <input type="hidden" name="cityid[{$adrid}]" value="{$adr.cityid}" />
        <input type="text" name="adr1[{$adrid}]" size="43" maxlength="88" value="{$adr.adr1}" />
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adr2[{$adrid}]" size="43" maxlength="88" value="{$adr.adr2}" />
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adr3[{$adrid}]" size="43" maxlength="88" value="{$adr.adr3}" />
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        <span class="titre">Code postal / Ville</span><br />
      </td>
      <td class="cold">
        <input type="text" name="postcode[{$adrid}]" value="{$adr.postcode}" size="7" maxlength="18" />
        &nbsp;
        <input type="text" name="city[{$adrid}]" value="{$adr.city}" size="32" maxlength="78" />
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        <span class="titre">Pays</span>
      </td>
      <td class="cold">
        <select name="country[{$adrid}]">
          {geoloc_country country=$adr.country}
        </select>
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        <span class="titre">Région ou département</span><br />
        <span class="comm">(selon pays)</span>
      </td>
      <td class="cold">
        <select name="region[{$adrid}]">
          {geoloc_region country=$adr.country region=$adr.region}
        </select>
      </td>
    </tr>
