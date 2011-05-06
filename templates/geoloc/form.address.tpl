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

<tr{if t($class)} class="{$class}"{/if}>
  <td>
    <textarea name="{$prefname}[text]" cols="30" rows="4" onkeyup="addressChanged('{$prefid}')">{$address.text}</textarea>
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
  </td>
  <td>
  {if t($address.latitude)}
    <img src="https://maps.googleapis.com/maps/api/staticmap?size=300x100&amp;markers=color:{$profile->promoColor()}%7C{$address.latitude},{$address.longitude}&amp;zoom=12&amp;sensor=false"
         alt="Position de l'adresse" />
    <br />
    <small><a href="javascript:deleteGeocoding()">{icon name=cross title="Adresse mal localisée"} Signaler que le repère est mal placé</a></small>
  {/if}
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
