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
        $Id: utilisateurs_recherche.tpl,v 1.4 2004-08-31 11:25:41 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}
<div class="rubrique">
  Chercher un X non inscrit
</div>

{if $err}
<p class="erreur">{$err}</p>
{/if}

<p>
Bien remplir tous les champs pour passer à la page suivante.
</p>

<p>
Si un champ est <strong>inconnu ou incertain</strong>, le remplir quand m&ecirc;me avec
<strong>le caract&egrave;re % (pourcent). La promo peut rester vide.</strong>
</p>

<form action="{$smarty.server.PHP_SELF}" method="get">
  <table class="bicol" cellpadding="3" summary="Recherche marketing">
    <tr>
      <th colspan="2">
        Recherche marketing
      </th>
    </tr>
    <tr>
      <td class="titre">
        Prénom :
      </td>
      <td>
        <input type="text" size="40" maxlength="60" value="{$smarty.request.prenomR}" name="prenomR" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Nom :
      </td>
      <td>
        <input type="text" size="40" maxlength="60" value="{$smarty.request.nomR}" name="nomR" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Promo :
      </td>
      <td>
        <input type="text" size="4" maxlength="4" value="{$smarty.request.promoR}" name="promoR" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Chercher" />
      </td>
    </tr>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
