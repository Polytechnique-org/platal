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

{assign var=prefname value="addresses[$i]"}
{assign var=prefid value="addresses_$i"}
{if !hasPerm('directory_private') && ($address.pub eq 'private') && !$new}
{assign var=hiddenaddr value=true}
{else}
{assign var=hiddenaddr value=false}
{/if}

<table class="bicol" style="display: none; margin-bottom: 1em" id="{$prefid}_grayed">
  <tr>
    <th class="grayed">
      <div style="float: right">
        <a href="javascript:toggleAddress('{$i}',0)">{icon name=arrow_refresh title="Restaurer l'adresse"}</a>
      </div>
      Restaurer l'adresse n°{$i+1}
    </th>
  </tr>
</table>
<table class="bicol" style="margin-bottom: 1em" id="{$prefid}">
  <tr>
    <th>
      <div style="float: left">
        <label>
          <input name="{$prefname}[current]" type="radio" {if $address.current}checked="checked"{/if}
                      onchange="checkCurrentAddress({$i})" />
          <span class="smaller" style="font-weight: normal">actuelle</span>
        </label>
      </div>
      <div style="float: right">
        <a href="javascript:toggleAddress('{$i}',1)">
          {icon name=cross title="Supprimer l'adresse"}
        </a>
      </div>
      Adresse n°{$i+1}{if $hiddenaddr} (masquée){/if}
    </th>
  </tr>
  <tr {if $hiddenaddr}style="display: none"{/if}>
    <td>
      <div style="margin-bottom: 0.2em" class="flags">
        {include file="include/flags.radio.tpl" name="`$prefname`[pub]" val=$address.pub}
      </div>
      <div style="clear: both"></div>
      <div style="float: left">
      {include file="geoloc/form.address.tpl" prefname=$prefname prefid=$prefid address=$address id=$i}
      </div>
      <div style="float: left">
        <div>
          <label>
            <input type="radio" name="{$prefname}[temporary]" value="0"
                   {if !$address.temporary}checked="checked"{/if} />
            permanente
          </label>
          <label>
            <input type="radio" name="{$prefname}[temporary]" value="1"
                   {if $address.temporary}checked="checked"{/if} />
            temporaire
          </label>
        </div>
        <div>
          <label>
            <input type="radio" name="{$prefname}[secondary]" value="0"
                   {if !$address.secondary}checked="checked"{/if} />
            ma résidence principale
          </label>
          <label>
            <input type="radio" name="{$prefname}[secondary]" value="1"
                   {if $address.secondary}checked="checked"{/if} />
            une résidence secondaire
          </label>
        </div>
        <div>
          <label>
            <input type="checkbox" name="{$prefname}[mail]" {if $address.mail}checked="checked"{/if} />
            on peut m'y envoyer du courrier par la poste
          </label>
        </div>
        <div>
          <label>
            Commentaire&nbsp;:
            <input type="text" size="35" maxlength="100"
                   name="{$prefname}[comment]" value="{$address.comment}" />
          </label>
        </div>
      </div>
    </td>
  </tr>
  <tr class="pair" {if $hiddenaddr}style="display: none"{/if}>
    <td>
      {foreach from=$address.phones key=t item=tel}
        <div id="{"`$prefid`_tel_`$t`"}" style="clear: both">
          {include file="profile/phone.tpl" prefname="`$prefname`[phones]"
                   prefid="`$prefid`_tel" telid=$t tel=$tel}
        </div>
      {/foreach}
      {if $address.phones|@count eq 0}
        <div id="{"`$prefid`_tel_0"}" style="clear: both">
          {include file="profile/phone.tpl" prefname="`$prefname`[phones]" prefid="`$prefid`_tel" telid=0 tel=0}
        </div>
      {/if}
      <div id="{$prefid}_tel_add" class="center" style="clear: both; padding-top: 4px">
        <a href="javascript:addTel('{$prefid}_tel','{$prefname}[phones]')">
          {icon name=add title="Ajouter un numéro de téléphone"} Ajouter un numéro de téléphone
        </a>
      </div>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
