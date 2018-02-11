{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<h1>Préférences pour tes envois d'email depuis Polytechnique.org</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">
  {xsrf_token_field}
  <table class="bicol" summary="email pref">
    <tr>
      <td class="titre">Ton adresse d'émission</td>
      <td>
        <input type="text" name="from_email" size="60" value="{$from_email}" {if $error}class="error"{/if} />
      </td>
    </tr>
    <tr>
      <td class="titre">Formattage des emails</td>
      <td>
        <label>HTML<input type="radio" name="from_format" value="html" {if $from_format eq "html"}checked="checked"{/if} /></label>
        <label><input type="radio" name="from_format" value="text" {if $from_format neq "html"}checked="checked"{/if} />texte brut</label>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Valider" name="submit" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
