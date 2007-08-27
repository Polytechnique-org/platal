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

<input type="hidden" name="{$name}[changed]" value="0"/>
{if $adr.geoloc}
<div class="erreur">
  La geolocalisation n'a pas donné un résultat certain, valide la nouvelle adresse
  ou modifie l'ancienne pour que ton adresse puisse être prise en compte.
</div>
<script type="text/javascript">setTimeout("document.location += '#{$adid}'", 10);</script>
{/if}
<textarea name="{$name}[text]" cols="30" rows="4"
          onchange="form['{$name}[changed]'].value=1"
          {if !$adr.cityid}class="error"{/if}
          >{$adr.text}</textarea>
{if $adr.geoloc}
<textarea cols="30" rows="4"
          class="valid"
          onclick="blur()"
          >{$adr.geoloc}</textarea>
<p class="center">
  <input type="submit" name="{$name}[parsevalid]" value="Valider l'adresse" />
</p>
{/if}
<input type="hidden" name="{$name}[cityid]" value="{$adr.cityid}" />
<input type="hidden" name="{$name}[adr1]" value="{$adr.adr1}" />
<input type="hidden" name="{$name}[adr2]" value="{$adr.adr2}" />
<input type="hidden" name="{$name}[adr3]" value="{$adr.adr3}" />
<input type="hidden" name="{$name}[postcode]" value="{$adr.postcode}"/>
<input type="hidden" name="{$name}[city]" value="{$adr.city}" />
<input type="hidden" name="{$name}[country]" value="{$adr.country}" />
<input type="hidden" name="{$name}[region]" value="{$adr.region}" />
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
