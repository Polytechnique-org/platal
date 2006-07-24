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

    {if $adr.geoloc}
    <tr>
      <td class="cold" colspan="5">
        <input type="hidden" name="change{$adrid}" value="0"/>
	<span class="erreur">La geolocalisation n'a pas donné un résultat certain, valide la nouvelle adresse ou modifie l'ancienne pour que ton adresse puisse être prise en compte.</span><br />
	<script type="text/javascript">setTimeout("document.location += '#adr{$adrid}'", 10);</script>
        <textarea name="txt[{$adrid}]" cols="30" rows="4" onchange="form.change{$adrid}.value=1"
	{if !$adr.cityid}style="background:#FAA"{/if}
	>{$adr.txt}</textarea>
	  <textarea cols="30" rows="4"
	  style="border:inherit;background:#AFA"
	  onclick="blur()"
	>{$adr.geoloc}</textarea><p class="right">
	[<a href="{$smarty.server.PHP_SELF}?old_tab={$smarty.request.old_tab}&amp;parsevalid[{$adrid}]=1&amp;modifier=1">Valider</a>]
	</p>
    {else}
    <tr class="center">
      <td class="cold" colspan="5">
        <input type="hidden" name="change{$adrid}" />
        <textarea name="txt[{$adrid}]" cols="43" rows="4" onchange="form.change{$adrid}.value=1"
	{if $adr.nouvelle != 'new' && !$adr.cityid}style="background:#FAA"{/if}
	>{$adr.txt}</textarea>
	{/if}
        <input type="hidden" name="cityid[{$adrid}]" value="{$adr.cityid}" />
        <input type="hidden" name="adr1[{$adrid}]" value="{$adr.adr1}" />
        <input type="hidden" name="adr2[{$adrid}]" value="{$adr.adr2}" />
        <input type="hidden" name="adr3[{$adrid}]" value="{$adr.adr3}" />
        <input type="hidden" name="postcode[{$adrid}]" value="{$adr.postcode}"/>
        <input type="hidden" name="city[{$adrid}]" value="{$adr.city}" />
        <input type="hidden" name="country[{$adrid}]" value="{$adr.country}" />
        <input type="hidden" name="region[{$adrid}]" value="{$adr.region}" />
      </td>
    </tr>
