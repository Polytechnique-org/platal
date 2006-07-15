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

<h1>Abonnés à des listes non présents dans l'annuaire</h1>

<h2>Polytechniciens</h2>

<ul>
  {foreach from=$not_in_group_x item=n}
  <li>{$n} [<a href='{rel}/{$platal->ns}member/new/x/{$n}' class='popup'>l'inscrire</a>]</li>
  {foreachelse}
  <li><em>tous les polytechniciens présents sur les listes sont inscrits à l'annuaire du groupe.</em></li>
  {/foreach}
</ul>

<h2>non Polytechniciens</h2>
<ul>
  {foreach from=$not_in_group_ext item=n}
  <li>{$n} [<a href='{rel}/{$platal->ns}member/new/ext/{$n}' class='popup'>l'inscrire</a>]</li>
  {foreachelse}
  <li><em>tous les non-polytechniciens présents sur les listes sont inscrits à l'annuaire du groupe.</em></li>
  {/foreach}
</ul>

<h2>Comparer une liste et l'annuaire</h2>

<table cellspacing="2" cellpadding="0" class="tiny">
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th></th>
  </tr>
  {foreach from=$lists item=l}
  <tr>
    <td>{$l.list}</td>
    <td>{$l.desc}</td>
    <td><a href="{rel}/{$platal->ns}lists/sync/{$l.list}">synchro</a></td>
  </tr>
  {/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
