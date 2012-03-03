{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

{if t($external_auth) || $platal->pl_self() neq 'login'}
<h1>
  Accès restreint
</h1>
<p>
  Bonjour,<br />
  {if t($group)}
    le site du groupe {$group}
  {else}
    la page que vous avez demandée
  {/if}
  (<strong>{if t($external_auth)}{$smarty.server.HTTP_REFERER|truncate:120:"...":false}{else}{$globals->baseurl}/{$platal->pl_self()}{/if}</strong>)
  nécessite une authentification.
</p>
{else}
<h1>
  Accès réservé aux polytechniciens
</h1>
{/if}

<form action="{$smarty.server.REQUEST_URI}" method="post" id="login" onsubmit="doChallengeResponse(); return false;" style="display: none">
  <table class="bicol" cellpadding="4" summary="Formulaire de login">
    <tr>
      <th colspan="2">{if $smarty.server.HTTPS}{icon name=lock}{/if} Identification
      {if !$smarty.server.HTTPS && #globals.core.secure_domain#}
      (<a href="https://{#globals.core.secure_domain#}{$smarty.server.REQUEST_URI}">{icon name=lock_add} Passer en connexion sécurisée</a>)
      {/if}
      </th>
    </tr>
    <tr style="white-space: nowrap">
      <td class="titre">
        Identifiant ou email&nbsp;:
      </td>
      <td>
        <input type="text" name="username" size="40" maxlength="100" value="{insert name="getUserName"}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Mot de passe&nbsp;:
      </td>
      <td>
        <input type="password" name="password" size="10" maxlength="256" />
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <script type="text/javascript">{literal}
          function confirm_remember(input) {
            if (input.checked && !confirm('Cocher cette case te permet d\'être automatiquement reconnu à ta prochaine connexion depuis cet ordinateur. '
            + 'Il n\'est pas conseillé de cocher la case si cette machine n\'est pas strictement personnelle.\n\nVeux-tu vraiment cocher cette case ?')) {
              input.checked = false;
              return false;
            }
            return true;
          }
        {/literal}</script>
        <input type="checkbox" name="remember" id="remember" onchange="return confirm_remember(this);" /><label for="remember">Garder l'accès aux services après déconnexion.</label>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span style="float: left">
        Mot de passe perdu&nbsp;:
        <a href="recovery">Étudiants et diplômés de l'X</a> |
        <a href="recovery/ext">Extérieurs</a>
        </span>

        <input type="submit" name="submitbtn" value="Me connecter" style="float: right" />
      </td>
    </tr>
  </table>
</form>

{if !$smarty.session.auth}
<p>
<strong>Tu ne connais pas ton mot de passe&nbsp;?</strong>
</p>
  Si tu n'es jamais venu sur le site, <strong>il faut t'enregistrer auprès de
    nous</strong> pour obtenir un accès. {#globals.core.sitename#} c'est l'email des X,
  l'annuaire en ligne, plus un tas d'autres services.  Nous te fournirons un accès le plus
  rapidement possible. <strong> <a href="register/">Clique ici pour nous demander tes
      paramètres personnels.</a></strong>
{/if}

<div id="nologin" style="background: #fcc; color: red">
  Pour assurer la confidentialité de ton mot de passe, il est chiffré sur ta machine
  avant de nous être transmis. Pour cela, il faut
  <a href="Xorg/FAQ?display=light#connect" class="popup2">activer javascript</a>
  dans ton navigateur, ce qui n'est actuellement pas le cas.
  <div class="center" style="margin-top: 1ex">
    <strong>Active le javascript et recharge cette page pour pouvoir te connecter.</strong>
  </div>
</div>

<p>
  <strong>Problème de connexion&nbsp;?</strong> <a href="Xorg/FAQ?display=light#connect" class="popup2">La réponse est là.</a>
</p>

<script type="text/javascript">
  document.getElementById('login').style.display="";
  document.getElementById('nologin').style.display="none";
</script>

<hr />

<div class="smaller">
  {if $smarty.server.HTTPS}
  {icon name=lock} Tu utilises actuellement une connexion HTTPS sécurisée. Aucune information ne circule
  en clair entre chez toi et Polytechnique.org, ce qui assure une confidentialité maximale.
  {else}
  {icon name=lock_open} Tu utilises actuellement une connexion HTTP non sécurisée. Toutes les informations
  (<strong>excepté le mot de passe de connexion au site</strong>) circulent en clair entre chez toi et
  Polytechnique.org. Tu peux basculer sur une connexion sécurisée en cliquant sur le lien
  <div class="center">
  <a href="https://{#globals.core.secure_domain#}{$smarty.server.REQUEST_URI}">
    {icon name=lock_add} Passer en connexion sécurisée</a>
  </div><br />
  Plus d'informations sur la connexion sécurisée se trouvent
  <a href="Xorg/CertificatDeSécurité?display=light" class="popup2">sur cette page</a>.
  {/if}
</div>

<!-- Set up the form with the challenge value and an empty reply value //-->
<form action="{$smarty.server.REQUEST_URI}" method="post" id="loginsub">
  <div>
    {xsrf_token_field}
    <input type="hidden" name="challenge" value="{$smarty.session.challenge}" />
    <input type="hidden" name="response"  value="" />
    <input type="hidden" name="xorpass"   value="" />
    <input type="hidden" name="username"  value="" />
    <input type="hidden" name="remember"  value="" />
    {if t($external_auth)}
      <input type="hidden" name="external_auth" value="1" />
    {/if}
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

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
