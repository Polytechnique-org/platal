{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

{assign var=telpref value="`$prefname`[`$telid`]"}
{assign var=id value="`$prefid`_`$telid`"}
{if $isMe || hasPerm('admin') || empty($tel.display|smarty:nodefaults)}
  {assign var=hiddentel value=false}
{elseif hasPerm('directory_hidden') || (($tel.pub neq 'hidden') && ($tel.pub neq 'private'))}
  {assign var=hiddentel value=false}
{elseif hasPerm('directory_private') && ($tel.pub neq 'hidden')}
  {assign var=hiddentel value=false}
{else}
  {assign var=hiddentel value=true}
{/if}
<div class="titre" style="float: left; width: 2.5em">N°{$telid+1}</div>
<div style="float: left;">
  {if $hiddentel}
  Numéro {if $tel.type eq 'fixed'}fixe{elseif $tel.type eq 'mobile'}de mobile{else}de fax{/if} (masqué)
  <input type="hidden" name="{$telpref}[type]" value="{$tel.type}" />
  <input type="hidden" name="{$telpref}[display]" value="{$tel.display}" />
  {else}
  <select name="{$telpref}[type]">
    <option value="fixed"{if $tel.type eq 'fixed'} selected="selected"{/if}>Fixe</option>
    <option value="mobile"{if $tel.type eq 'mobile'} selected="selected"{/if}>Mobile</option>
    <option value="fax"{if $tel.type eq 'fax'} selected="selected"{/if}>Fax</option>
  </select>
  <input type="text" size="19" maxlength="28" name="{$telpref}[display]" {if $tel.error}class="error"{/if} value="{$tel.display}" />
  <a id="{$id}_addComment" href="javascript:addPhoneComment('{$id}')" {if $tel.comment neq ''}style="display:none" {/if}>
    {icon name=comments title="Ajouter un commentaire"}
  </a>
  {/if}
  <a class="removeTel" href="javascript:removeTel('{$prefname}','{$prefid}','{$telid}')">
    {icon name=cross title="Supprimer ce numéro de téléphone"}
  </a>
</div>
<div style="float: right" class="flags">
  {if t($mainField)}
  {include file="include/flags.radio.tpl" name="`$telpref`[pub]" val=$tel.pub disabled=$hiddentel
           mainField=$mainField mainId=$mainId subField=$subField subId=$telid}
  {else}
  {include file="include/flags.radio.tpl" name="`$telpref`[pub]" val=$tel.pub disabled=$hiddentel}
  {/if}
</div>
<div id="{$id}_comment" style="clear: both;{if $tel.comment eq ''} display:none{/if}">
  Commentaire :
  <input type="text" size="45" maxlength="80" name="{$telpref}[comment]" {if $tel.error}class="error"{/if} value="{$tel.comment}"/>
  <a class="removePhoneComment" href="javascript:removePhoneComment('{$id}','{$telpref}')">
    {icon name=cross title="Supprimer le commentaire"}
  </a>
</div>
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
