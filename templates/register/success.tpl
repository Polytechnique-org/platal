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

<h1>Bravo !!!</h1>

<p>
Tu as maintenant accès au site !!!
Ton adresse électronique à vie <strong>{$smarty.session.forlife}@polytechnique.org</strong> est déjà ouverte, essaie-la !
</p>
<p>
  Remarque: m4x.org est un domaine "discret" qui veut dire "mail for X" et
  qui comporte exactement les mêmes adresses que le domaine polytechnique.org.
</p>


<h2>Mot de passe</h2>

{if $mdpok}

<p class="erreur">
ton mot de passe a bien été mis à jour !
</p>

{else}

<p>
Tu as recu un mot de passe par défaut, si tu souhaites en changer, tu peux le faire ici :
</p>

<form action="{$smarty.server.PHP_REQUEST}" method="post" id="changepass">
  <table class="tinybicol" cellpadding="3" cellspacing="0">
    <tr>
      <th colspan="2">
        Saisie du nouveau mot de passe
      </th>
    </tr>
    <tr>
      <td class="titre">
        Nouveau mot de passe :
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Retape-le une fois :
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau2" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Changer" name="submitn" onclick="EnCryptedResponse(); return false;" />
      </td>
    </tr>
  </table>
</form>

<form action="{$smarty.server.PHP_REQUEST}" method="post" id="changepass2">
<div><input type="hidden" name="response2"  value="" /></div>
</form>

<p>
N'oublie pas : si tu perds ton mot de passe, nous n'avons aucun engagement, en
particulier en termes de rapidité, mais pas seulement, à te redonner accès au
site. Cela peut prendre plusieurs semaines, les pertes de mot de passe sont
traitées avec la priorité minimale.
</p>

{/if}

<h2>Rejoindre la communauté</h2>

<form action='{rel}/profil.php' method='post'>
  <p>
  Pour rejoindre la communauté des X sur le web, nous te convions le plus vivement à remplir ton profil !!!
  </p>

  <p>
  Cette fonctionnalités n'est pas du tout redondante avec l'annuaire de l'AX, car nous pouvons synchroniser pour toi
  les données qu'il contient :
  </p>

  <ul>
    <li>
    tu peux choisir dans ton profil sur Polytechnique.org d'automatiquement transmettre à l'AX (et <strong>uniquement</strong> ces données)
    des parties de ta fiche, au fur et à mesure que tu les modifies.
    </li>
    <li>
    de même, nous mettons à jour ta fiche depuis les données de l'annuaire de l'AX si tu le souhaites.
    (si tu ne le souhaite pas, décoche la case ci contre : <input type='checkbox' value='1' checked="checked" name='register_from_ax_question' />)
    </li>
  </ul>

  <div class="center">
    <input type="submit" value="Rejoindre les X sur le Net !" class="erreur" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
