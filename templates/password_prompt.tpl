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
        $Id: password_prompt.tpl,v 1.21 2004-08-31 20:41:08 x2000bedo Exp $
 ***************************************************************************}

<noscript>
  <p class="erreur">
    Ton navigateur n'accepte pas le javaScript !!
  </p>
  <p>
    Cette forme de script web est nécessaire pour l'utilisation du site.
    Pour en savoir plus, regarde la <a href="faq.php#connect">FAQ</a>.
  </p>
</noscript>

<div class="rubrique">
  Accès réservé aux Polytechniciens
</div>
{min_auth level="cookie"}
<p>
<strong>Merci de rentrer ton mot de passe pour démarrer une connexion au site.</strong>
Si tu n'es pas {insert name="getName" script="insert.password.inc.php"}, change le login ci-dessous, ou rends-toi sur
<a href="{"inscrire.php"|url}">la page d'inscription</a>.
</p>
{/min_auth}

{only_public}
<p>
<strong>Tu ne connais pas ton mot de passe ?</strong>
</p>
<ul>
  <li>
  Si tu viens de terminer ta pré-inscription, <strong>il est dans le mail</strong> que
  nous t'avons envoyé (expéditeur pre-inscription@polytechnique.org).
  </li>
  <li>
  Si tu n'es jamais venu sur le site, <strong>il faut t'enregistrer auprès de
    nous</strong> pour obtenir un accès. Polytechnique.org c'est l'e-mail des X,
  l'annuaire en ligne, plus un tas d'autres services.  Nous te fournirons un accès le plus
  rapidement possible. <strong> <a href="inscrire.php">Clique ici pour nous demander tes
      paramètres personnels.</a></strong>
  </li>
</ul>
{/only_public}

<br />

<form action="{$smarty.server.REQUEST_URI}" method="post" id="login" onsubmit="doChallengeResponse(); return false;">
  <table class="tinybicol" cellpadding="4" summary="Formulaire de login">
    <tr>
      <th colspan="2">Connexion</th>
    </tr>
    <tr>
      <td class="titre">
        Login (prenom.nom) :
      </td>
      <td>
        <input type="text" name="username" size="20" maxlength="50"
          value="{insert name="getUserName" script="insert.password.inc.php"}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Mot de passe:
      </td>
      <td>
        <input type="password" name="password" size="10" maxlength="10" />
      </td>
    </tr>
    <tr>
      <td>
        <img src="{"images/pi.png"|url}" alt=" [ ? ] " />
        <a href="{"recovery.php"|url}">J'ai perdu mon mot de passe</a>
      </td>
      <td class="right">
        <input type="submit" name="submitbtn" value="Envoyer" />
      </td>
    </tr>
  </table>
</form>
<p>
Problème de connexion ? <a href="{"faq.php#connect"|url}">La réponse est là.</a>
<br />
(Activer obligatoirement le <strong>javascript</strong>)
</p>

{dynamic}
{if $smarty.request.response}<!-- failed login code //-->
<br />
<div class="erreur">
  Erreur d'identification. Essaie à nouveau !
</div>
{/if}

<!-- Set up the form with the challenge value and an empty reply value //-->
<form action="{$smarty.server.REQUEST_URI}" method="post" id="loginsub">
  <div>
    <input type="hidden" name="challenge" value="{$smarty.session.session->challenge}" />
    <input type="hidden" name="response"  value="" />
    <input type="hidden" name="username"  value="" />
  </div>
</form>
{/dynamic}

{literal}
<script type="text/javascript">
  <!--
  // Activate the appropriate input form field.
  if (document.forms.login.username.value == '') {
    document.forms.login.username.focus();
  } else {
    document.forms.login.password.focus();
  }
  // -->
</script>
{/literal}

{* vim:set et sw=2 sts=2 sws=2: *}
