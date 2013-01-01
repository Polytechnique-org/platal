{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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


<tr class="impair">
  <td class="titre">Ancien alias&nbsp;:</td>
  <td>{$valid->old_alias}</td>
</tr>
<tr class="impair">
  <td class="titre">Nouvel alias&nbsp;:</td>
  <td>{$valid->new_alias}</td>
</tr>
{foreach from=$valid->public_names item=name key=type}
<tr class="impair">
  <td class="titre">*{$valid->descriptions.$type}&nbsp;:</td>
  <td>{$name}</td>
</tr>
{/foreach}
{foreach from=$valid->old_public_names item=name key=type}
<tr class="impair">
  <td class="titre">&#8224;{$valid->descriptions.$type}&nbsp;:</td>
  <td>{$name}</td>
</tr>
{/foreach}
<tr class="impair">
  <td class="center" colspan="2">
    <span class="smaller">* nouveau nom ; &#8224; ancien nom</span>
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
