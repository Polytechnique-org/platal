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
        $Id: utilisateurs_inscrire.tpl,v 1.6 2004/10/24 14:41:14 x2000habouzit Exp $
 ***************************************************************************}


<h1>
  Inscrire manuellement un X
</h1>
{dynamic}
{if $success eq "1"}
<p>
Paramètres à transmettre:<br />
Login=<strong>{$mailorg}</strong><br />
Password=<strong>{$pass_clair}</strong>
</p>
<p>
Pour éditer le profil,
<a href="../admin/utilisateurs.php?login={$mailorg}">clique sur ce lien.</a>
</p>
{else}
<p>
Les prénom, nom, promo sont pré-remplis suivant la table d'identification.
Modifie-les comme tu le souhaites. Une autre solution consiste à éditer
d'abord la table d'identification (écran précédent) avant d'inscrire cet X.
</p>

<form action="{$smarty.server.PHP_SELF}" method="get">
  <table class="bicol" summary="Créer un login">
    <tr>
      <th colspan="2">
        Créer un login
      </th>
    </tr>
    <tr>
      <td class="titre">Prénom d'inscription</td>
      <td>
        <input type="text" size="40" maxlength="60" value="{$row.prenom}" name="prenomN" />
      </td>
    </tr>
    <tr>
      <td class="titre">Nom d'inscription</td>
      <td>
        <input type="text" size="40" maxlength="60" value="{$row.nom}" name="nomN" />
      </td>
    </tr>
    <tr>
      <td class="titre">Promotion</td>
      <td>
        <input type="text" size="4" maxlength="4" value="{$row.promo}" name="promoN" />
      </td>
    </tr>
    <tr>
      <td class="titre">Login</td>
      <td>
        <input type="text" size="40" maxlength="60" value="{$mailorg}" name="mailorg" />
      </td>
    </tr>
    <tr>
      <td class="titre">Date de naissance</td>
      <td>
        <input type="text" size="8" maxlength="8" value="" name="naissanceN" />
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <input type="hidden" name="xmat" value="{$smarty.request.xmat}" />
        <input type="submit" name="submit" value="Creer le login" />
      </td>
    </tr>
  </table>
</form>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
