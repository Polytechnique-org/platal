{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

{if $referer || $platal->pl_self() neq 'login'}
<h1>
  Accès restreint
</h1>
<p>
  Bonjour,<br />
  La page que vous avez demandé
  (<strong>{if $referer}{$smarty.server.HTTP_REFERER}{else}{$globals->baseurl}/{$platal->pl_self()}{/if}</strong>)
  nécessite une authentification.
</p>
{else}
<h1>
  Accès réservé aux polytechniciens
</h1>
{/if}
{if $smarty.session.auth ge AUTH_COOKIE}
<p>
<strong>Merci de rentrer ton mot de passe pour démarrer une connexion au site.</strong>
Si tu n'es pas {insert name="getName"}, change le login ci-dessous, ou rends-toi sur
<a href="register/">la page d'inscription</a>.
</p>
{/if}

{if !$smarty.session.auth}
<p>
<strong>Tu ne connais pas ton mot de passe ?</strong>
</p>
<ul>
  <li>
  Si tu viens de terminer ta pré-inscription, <strong>il est dans le mail</strong> que
  nous t'avons envoyé (expéditeur pre-inscription@{#globals.mail.domain#}).
  </li>
  <li>
  Si tu n'es jamais venu sur le site, <strong>il faut t'enregistrer auprès de
    nous</strong> pour obtenir un accès. {#globals.core.sitename#} c'est l'e-mail des X,
  l'annuaire en ligne, plus un tas d'autres services.  Nous te fournirons un accès le plus
  rapidement possible. <strong> <a href="register/">Clique ici pour nous demander tes
      paramètres personnels.</a></strong>
  </li>
</ul>
{/if}

<br />

<form action="{$smarty.server.REQUEST_URI}" method="post" id="login" onsubmit="doChallengeResponse(); return false;" style="display: none">
  <table class="bicol" cellpadding="4" summary="Formulaire de login">
    <tr>
      <th colspan="2">{if $smarty.server.HTTPS}{icon name=lock}{/if} Identification
      {if !$smarty.server.HTTPS}
      (<a href="{$globals->baseurl|replace:"http":"https"}/{$platal->pl_self()}">{icon name=lock_add} Passer en connexion sécurisée</a>)
      {/if}
      </th>
    </tr>
    <tr style="white-space: nowrap">
      <td class="titre">
        Adresse email :
      </td>
      <td>
        <input type="text" name="username" size="20" maxlength="50" value="{insert name="getUserName"}" />&nbsp;@&nbsp;<select name="domain">
          <option value="login">{#globals.mail.domain#} / {#globals.mail.domain2#}</option>
          <option value="alias" {if $smarty.cookies.ORGdomain eq alias}selected="selected"{/if}>
          {#globals.mail.alias_dom#} / {#globals.mail.alias_dom2#}
          </option>
          {$smarty.cookies.domain}
        </select>
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
      <td></td>
      <td {popup caption='Connexion permanente' width='300' text="Coche cette case pour être automatiquement reconnu à ta prochaine connexion
        depuis cet ordinateur.<br />
        Il n'est pas conseillé de cocher la case si cette machine n'est pas <b>strictement</b> personnelle"}>
        <input type="checkbox" name="remember" /> Garder l'accès aux services après déconnexion
      </td>
    </tr>
    <tr>
      <td colspan="2">
      <table width="100%"><tr>
      <td>
        <a href="recovery">mot de passe perdu ?</a>
      </td>
      <td class="right">
        <input type="submit" name="submitbtn" value="Envoyer" />
      </td>
      </tr></table>
      </td>
    </tr>
  </table>
  <p>     
    Problème de connexion ? <a href="Xorg/FAQ?display=light#connect" class="popup2">La réponse est là.</a>
  </p>
</form>

<div id="nologin" style="background: #fcc; color: red">
  Pour assurer la confidentialité de ton mot de passe, il est chiffré sur ta machine
  avant de nous être transmis. Pour cela, il faut
  <a href="Xorg/FAQ?display=light#connect" class="popup2">activer javascript</a>
  dans ton navigateur, ce qui n'est actuellement pas le cas.
  <div class="center" style="margin-top: 1ex">
    <strong>Active le javascript et recharge cette page pour pouvoir te connecter.</strong>
  </div>
</div>

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
  <a href="{$globals->baseurl|replace:"http":"https"}/{$platal->pl_self()}">
    {icon name=lock_add} Passer en connexion sécurisée</a>
  </div><br />
  Plus d'informations sur la connexion sécurisée se trouvent
  <a href="Xorg/CertificatDeSécurité?display=light" class="popup2">sur cette page</a>.
  {/if}
</div>

{if $smarty.request.response}<!-- failed login code //-->
<br />
<div class="erreur">
  Erreur d'identification. Essaie à nouveau !
</div>
{/if}

<!-- Set up the form with the challenge value and an empty reply value //-->
<form action="{$smarty.server.REQUEST_URI}" method="post" id="loginsub">
  <div>
    <input type="hidden" name="challenge" value="{$smarty.session.challenge}" />
    <input type="hidden" name="response"  value="" />
    <input type="hidden" name="xorpass"  value="" />
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
