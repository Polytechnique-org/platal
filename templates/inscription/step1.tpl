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

{dynamic}
<p class='erreur'>{$erreur|smarty:nodefaults|nl2br}</p>
 
<form action="{"inscription/step2.php"|url}" method="post" id='idf'>
  <h1>
    Identification
  </h1>
  <p>
    Renseigne tes nom, prénom et promotion, et si tu es d'une promotion
    postérieure à la 1996, ton numéro de matricule.
  </p>
  <table class="bicol" summary="Identification" cellpadding="3">
    <tr>
      <th>
        Promo &lt; 1996
      </th>
      <th>
        Promo depuis 1996
      </th>
    </tr>
    <tr>
      <td>
        Le numéro d'identification n'est pas nécessaire pour 
        l'inscription pour les promotions jusqu'à 1995 incluse.
      </td>
      <td>
        <strong>Matricule X :</strong>&nbsp;&nbsp;
        <input type="text" size="6" maxlength="6" name="matricule" value="{$smarty.request.matricule}" />
        <br />
        6 chiffres terminant par le numéro d'entrée<br />
        (ex: 960532 ou 100532)<br />
        Voir sur le GU ou un bulletin de solde pour trouver cette information<br /><br />
        Pour les élèves étrangers voie 2, il est du type :<br />
        Promotion: 1995, Matricule: 960XXX - Promotion: 2001, Matricule 102XXX.
      </td>
    </tr>
    <tr>
      <th colspan="2">
        Identification
      </th>
    </tr>
    <tr>
      <td class="titre"> 
        Nom<br />
        <span class="smaller">(Xettes mariées : nom à l'X)</span>
      </td>
      <td>
        <input type="text" size="20" maxlength="30" name="nom" value="{$smarty.request.nom}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Prénom
      </td>
      <td>
        <input type="text" size="15" maxlength="20" name="prenom" value="{$smarty.request.prenom}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Promotion
      </td>
      <td>
        <input type="text" size="4" maxlength="4" name="promo" value="{$smarty.request.promo}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Continuer l'inscription" />
      </td>
    </tr>
  </table>
</form>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
