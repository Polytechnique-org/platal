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

<h1>Raccourcisseur d'url</h1>

<form action="admin/url" method="post">
  {xsrf_token_field}
  <table class="bicol">
    <tr>
      <th>Url&nbsp;:</th>
      <td><input type="text" name="url" value="{if t($url)}{$url}{/if}" /></td>
    </tr>
    <tr>
      <th>Alias (6 caractères, optionnel)&nbsp;:</th>
      <td>
        <input type="text" name="alias" size="6" maxlength="6" value="{if t($alias)}{$alias}{/if}" />
        <small>(peut contenir lettres, chiffres et tirets)</small>
      </td>
    </tr>
  </table>
  <p class="center"><input type="submit" value="Raccourcir" /></p>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
