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

{if $ajaxskill}
<?xml version="1.0" encoding="utf-8"?>
{/if}
<div id="{$cat}_{$id}" style="clear: both; margin-top: 0.5em">
  <div style="float: left; width: 50%" class="titre" id="{$cat}_{$id}_title">
    {$skill.text}
    <input type="hidden" name="{"`$cat`[`$id`][text]"}" value="{$skill.text}" />
  </div>
  <select name="{"`$cat`[`$id`][level]"}">
    {foreach from=$levels item=level key=lid}
    <option value="{$lid}" {if $skill.level eq $lid}selected="selected"{/if}>{$level}</option>
    {/foreach}
  </select>
  <a href="javascript:remove('{$cat}', '{$id}')">{icon name=cross title="Supprimer"}</a>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
