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

{if $canedit || $has_perms}
<table class='wiki' cellspacing='0' cellpadding='0'>
  <tr>
    <td>
      <a href='{$wikipage}'>Voir la page</a>
    </td>
    {if $canedit}
    <td>
      <a href='{$wikipage}?action=edit'>Éditer la page</a>
    </td>
    {/if}
  {if $has_perms}
    <td>
      <a href='{$wikipage}?action=diff'>Historique</a>
    </td>
    <td>
      <a href='{$wikipage}?action=upload'>Upload</a>
    </td>
  </tr>
  <tr>
    <td>
      <select onchange="dynpostkv('{$wikipage}', 'setrperms', this.value)">
      {html_options options=$perms_opts selected=$perms[0]}
      </select>
    </td>
    <td>
      <select onchange="dynpostkv('{$wikipage}', 'setwperms', this.value)">
      {html_options options=$perms_opts selected=$perms[1]}
      </select>
    </td>
    <td colspan='2' style='text-align: left'>&lt;-- Droits associés</td>
  {/if}
  </tr>
</table>
{/if}

{if $text}
{$pmwiki|smarty:nodefaults}
{else}
{include file=$pmwiki_cache}
{/if}
