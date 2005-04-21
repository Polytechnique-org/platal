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

<h1> Ajout d'un membre polytechnicien</h1>
<form method="post" action="{$smarty.server.PHP_SELF}">
  <p class="descr">
  Identifiant X.org <small>(prenom.nom)</small> :
  <input type="text" name="username" value="{$smarty.request.username}" size="40" maxlength="100" />
  </p>
  <p class="descr">
  Permissions sur ce groupe :
  </p>
  <select name="is_admin">
    <option value="0">Membre</option>
    <option value="1">Administrateur</option>
  </select>
  <div class="center">
    <input type="submit" name="addx" value="Ajouter" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
