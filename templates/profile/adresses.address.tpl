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

{if $ajaxadr}
<?xml version="1.0" encoding="utf-8"?>
{/if}
{assign var=adpref value="addresses[$i]"}
{assign var=adid value="addresses_$i"}
<input type="hidden" name="{$adpref}[removed]" value="0"/>
<input type="hidden" name="{$adpref}[datemaj]" value="{$adr.datemaj}"/>
<table class="bicol" style="display: none; margin-bottom: 1em" id="{$adid}_grayed">
  <tr>
    <th class="grayed">
      <div style="float: right">
        <a href="javascript:restoreAddress('{$adid}', '{$adpref}')">{icon name=arrow_refresh title="Restaurer l'adresse"}</a>
      </div>
      Restaurer l'adresse n°{$i+1}
    </th>
  </tr>
</table>
<table class="bicol" style="margin-bottom: 1em" id="{$adid}">
  <tr>
    <th>
      <div style="float: left">
        <input name="{$adpref}[current]" type="radio" value="1" {if $adr.current}checked="checked"{/if}
               id="{$adid}_current" onchange="checkCurrentAddress(this); return true" />
        <label for="{$adid}_current" class="smaller" style="font-weight: normal">actuelle</label>
      </div>
      <div style="float: right">
        <a href="javascript:removeAddress('{$adid}', '{$adpref}')">{icon name=cross title="Supprimer l'adresse"}</a>
      </div>
      Adresse n°{$i+1}
    </th>
  </tr>
  <tr>
    <td>
      <div style="margin-bottom: 0.2em" class="flags">
        {include file="include/flags.radio.tpl" name="`$adpref`[pub]" notable=true val=$adr.pub display="div"}
      </div>
      <div style="clear: both"></div>
      <div style="float: left">{include file="geoloc/form.address.tpl" name=$adpref id=$adid adr=$adr}</div>
      <div style="float: left">
        <div>
          <input type="radio" name="{$adpref}[temporary]" id="{$adid}_temp_0" value="0"
                 {if !$adr.temporary}checked="checked"{/if} /><label for="{$adid}_temp_0">permanente</label>
          <input type="radio" name="{$adpref}[temporary]" id="{$adid}_temp_1" value="1"
                 {if $adr.temporary}checked="checked"{/if} /><label for="{$adid}_temp_1">temporaire</label>
        </div>
        <div>
          <input type="radio" name="{$adpref}[secondaire]" id="{$adid}_sec_0" value="0"
                 {if !$adr.secondaire}checked="checked"{/if} /><label for="{$adid}_sec_0">ma résidence principale</label>
          <input type="radio" name="{$adpref}[secondaire]" id="{$adid}_sec_1" value="1"
                 {if $adr.secondaire}checked="checked"{/if} /><label for="{$adid}_sec_1">une résidence secondaire</label>
        </div>
        <div>
          <input type="checkbox" name="{$adpref}[mail]" id="{$adid}_mail"
                 {if $adr.mail}checked="checked"{/if} />
          <label for="{$adid}_mail">on peut m'y envoyer du courrier par la poste</label>
        </div>
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td>
      {foreach from=$adr.tel key=t item=tel}
      <div id="{"`$adid`_tel_`$t`"}" style="clear: both">
      {include file="profile/adresses.tel.tpl" t=$t tel=$tel}
      </div>
      {/foreach}
      {if $adr.tel|@count eq 0}
      <div id="{"`$adid`_tel_0"}" style="clear: both">
      {include file="profile/adresses.tel.tpl" t=0 tel=0}
      </div>
      {/if}
      <div id="{$adid}_add_tel" class="center" style="clear: both">
        <a href="javascript:addTel({$i})">
          {icon name=add title="Ajouter un numéro de téléphone"} Ajouter un numéro de téléphone
        </a>
      </div>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
