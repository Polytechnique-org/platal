{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

{include file='listes/header_listes.tpl' on='sync'}
<h1>Non abonnés à la liste {$smarty.request.liste}@{$asso.mail_domain}</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">

  <table cellspacing="2" cellpadding="0" class="tiny">
    <tr>
      <th>Membre</th>
      <th></th>
    </tr>
    {foreach from=$not_in_list item=u}
    <tr>
      <td>{$u.prenom} {$u.nom}</td>
      <td><input type="checkbox" name="add[{$u.email}]" /></td>
    </tr>
    {/foreach}
    <tr>
      <td colspan='2' class="center">
        <input type='submit' value='forcer inscription' />
      </td>
    </tr>
  </table>

</form>

{* vim:set et sw=2 sts=2 sws=2: *}
