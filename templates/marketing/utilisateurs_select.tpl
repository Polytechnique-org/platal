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
 ***************************************************************************
        $Id: utilisateurs_select.tpl,v 1.4 2004/10/24 14:41:14 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}

<h1>
  Selectionner un X non inscrit
</h1>

<p>
Sélectionne l'X que tu veux inscrire ou &agrave; qui tu veux envoyer le mail de pré-inscription.
</p>

<form action="{$smarty.server.PHP_SELF}" method="get">
  <table class="bicol" cellpadding="3" summary="Sélection de l'X non inscrit">
    <tr>
      <th>
        Sélection de l'X non inscrit
      </th>
    </tr>
    <tr>
      <td>
        <select name="xmat">
          {foreach from=$nonins item=x}
          <option value="{$x.matricule}">{$x.matricule} {$x.prenom} {$x.nom} (X{$x.promo})</option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <td class="center">
        {foreach from=$id_actions item=id_action}
        <input type="submit" name="submit" value="{$id_action}" />&nbsp;&nbsp;
        {/foreach}
      </td>
    </tr>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
