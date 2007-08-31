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

function restoreObject(id, pref)
{
  document.getElementById(id).style.display = '';
  document.forms.prof_annu[pref + "[removed]"].value = "0";
}

function getAddressElement(adrid, adelement)
{
  return document.forms.prof_annu["addresses[" + adrid + "][" + adelement + "]"];
}

function checkCurrentAddress(newCurrent)
{
  var hasCurrent = false;
  var i = 0;
  while (getAddressElement(i, 'pub') != null) {
    var radio = getAddressElement(i, 'current');
    var removed = getAddressElement(i, 'removed');
    if (removed.value == "1" && radio.checked) {
      radio.checked = false;
    } else if (radio.checked && radio != newCurrent) {
      radio.checked = false;
    } else if (radio.checked) {
      hasCurrent = true;
    }
    i++;
  }
  if (!hasCurrent) {
    i = 0;
    while (getAddressElement(i, 'pub') != null) {
      var radio = getAddressElement(i, 'current');
      var removed = getAddressElement(i, 'removed');
      if (removed.value != "1") {
        radio.checked= true;
        return;
      }
      i++;
    }
  }
}

function removeAddress(id, pref)
{
  removeObject(id, pref);
  checkCurrentAddress(null);
  if (document.forms.prof_annu[pref + '[datemaj]'].value != '') {
    document.getElementById(id + '_grayed').style.display = '';
  }
}

function restoreAddress(id, pref)
{
  document.getElementById(id +  '_grayed').style.display = 'none';
  checkCurrentAddress(null);
  restoreObject(id, pref);
}

function addAddress()
{
  var i = 0;
  while (getAddressElement(i, 'pub') != null) {
    i++;
  }
  $("#add_adr").before('<div id="addresses_' + i + '_cont"></div>');
  Ajax.update_html('addresses_' + i + '_cont', 'profile/ajax/address/' + i, checkCurrentAddress);
}

function addTel(id)
{
  var i = 0;
  var adid = 'addresses_' + id;
  var tel  = adid + '_tel_';
  while (document.getElementById(tel + i) != null) {
    i++;
  }
  $('#' + adid + '_add_tel').before('<div id="' + tel + i + '" style="clear: both"></div>');
  Ajax.update_html(tel + i, 'profile/ajax/tel/' + id + '/' + i);
}

function validGeoloc(id, pref)
{
  document.getElementById(id + '_geoloc').style.display = 'none';
  document.getElementById(id + '_geoloc_error').style.display = 'none';
  document.getElementById(id + '_geoloc_valid').style.display = 'none';
  document.forms.prof_annu[pref + "[parsevalid]"] = "1";
  document.forms.prof_annu[pref + "[text]"].value = document.forms.prof_annu[pref + "[geoloc]"].value;
  attachEvent(document.forms.prof_annu[pref + "[text]"], "click",
              function() { document.forms.prof_annu[pref + "[text]"].blur(); });
  document.forms.prof_annu[pref + "[text]"].className = '';
}

function validAddress(id, pref)
{
  document.getElementById(id + '_geoloc').style.display = 'none';
  document.getElementById(id + '_geoloc_error').style.display = 'none';
  document.getElementById(id + '_geoloc_valid').style.display = 'none';
  document.forms.prof_annu[pref + "[parsevalid]"] = "0";
  attachEvent(document.forms.prof_annu[pref + "[text]"], "click",
              function() { document.forms.prof_annu[pref + "[text]"].blur(); });
  document.forms.prof_annu[pref + "[text]"].className = '';
}

{/literal}
//]]></script>

{foreach key=i item=adr from=$addresses}
<div id="{"addresses_`$i`_cont"}">
{include file="profile/adresses.address.tpl" i=$i adr=$adr}
</div>
{/foreach}
{if $addresses|@count eq 0}
<div id="addresses_0">
{include file="profile/adresses.address.tpl" i=0 adr=0}
</div>
{/if}

<div id="add_adr" class="center">
  <a href="javascript:addAddress()">
    {icon name=add title="Ajouter une adresse"} Ajouter une adresse
  </a>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
