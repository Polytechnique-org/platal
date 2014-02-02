{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

{include file="register/breadcrumb.tpl"}

<h1>Identification</h1>

<form action="register" method="post">
  <p>
    Avant toute chose, il te faut nous donner ta promotion&nbsp;:
  </p>
  <table class="tinybicol">
    <tr>
      <th colspan="2">
        Promotion
      </th>
    </tr>
    <tr>
      <td>
        Donne ta promotion sur 4 chiffres&nbsp;:
      </td>
      <td>
        <input type="text" size="4" maxlength="4" name="yearpromo" value="{$smarty.post.yearpromo}" />
      </td>
    </tr>
    <tr>
      <td>
        Formation suivie&nbsp;:
      </td>
      <td>
        <select name="edu_type">
          <option value="{#Profile::DEGREE_X#}" selected="selected">polytechnicienne</option>
          <option value="{#Profile::DEGREE_M#}">master</option>
          <option value="{#Profile::DEGREE_D#}">doctorat</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="center" colspan="2">
        <input type="submit" value="Valider" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
