{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<!--
{assign var=telpref value="`$prefname`[`$telid`]"}
{assign var=id value="`$prefid`_`$telid`"}
<div class="titre" style="float: left; width: 2.5em">N°{$telid+1}</div>
<div style="float: left;">
  <select name="{$telpref}[type]">
    <option value="fixed"{if $tel.type eq 'fixed'} selected="selected"{/if}>Fixe</option>
    <option value="mobile"{if $tel.type eq 'mobile'} selected="selected"{/if}>Mobile</option>
    <option value="fax"{if $tel.type eq 'fax'} selected="selected"{/if}>Fax</option>
  </select>
  <input type="hidden" name="{$telpref}[removed]" value="0"/>
  <input type="text" size="19" maxlength="28" name="{$telpref}[tel]" {if $tel.error}class="error"{/if} value="{$tel.tel}" />
  <a href="javascript:removeTel('{$id}')">
    {icon name=cross title="Supprimer ce numéro de téléphone"}
  </a>
  <a id="{$id}_addComment" href="javascript:addPhoneComment('{$id}', '{$telpref}')" {if $tel.comment neq ''}style="display:none" {/if}>
    {icon name=comments title="Ajouter un commentaire"}
  </a>
</div>
<div style="float: right" class="flags">
  {include file="include/flags.radio.tpl" name="`$telpref`[pub]" val=$tel.pub}
</div>
<div id="{$id}_comment" style="clear: both;{if $tel.comment eq ''} display:none{/if}">
  Commentaire :
  <input type="text" size="45" maxlength="80" name="{$telpref}[comment]" {if $tel.error}class="error"{/if} value="{$tel.comment}"/>
  <a href="javascript:removePhoneComment('{$id}', '{$telpref}')">
    {icon name=cross title="Supprimer le commentaire"}
  </a>
</div>
{assign var=edu value="edu_`$eduid`"}
-->
{assign var=edu value="$edus"}
{assign var=eduname value="edu_`$eduid`"}
{$edu} {$edu.id} {$edu.type} {$eduname}[id]
<div>
  <select name="{$eduname}[id]" onchange="fillType(this.form['{$eduname}[type]'], this.selectedIndex-1);">
    {applis_options selected=$edu.id}
  </select>
  <br />
  <input type="hidden" name="{$eduname}_tmp" value="{$edu.type}" />
  <select name="{$eduname}[type]">
    <option value=""></option>
  </select>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
