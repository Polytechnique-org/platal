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
        $Id: recovery.tpl,v 1.6 2004-10-24 14:41:11 x2000habouzit Exp $
 ***************************************************************************}


<h1>
  Perte du mot de passe
</h1>

{dynamic}

<div class="erreur">{$error}</div>

{if $ok}

<p>
<strong>Un certificat d'authentification aléatoire</strong> vient de t'être attribué et envoyé à
ton adresse en polytechnique.org.<span class="erreur"> Il expire dans six heures.</span> Tu dois donc
<strong>consulter ton mail avant son expiration</strong> et utiliser le certificat comme expliqué
dans le mail pour changer ton mot de passe.
</p>
<p>
Si tu n'accèdes pas à ton mail dans les
6 heures, sollicite un nouveau
certificat sur cette page.
</p>

{else}

<form action="{$smarty.server.PHP_SELF}" method="post">
  <p>
  Il est impossible de récupérer le mot de passe perdu (nous n'avons que le résultat après un
  chiffrement irréversible de ton mot de passe). La procédure suivante va te permettre de choisir un
  nouveau mot de passe.
  </p>
  <p>
  Après avoir complété les informations suivantes, tu recevras à ton adresse Polytechnique.org un
  courrier électronique te permettant de choisir ce nouveau mot de passe. Si jamais tu n'as plus
  accès aux boîtes aux lettres vers lesquelles ton adresse Polytechnique.org reroute ton courrier,
  alors indique nous ci-dessous l'adresse à laquelle tu souhaites recevoir le courrier. Nous t'y
  adresserons le message de création d'un nouveau mot de passe si et seulement si tes anciennes
  boîtes sont réellement inaccessibles.
  </p>
  <table class="tinybicol" cellpadding="3" cellspacing="0" summary="Récupération du mot de passe">
    <tr>
      <th colspan="2">
        Perte de mot de passe
      </th>
    </tr>
    <tr>
      <td class="titre">
        Login :<br />
        <span class="smaller">"prenom.nom"</span>
      </td>
      <td>
        <input type="text" size="20" maxlength="50" name="login" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Adresse électronique : <span class="smaller">(facultatif)</span>
      </td>
      <td>
        <input type="text" size="20" maxlength="50" name="email" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Date de naissance :
      </td>
      <td>
        <input type="text" size="8" maxlength="8" name="birth" />
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span class="smaller">
          (format JJMMAAAA soit 01032000 pour 1er mars 2000)
        </span>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Continuer" name="submit" />
      </td>
    </tr>
  </table>
</form>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
