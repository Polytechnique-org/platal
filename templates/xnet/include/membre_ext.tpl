{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************}

<h1> Ajout d'un membre ext&eacute;rieur</h1>
<form method="post" action="{$smarty.server.PHP_SELF}">
  <table>
    <tr>
      <td>Nom :</td>
      <td><input type="text" name="nom" value="{$smarty.request.nom}" size="40" maxlength="255" /></td>
    </tr>
    <tr>
      <td>Pr&eacute;nom :</td>
      <td><input type="text" name="prenom" value="{$smarty.request.prenom}" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td>Email :</td>
      <td><input type="text" name="email" value="{$smarty.request.email}" size="40" maxlength="60" /></td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <input type="submit" name="addext" value="Ajouter" />
        <input type="hidden" name="id" value="{$smarty.request.id}" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
