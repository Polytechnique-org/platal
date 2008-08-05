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

<h1>Refuser l'inscription d'un utilisateur</h1>

<form method='post' action='{$platal->pl_self(1)}'>
  {xsrf_token_field}
  <table class='tinybicol' cellpadding='0' cellspacing='0'>
    <tr>
      <th class='titre'>refuser l'inscription de&nbsp;:</th>
    </tr>
    <tr>
      <td>{$del_user.name}</td>
    </tr>
    <tr>
      <td>raison&nbsp;:
        <textarea cols='50' rows='10' name='reason'></textarea>
      </td>
    </tr>
    <tr>
      <td class='center'>
        <input type='hidden' name='sdel' value='{$del_user.id}' />
        <input type='submit' value='Refuser !' />
      </td>
    </tr>
  </table>
</form>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
