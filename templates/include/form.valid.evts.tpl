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
        $Id: form.valid.evts.tpl,v 1.9 2004/11/17 12:21:48 x2000habouzit Exp $
 ***************************************************************************}


<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol">
    <tr>
      <th class="titre" colspan="2">Événement</th>
    </tr>
    <tr>
      <td  colspan="2">
        Posté par <a href="{"fiche.php"|url}?user={$valid->bestalias}" class="popup2">
          {$valid->prenom} {$valid->nom} (X{$valid->promo})
        </a>
        [<a href="mailto:{$valid->bestalias}@polytechnique.org">lui écrire</a>]
      </td>
    </tr>
    <tr>
      <td class="titre">Titre</td>
      <td>{$valid->titre}</td>
    </tr>
    <tr>
      <td class="titre">Texte</td>
      <td>{$valid->texte}</td>
    </tr>
    <tr>
      <td class="titre">Péremption</td>
      <td>{$valid->peremption}</td>
    </tr>
    <tr>
      <td class="titre">Promos</td>
      <td>{$valid->pmin} - {$valid->pmax}</td>
    </tr>
    <tr>
      <td class="titre">Commentaire</td>
      <td>{$valid->comment}</td>
    </tr>
    <tr>
      <td class="center" colspan="2">
        <input type="hidden" name="uid" value="{$valid->uid}" />
        <input type="hidden" name="type" value="{$valid->type}" />
        <input type="hidden" name="stamp" value="{$valid->stamp}" />
        <input type="submit" name="action" value="Valider" />
        <input type="submit" name="action" value="Invalider" />
        <input type="submit" name="action" value="Supprimer" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
