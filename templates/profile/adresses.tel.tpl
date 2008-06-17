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

{assign var=telpref value="`$adpref`[tel][`$t`]"}
{assign var=telid value="`$adid`_tel_`$t`"}
<div style="float: right" class="flags">
  {include file="include/flags.radio.tpl" name="`$telpref`[pub]" val=$tel.pub}
</div>
<span class="titre">N°{$t+1}</span>
<input type="hidden" name="{$telpref}[removed]" value="0" />
<select name="{$telpref}[type]">
  <option value="fixed"{if $tel.type eq 'fixed'} selected="selected"{/if}>Fixe</option>
  <option value="mobile"{if $tel.type eq 'mobile'} selected="selected"{/if}>Mobile</option>
  <option value="fax"{if $tel.type eq 'fax'} selected="selected"{/if}>Fax</option>
</select>
<input type="text" size="19" maxlength="28" name="{$telpref}[tel]" {if $tel.error}class="error"{/if} value="{$tel.tel}" />
<a href="javascript:removeObject('{$telid}', '{$telpref}')">
  {icon name=cross title="Supprimer ce numéro de téléphone"}
</a>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
