{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

<table class="bicol" cellpadding="3" cellspacing="0" summary="Liste des Newsletter actives">
  <tr>
    <th>Id</th>
    <th>Groupe</th>
    <th>Titre</th>
    <th>CSS spécifique</th>
    <th>Critères actifs</th>
  </tr>
  {foreach from=$nls item=nl}
  <tr class="{cycle values="pair,impair"}">
    <td class="titre">{$nl->id}</td>
    <td>{$nl->group}</td>
    <td><a href=http://www.polytechnique.net/{$nl->group}/admin/nl>{$nl->name}</a></td>
    <td>{if $nl->hasCustomCss()}Oui{else}Non{/if}</td>
    <td>{$nl->criteria->flags()}</td>
  </tr>
  {/foreach}
</table>
