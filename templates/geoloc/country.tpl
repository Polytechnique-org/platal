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
<?xml version="1.0" encoding="utf-8"?>
<country id="{$smarty.request.mapid}">
  <countries>
    {foreach from=$countries item="country"}
    <country id="{$country.id}" name="{$country.name}">
      <file swf="{$country.swf}" scale="{$country.scale}" xclip="{$country.xclip}" yclip="{$country.yclip}">
        <color value="{$country.color}"/>
      </file>
      {if $country.nbPop > 0 or $country.id eq 0}
      <map x="{$country.x}" y="{$country.y}" height="{$country.height}" width="{$country.width}" ratio="{$country.rat}"/>
      <icon x="{$country.xPop}" y="{$country.yPop}" nb="{$country.nbPop}" size="{$country.rad}" name="{$country.name}" green="{if $country.nbPop}{$country.yellow/$country.nbPop}{else}0{/if}" blue="0" alpha="0.7"/>
      <moreinfos url="country{$plset_search|escape_html}mapid={$country.id}"/>
      {/if}
    </country>
    {/foreach}
  </countries>
  <cities>
    {foreach from=$cities item="city"}
    <city id="{$city.id}" name="{$city.name}">
      <icon x="{$city.x}" y="{$city.y}" nb="{$city.pop}" size="{$city.size}" name="{$city.name}" green="{if $city.pop}{$city.yellow/$city.pop}{else}0{/if}" blue="0"/>
      <moreinfos url="city{$plset_search|escape_html}cityid={$city.id}"/>
    </city>
    {/foreach}
  </cities>
</country>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
