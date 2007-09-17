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

{if $with_text_value}
<div>
{/if}
<select name="{$name}"{if $onchange} onchange="{$onchange}"{/if}{if $id} id="{$id}"{/if}>
    <option value=""> - </option>
  {iterate from=$list item='option'}
    <option value="{$option.id}">{$option.field|htmlspecialchars}</option>
  {/iterate}
</select>
{if $with_text_value}
<input type="hidden" value="" name="{$name}Txt" />
</div>
{/if}

{* vim:set et sws=2 sts=2 sw=2 enc=utf-8: *}
