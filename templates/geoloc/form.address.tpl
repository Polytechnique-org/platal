{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

{if $address.geoloc}
<div class="erreur center {$prefid}_geoloc">
  Le géocodage n'a pas donné un résultat certain&nbsp;! Tu as le choix entre&nbsp;:
</div>
<div class="{$prefid}_geoloc">
  <ul>
    <li><a href="javascript:validGeoloc('{$prefid}', '{$id}', 0)" style="color: red">ton adresse (à gauche)</a>&nbsp;;</li>
    <li><a href="javascript:validGeoloc('{$prefid}', '{$id}', 1)" style="color: green">notre suggestion (à droite)</a>&nbsp;;</li>
    <li><a href="javascript:validGeoloc('{$prefid}', '{$id}', 2)"
           title="Garder le texte de l'adresse que tu as renseignée tout en utilisant les informations trouvées par le géocodage pour te localiser sur le planisphère et dans lors d'une recherche dans l'annuaire.">
      le texte de ton adresse localisé à l'endroit que nous te suggérons</a>.</li>
  </ul>
</div>
{/if}

<div>
  <textarea name="{$prefname}[text]" cols="30" rows="4" onkeyup="addressChanged('{$prefid}')"
            {if $address.geoloc}class="error"{/if}>{$address.text}</textarea>
{if $address.geoloc}
  <textarea cols="30" rows="4" class="valid {$prefid}_geoloc"
            name="{$prefname}[geoloc]">{$address.geoloc}</textarea>
{/if}
</div>
{if $address.geoloc}
<input type="hidden" name="{$prefname}[geoloc_choice]" value="1" />
<input type="hidden" name="{$prefname}[geoloc]" value="{$address.geoloc}" />
<input type="hidden" name="{$prefname}[geocodedPostalText]" value="{$address.geocodedPostalText}" />
<input type="hidden" name="{$prefname}[updateTime]" value="{$address.updateTime}" />
{/if}
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
<input type="hidden" name="{$prefname}[cedex]" value="{$address.cedex}" />
<input type="hidden" name="{$prefname}[updateTime]" value="{$address.updateTime}" />
<input type="hidden" name="{$prefname}[changed]" value="0" />
<input type="hidden" name="{$prefname}[removed]" value="0" />

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
