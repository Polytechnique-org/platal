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
        $Id: form.valid.emploi.tpl,v 1.5 2004-08-31 11:25:40 x2000habouzit Exp $
 ***************************************************************************}


<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol" cellpadding="4" summary="Annonce emploi">
    <thead>
      <tr>
        <th colspan="2">Offre d'emploi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Demandeur</td>
        <td>{$valid->entreprise} ({$valid->mail})</td>
      </tr>
      <tr>
        <td>Titre du post</td>
        <td>{$valid->titre}</td>
      </tr>
      <tr>
        <td colspan="2"><pre>{$valid->text}</pre></td>
      </tr>
      <tr>
        <td class="center" colspan="2">
          <input type="hidden" name="uid" value="{$valid->uid}" />
          <input type="hidden" name="type" value="{$valid->type}" />
          <input type="hidden" name="stamp" value="{$valid->stamp}" />
          <input type="submit" name="submit" value="Accepter" />
          <input type="submit" name="submit" value="Refuser" />
        </td>
      </tr>
    </tbody>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
