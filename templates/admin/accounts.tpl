{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<h1>Comptes désactivés</h1>

<table class="bicol">
  <tr><th>Nom</th><th>Commentaire</th></tr>
  {iterate from=$disabled item=user}
  <tr class="{cycle values="pair,impair"}">
    <td>
      <a href="admin/user/{$user.hruid}">{$user.prenom} {$user.nom} ({$user.promo})</a>
    </td>
    <td>
      {$user.comment|default='(none)'}
    </td>
  </tr>
  {/iterate}
</table>

<h1>Administrateurs du site</h1>

<table class="tinybicol">
  <tr><th>Utilisateur</th></tr>
  {iterate from=$admins item=user}
  <tr class="{cycle values="pair,impair"}">
    <td>
      <a href="admin/user/{$user.hruid}">{$user.prenom} {$user.nom} ({$user.promo})</a>
    </td>
  </tr>
  {/iterate}
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
