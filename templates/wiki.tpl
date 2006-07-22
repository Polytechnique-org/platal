{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<table class='wiki' cellspacing='0' cellpadding='0'>
  <tr>
    <td>
      <a href='{$wikipage}'>Voir la page</a>
      {if $has_perms}
      <select>
      {html_options options=$perms_opts selected=$perms[0]}
      </select>
      {/if}
    </td>
    {if $canedit}
    <td>
      <a href='{$wikipage}?action=edit'>Éditer</a>
      {if $has_perms}
      <select>
      {html_options options=$perms_opts selected=$perms[1]}
      </select>
      {else}
      {$perms[0]}
      {/if}
    </td>
    {/if}
    {if $has_perms}
    <td>
      <a href='{$wikipage}?action=diff'>Historique</a>
    </td>
    <td>
      <a href='{$wikipage}?action=upload'>Upload</a>
    </td>
    {/if}
  </tr>
</table>

{$pmwiki|smarty:nodefaults}
