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
        $Id: form.valid.epouses.tpl,v 1.11 2004/11/13 15:56:37 x2000habouzit Exp $
 ***************************************************************************}


<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol" cellpadding="4" summary="Demande d'alias d'épouse">
    <tr>
      <td>Demandeur&nbsp;:</td>
      <td>
        <a href="{"fiche.php"|url}?user={$valid->forlife}" class="popup2">
          {$valid->prenom} {$valid->nom}
        </a>
        {if $valid->oldepouse}({$valid->oldepouse} - {$valid->oldalias}){/if}
      </td>
    </tr>
    <tr>
      <td>&Eacute;pouse&nbsp;:</td>
      <td>{$valid->epouse}</td>
    </tr>
    <tr>
      <td>Nouvel&nbsp;alias&nbsp;:</td>
      <td>{$valid->alias|default:"<span class='erreur'>suppression</span>"}</td>
    </tr>
    {if $valid->homonyme}
    <tr>
      <td colspan="2">
        <span class="erreur">Probleme d'homonymie !
          <a href="{"fiche.php"|url}?user=$valid->homonyme" class="popup2">{$valid->homonyme}</a>
        </span>
      </td>
    </tr>
    {/if}
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
