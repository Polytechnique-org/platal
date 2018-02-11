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

<tr>
  <td>{$description}</td>
  <td>
    <span id="{$name}_list"></span>
    <input name="{$name}_text" type="text" class="autocomplete" size="32" value="{$value_text}" />
    <input name="{$name}" type="hidden" class="autocomplete_target" value="{$value}" />
    <a href="{$name}" class="autocomplete_to_select" title="display" id="{$name}_table">
      <img src="images/icons/table.gif" alt="{$title}" title="{$title}" />
    </a>
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
