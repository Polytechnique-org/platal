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

<div>
<h1 style="display: block; float: right">
  {if !$plset_count}
  Aucune entrée
  {elseif $plset_count eq 1}
  1 entrée
  {else}
  {$plset_count} entrées
  {/if}
</h1>
<h1>
  {$plset_mods[$plset_mod]}
  {if $plset_mods|@count > 1}[
  {foreach from=$plset_mods key=mod item=desc name=mods}
    {if $mod neq $plset_mod}
    <a href="{$plset_base}/{$mod}{$plset_search}">{$desc}</a> {if !$smarty.foreach.mods.last}| {/if}
    {/if}
  {/foreach}
  ]
  {/if}
</h1>
</div>

<div id="plset_content" style="clear: both">
{include file=$plset_content}
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
