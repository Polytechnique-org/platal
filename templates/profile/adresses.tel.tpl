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

{if $ajaxtel}
<?xml version="1.0" encoding="utf-8"?>
{/if}
{assign var=telpref value="`$adpref`[tel][`$t`]"}
{assign var=telid value="`$adid`_tel_`$t`"}
<div style="float: right" class="flags">
  {include file="include/flags.radio.tpl" name="`$telpref`[pub]" val=$tel.pub display="div"}
</div>
<span class="titre">N°{$t+1}</span>
<input type="hidden" name="{$telpref}[removed]" value="0" />
<input type="text" size="10" maxlength="30" name="{$telpref}[type]" value="{$tel.type|default:"Tél."}" />
<input type="text" size="19" maxlength="28" name="{$telpref}[tel]" {if $tel.error}class="error"{/if} value="{$tel.tel}" />
<a href="javascript:removeObject('{$telid}', '{$telpref}')">
  {icon name=cross title="Supprimer ce numéro de téléphone"}
</a>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
