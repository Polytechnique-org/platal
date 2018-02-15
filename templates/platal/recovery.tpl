{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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
<strong>Un certificat d'authentification</strong> vient de t'être attribué et a été envoyé vers les redirections de
ton adresse {#globals.mail.domain#}. Ce certificat te permet d'accéder à un formulaire de changement de mot de passe.
<span class="erreur"> Il expire dans six heures.</span> Tu dois donc <strong>consulter tes emails avant son
expiration</strong> et utiliser le certificat comme expliqué dans l'email pour changer ton mot de passe.
</p>
<p>
Si tu n'accèdes pas à cet email dans les 6 heures, sollicite un nouveau certificat sur cette page.
</p>

{elseif $no_addr}

<p class="erreur">
  {icon name=error} Les informations n'ont pas pu être envoyées car ton adresse {#globals.core.sitename#} n'a plus
  de redirection fonctionnelle.
</p>

<p>
  <a href="mailto:register@{#globals.mail.domain#}">Contacte le support</a> pour que nous puissions régler ton problème
  au plus vite.
</p>

{else}

<form action="{$platal->ns}recovery" method="post">
  <p>
  Il est impossible de récupérer le mot de passe perdu car nous n'avons que le résultat après un
  chiffrement irréversible de ton mot de passe. La procédure suivante va te permettre de choisir un
  nouveau mot de passe.
  </p>
  <p>
  Après avoir complété les informations suivantes, tu recevras à ton adresse {#globals.core.sitename#} un
  email te permettant de choisir un nouveau mot de passe. Si tu désires que cet email soit
  envoyé vers une de tes redirections en particulier, tu peux renseigner l'adresse de cette redirection dans
  le champ facultatif (cette adresse doit être une de tes redirections actuelles&nbsp;!).
  </p>
  <p>
  Si tu ne reçois pas cet email, n'hésite pas à contacter
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
        Login&nbsp;:<br />
        <span class="smaller">"prenom.nom" ou "prenom.nom.promo"</span>
      </td>
      <td>
        <input type="text" size="20" maxlength="255" name="login" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Date de naissance&nbsp;:<br />
        <span class="smaller">format JJMMAAAA soit 01032000<br />pour 1<sup>er</sup> mars 2000</span>
      </td>
      <td>
        <input type="text" size="8" maxlength="8" name="birth" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Adresse email&nbsp;: <span class="smaller">(facultatif)</span>
      </td>
      <td>
        <input type="text" size="20" maxlength="255" name="email" />
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

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
