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


<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol" cellpadding="4" summary="Demande d'alias">
    <tr>
      <td>Demandeur&nbsp;:
      </td>
      <td>
        <a href="{"fiche.php"|url}?user={$valid->bestalias}" class='popup2'>
          {$valid->prenom} {$valid->nom}</a> {$valid->old}
      </td>
    </tr>
    <tr>
      <td>Nouvel&nbsp;alias&nbsp;:</td>
      <td>{$valid->alias}@{#globals.mail.alias_dom}</td>
    </tr>
    <tr>
      <td>Motif :</td>
      <td style="border: 1px dotted inherit">
        {$valid->raison|nl2br}
      </td>
    </tr>
    <tr>
      <td class="middle">
        <input type="hidden" name="uid" value="{$valid->uid}" />
        <input type="hidden" name="type" value="{$valid->type}" />
        <input type="hidden" name="stamp" value="{$valid->stamp}" />
        <input type="submit" name="submit" value="Accepter" />
        <br /><br />
        <input type="submit" name="submit" value="Refuser" />
      </td>
      <td>
        <p>Raison du refus:</p>
        <textarea rows="5" cols="50" name="motif"></textarea>
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
