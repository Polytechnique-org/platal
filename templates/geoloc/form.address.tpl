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
    <input type="hidden" name="{$prefname}[accuracy]" value="{$address.accuracy}" />
    <input type="hidden" name="{$prefname}[postalText]" value="{$address.postalText}" />
    <input type="hidden" name="{$prefname}[postalCode]" value="{$address.postalCode}" />
    <input type="hidden" name="{$prefname}[administrativeAreaId]" value="{$address.administrativeAreaId}" />
    <input type="hidden" name="{$prefname}[subAdministrativeAreaId]" value="{$address.subAdministrativeAreaId}" />
    <input type="hidden" name="{$prefname}[localityId]" value="{$address.localityId}" />
    <input type="hidden" name="{$prefname}[countryId]" value="{$address.countryId}" />
    <input type="hidden" name="{$prefname}[latitude]" value="{$address.latitude}" />
    <input type="hidden" name="{$prefname}[longitude]" value="{$address.longitude}" />
    <input type="hidden" name="{$prefname}[north]" value="{$address.north}" />
    <input type="hidden" name="{$prefname}[south]" value="{$address.south}" />
    <input type="hidden" name="{$prefname}[east]" value="{$address.east}" />
    <input type="hidden" name="{$prefname}[west]" value="{$address.west}" />
    <input type="hidden" name="{$prefname}[changed]" value="0" />
    <input type="hidden" name="{$prefname}[removed]" value="0" />
  </td>
  <td>
  {if t($address.latitude)}
    <img src="https://maps.googleapis.com/maps/api/staticmap?size=300x100&amp;markers=color:{$profile->promoColor()}%7C{$address.longitude},{$address.latitude}&amp;zoom=12&amp;sensor=false"
         alt="Position de l'adresse" />
    <br />
    <small><a href="javascript:deleteGeocoding()">{icon name=cross title="Adresse mal localisée"} Signaler que le repère est mal placé</a></small>
  {/if}
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
