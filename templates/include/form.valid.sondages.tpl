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
        $Id: form.valid.sondages.tpl,v 1.7 2004-08-31 11:25:40 x2000habouzit Exp $
 ***************************************************************************}


<form action="{$smarty.server.PHP_SELF}" method="post">
<table class="bicol" cellpadding="4" summary="Sondage">
<tr>
  <td>Demandeur&nbsp;:
  </td>
  <td><a href="javascript:x()" onclick="popWin('{"fiche.php"|url}?user={$valid->username}')">
      {$valid->prenom} {$valid->nom}</a>
    {if $valid->old}({$valid->old}){/if}
  </td>
</tr>
<tr>
  <td>Titre du sondage&nbsp;:</td>
  <td>{$valid->titre}</td>
</tr>
<tr>
  <td>Prévisualisation du sondage&nbsp;:</td>
  <td><a href="{"sondages/questionnaire.php?SID=$valid->sid"|url}" onclick="return popup(this)">{$valid->titre}</a>
  </td>
</tr>
<tr>
  <td>Alias du sondage&nbsp;:</td>
  <td><input type="text" name="alias" value="{$valid->alias}" />&nbsp;(ne doit pas contenir le caractère ')
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
      <textarea rows="5" cols="74" name=motif></textarea>
  </td>
</tr>
</table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
