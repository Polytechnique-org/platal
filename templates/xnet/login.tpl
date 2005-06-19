{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

<h1>
  Accès à Polytechnique.net
</h1>
 
<noscript>
  <p class="erreur">
    Ton navigateur n'accepte pas le javaScript !!
  </p>
  <p>
    Cette forme de script web est nécessaire pour l'utilisation du site.
    Pour en savoir plus, regarde la <a href="faq.php#connect">FAQ</a>.
  </p>
</noscript>

<table class='large' cellpadding="4" cellspacing="0">
  <tr>
    <th style="width: 50%;">
      Accès pour les Polytechniciens
    </th>
    <th>
      Accès pour les extérieurs
    </th>
  </tr>
  <tr>
    <td style="padding: 1em">
      <p class="descr">
      Il suffit de suivre <strong><a href="{$smarty.session.session->loginX}">ce lien</a></strong> qui va te rediriger vers
      <a href="https://www.polytechnique.org/">Polytechnique.org</a> brièvement.
      </p>
      <p class="descr">
      Une fois autentifié sur Polytechnique.org, tu seras redirigé sur X.net
      </p>
      <div class="center">
        <strong>
          <a href="{$smarty.session.session->loginX}">ME CONNECTER</a>
        </strong>
      </div>
    </td>
    <td style="padding: 1em">
      <form action="{$smarty.server.REQUEST_URI}" method="post" id="login" onsubmit="doChallengeResponse(); return false;">
        <table class="large" cellpadding="4" cellspacing="0" summary="Formulaire de login">
          <tr>
            <th colspan="2">Connexion
              <input type="hidden" name="remember" value="" />
              <input type="hidden" name="domain" value="" />
            </th>
          </tr>
          <tr>
            <td class="titre">
              Login
            </td>
            <td>
              <input type="text" name="username" size="20" maxlength="50" value="" />
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
            <td colspan="2" class="center">
              <input type="submit" name="submitbtn" value="Envoyer" />
            </td>
          </tr>
        </table>
      </form>

      (Activer obligatoirement le <strong>javascript</strong>)
      {if $smarty.request.response}<!-- failed login code //-->
      <div class="erreur">
        Erreur d'identification. Essaie à nouveau !
      </div>
      {/if}
    </td>
  </tr>
</table>

<!-- Set up the form with the challenge value and an empty reply value //-->
<form action="{$smarty.server.REQUEST_URI}" method="post" id="loginsub">
  <div>
    <input type="hidden" name="challenge" value="{$smarty.session.session->challenge}" />
    <input type="hidden" name="response"  value="" />
    <input type="hidden" name="username"  value="" />
    <input type="hidden" name="remember"  value="" />
    <input type="hidden" name="domain"    value="" />
  </div>
</form>

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
