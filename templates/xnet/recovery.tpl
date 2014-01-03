{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}


<h1>Perte du mot de passe</h1>


{if $ok}

<p>
<strong>Un certificat d'authentification</strong> vient de vous être attribué et envoyé.
Ce certificat permet d'accéder à un formulaire de changement de mot de passe.
<span class="erreur"> Il expire dans six heures.</span> Vous devez donc <strong>consulter vos emails avant son
expiration</strong> et utiliser le certificat comme expliqué dans l'email pour changer votre mot de passe.
</p>
<p>
Si vous n'accéder pas à cet email dans les 6 heures, sollicitez un nouveau certificat sur cette page.
</p>

{else}

<form action="{$platal->ns}recovery/ext" method="post">
  <p>
  Il est impossible de récupérer le mot de passe perdu car nous n'avons que le résultat après un
  chiffrement irréversible de votre mot de passe. La procédure suivante va vous permettre de choisir un
  nouveau mot de passe.
  </p>
  <p>
  Après avoir complété les informations suivantes, vous recevrez un
  email vous permettant de choisir un nouveau mot de passe.  </p>
  <p>
  Si vous ne recevez pas cet email, n'hésitez pas à contacter
  <a href="mailto:support@{#globals.mail.domain#}">le support technique</a>.
  </p>
  <table class="tinybicol" cellpadding="3" cellspacing="0" summary="Récupération du mot de passe">
    <tr>
      <th colspan="2">
        Perte de mot de passe
      </th>
    </tr>
    <tr>
      <td class="titre">
        Identifiant (adresse email)&nbsp;:
      </td>
      <td>
        <input type="text" size="20" maxlength="255" name="login" />
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

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
