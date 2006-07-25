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


<h1>
  Lettre de Polytechnique.org
</h1>

<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
  <tr>
    <th>date</th>
    <th>titre</th>
  </tr>
  <tr>
    <td colspan='2'><a href='admin/newsletter/new'>Créer une nouvelle lettre</a></td>
  </tr>
  {foreach item=nl from=$nl_list}
  <tr class="{cycle values="pair,impair"}">
    <td>{$nl.date|date_format}</td>
    <td>
      <a href="admin/newsletter/edit/{$nl.id}">{$nl.titre|default:"[no title]"}</a>
    </td>
  </tr>
  {/foreach}
</table>


{* vim:set et sw=2 sts=2 sws=2: *}
