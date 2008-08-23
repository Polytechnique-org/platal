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

<h1>Droits d'administration des lettres de l'AX</h1>

<form action="admin/axletter" method="post">
  {xsrf_token_field}
  <table class="tinybicol">
    <tr>
      <th>Nom</th>
      <th>Action</th>
    </tr>
    <tr class="pair">
      <td colspan="2" class="center">
        <input type="text" name="uid" value="" />
        <input type="submit" name="action" value="add" />
      </td>
    </tr>
    {iterate item=a from=$admins}
    <tr class="{cycle values="impair, pair"}">
      <td><a href="profile/{$a.forlife}" class="popup2">{$a.prenom} {$a.nom} (X{$a.promo}){icon name=user_suit}</a></td>
      <td class="right"><a href="admin/axletter/del/{$a.forlife}?token={xsrf_token}">{icon name=cross title="Retirer"}</a></td>
    </tr>
    {/iterate}
  </table>
</form>

<h1>Ajout d'utilisateurs</h1>

{include core=csv-importer.tpl}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
