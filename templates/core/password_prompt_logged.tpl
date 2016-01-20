{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<h1>{icon name=error} Page sécurisée</h1>

<div>
  La page que tu as demandée est classée comme sensible. Il est nécessaire de taper ton mot de passe
  pour y accéder, même avec l'accès permanent activé.
</div>
<br />

<form action="{$smarty.server.REQUEST_URI}" method="post" id="login" onsubmit='doChallengeResponseLogged(); return false;'>
  <table class="bicol" cellpadding="4" summary="Formulaire de login">
    <tr>
      <td class="titre">
        Nom d'utilisateur&nbsp;:
      </td>
      <td>{$smarty.session.hruid}</td>
      <td class="right" rowspan="3" style="vertical-align: middle">
        <input  type="submit" name="submitbtn" value="Envoyer" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Mot de passe&nbsp;:
      </td>
      <td>
        <input type="password" name="password" size="10" maxlength="256" />
        &nbsp;<a href="recovery">Perdu&nbsp;?</a>
      </td>
    </tr>
    <tr>
      <td {popup caption='Connexion permanente' width='300' text='Décoche cette case pour que le site oublie ce navigateur.<br />
        Il est conseillé de décocher la case si cette machine n\'est pas <b>strictement</b> personnelle'} colspan="2">
        <label><input type="checkbox" name="remember" checked="checked" />
        Garder l'accès aux services après déconnexion.</label>
      </td>
    </tr>
  </table>
</form>
<br />

<!-- Set up the form with the challenge value and an empty reply value -->
<form action="{$smarty.server.REQUEST_URI}" method="post" id="loginsub">
  <div>
    {xsrf_token_field}
    <input type="hidden" name="challenge" value="{$smarty.session.challenge}" />
    <input type="hidden" name="username"  value="{$smarty.session.uid}" />
    <input type="hidden" name="remember"  value="" />
    <input type="hidden" name="response"  value="" />
  </div>
</form>

{literal}
<script type="text/javascript">
  <!--
  // Activate the appropriate input form field.
  document.forms.login.password.focus();
  // -->
</script>
{/literal}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
