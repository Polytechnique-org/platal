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
        $Id: utilisateurs_form.tpl,v 1.7 2004/10/24 14:41:14 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}

<h1>
  Envoyer un mail de pr&eacute;-inscription
</h1>

<p>
Le nom, pr&eacute;nom et promotion sont pris dans la table d'identification.  Le login sera automatiquement
calcul&eacute; &agrave; partir de ces données.
</p>

<form action="{$smarty.server.PHP_SELF}" method="get">
  <table cellpadding="3" class="bicol" summary="Envoyer un mail">
    <tr>
      <th colspan="2">
        Envoyer un mail
      </th>
    </tr>
    <tr>
      <td class="titre">
        Prénom :
      </td>
      <td>
        {$row.prenom}
      </td>
    </tr>
    <tr>
      <td class="titre">
        Nom :
      </td>
      <td>
        {$row.nom}
      </td>
    </tr>
    <tr>
      <td class="titre">
        Promo :
      </td>
      <td>
        {$row.promo}
      </td>
    </tr>
    <tr>
      <td class="titre">
        From du mail :
      </td>
      <td>
        <input type="text" size="40" maxlength="60" name="from"
        value="{$smarty.request.from|default:"`$smarty.session.forlife`@polytechnique.org"}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Adresse e-mail devinée :
      </td>
      <td>
        <input type="text" size="40" maxlength="60" name="mail"
        value="{$smarty.request.mail}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="hidden" name="xmat" value="{$smarty.request.xmat}" />
        <input type="hidden" name="sender" value="{$smarty.request.sender|default:$smarty.session.uid}" />
        <input type="submit" name="submit" value="Envoyer le mail" />
      </td>
    </tr>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
