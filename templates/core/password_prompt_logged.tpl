{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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


<div class="center">
  <table>
    <tr>
      <td>
        {icon name=error}
      </td>
      <td>
        <span class="smaller">
          <strong>
            Pour des raisons de <span class="erreur">sécurité</span>, il est obligatoire de taper ton mot de passe, même
            avec l'accès permanent, pour certaines opérations sensibles.
          </strong>
        </span>
      </td>
      <td>
        {icon name=error}
      </td>
    </tr>
  </table>
</div>
<br />

<form action="{$smarty.server.REQUEST_URI}" method="post" id="login" onsubmit='doChallengeResponse(); return false;'>
  <table class="tinybicol" cellpadding="4" summary="Formulaire de login">
    <tr>
      <td class="titre">
        Mot de passe&nbsp;:
      </td>
      <td>
        <input type="password" name="password" size="10" maxlength="10" />
        &nbsp;<a href="recovery">Perdu ?</a>
      </td>
      <td class="right" rowspan="2" style="vertical-align: middle">
        <input  type="submit" name="submitbtn" value="Envoyer" />
      </td>
    </tr>
    <tr>
      <td {popup caption='Connexion permanente' width='300' text='Décoche cette case pour que le site oublie ce navigateur.<br />
        Il est conseillé de décocher la case si cette machine n\'est pas <b>strictement</b> personnelle'} colspan="2">
        <input type="checkbox" name="remember" checked="checked" /> Garder l'accès aux services après déconnexion.
      </td>
    </tr>
  </table>
</form>
<br />
{if $smarty.request.response}<!-- failed login code -->
<div class="erreur">
  Erreur d'identification. Essaie à nouveau !
</div>
{/if}

<!-- Set up the form with the challenge value and an empty reply value -->
<form action="{$smarty.server.REQUEST_URI}" method="post" id="loginsub">
  <div>
    {xsrf_token_field}
    <input type="hidden" name="challenge" value="{$smarty.session.challenge}" />
    <input type="hidden" name="username"  value="{$smarty.cookies.ORGuid}" />
    <input type="hidden" name="xorpass"  value="" />
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

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
