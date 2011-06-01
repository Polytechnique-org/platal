{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

{if t($validation)}
<div style="float: left">
{else}
<tr{if t($class)} class="{$class}"{/if}>
  <td>
{/if}
    <textarea name="{$prefname}[text]" cols="30" rows="4" onchange="addressChanged('{$prefid}','{$profile->promoColor()}')">{$address.text}</textarea>
    <input type="hidden" name="{$prefname}[postalText]" value="{$address.postalText}" />
    <input type="hidden" name="{$prefname}[types]" value="{$address.types}" />
    <input type="hidden" name="{$prefname}[formatted_address]" value="{$address.formatted_address}" />
    <input type="hidden" name="{$prefname}[latitude]" value="{$address.latitude}" />
    <input type="hidden" name="{$prefname}[longitude]" value="{$address.longitude}" />
    <input type="hidden" name="{$prefname}[southwest_latitude]" value="{$address.southwest_latitude}" />
    <input type="hidden" name="{$prefname}[southwest_longitude]" value="{$address.southwest_longitude}" />
    <input type="hidden" name="{$prefname}[northeast_latitude]" value="{$address.northeast_latitude}" />
    <input type="hidden" name="{$prefname}[northeast_longitude]" value="{$address.northeast_longitude}" />
    <input type="hidden" name="{$prefname}[location_type]" value="{$address.location_type}" />
    <input type="hidden" name="{$prefname}[partial_match]" value="{$address.partial_match}" />
    <input type="hidden" name="{$prefname}[componentsIds]" value="{$address.componentsIds}" />
    <input type="hidden" name="{$prefname}[changed]" value="0" />
    <input type="hidden" name="{$prefname}[removed]" value="0" />
    <input type="hidden" name="{$prefname}[geocoding_calls]" value="{$address.geocoding_calls}" />
    <input type="hidden" name="{$prefname}[geocoding_date]" value="{$address.geocoding_date}" />
{if t($validation)}
    <br />
    <label><input type="checkbox" name="{$prefname}[modified]"{if $valid->modified} checked="checked"{/if} />Utiliser la version modifiée</label>
</div>
<div style="float: right">
{else}
  </td>
  <td>
{/if}
  <div id="{$prefid}_static_map_url" {if !t($address.latitude)}style="display: none"{/if}>
    <img src="{insert name="getStaticMapURL" latitude=$address.latitude longitude=$address.longitude color=$profile->promoColor()}" alt="Position de l'adresse" />
    {if t($geocoding_removal)}
    <br />
    <small id="{$prefid}_geocoding_removal">
    {if !t($address.request)}
      <label><input type="checkbox" name="{$prefname}[request]" onclick="return deleteGeocoding('{$prefid}')" /> Signaler que le repère est mal placé</label>
    {else}
    Localisation en attente de validation.
    <input type="hidden" name="{$prefname}[request]" value="{$address.request}" />
    {/if}
    </small>
    {/if}
  </div>
{if t($validation)}
</div>
<div style="clear: both"></div>
{else}
  </td>
</tr>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
