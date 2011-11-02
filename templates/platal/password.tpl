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


<h1>
  {if t($xnet)}Création du mot de passe{else}Changer de mot de passe{/if}
</h1>

<p>
  Le mot de passe doit faire au moins <strong>6 caractères</strong> et comporter deux types de
  caractères parmi les suivants&nbsp;: lettres minuscules, lettres majuscules, chiffres, caractères spéciaux.
  Attention au type de clavier que tu utilises (qwerty&nbsp;?) et aux majuscules/minuscules.
</p>
<p>
  Pour une sécurité optimale, le mot de passe {if !t($xnet)}{if !t($xnet_reset)} circule de manière chiffrée (https) et{/if}{/if} est stocké chiffré irréversiblement sur nos serveurs.
</p>
<br />
<fieldset style="width: 70%; margin-left: 15%">
  <legend>{icon name=lock} Saisie du {if !t($xnet)}nouveau {/if}mot de passe</legend>
  <form action="{$smarty.server.REQUEST_URI}" method="post" id="login">
  {xsrf_token_field}
    <table style="width: 100%">
      <tr>
        <td class="titre">
          Mot de passe&nbsp;:
        </td>
        <td>
          <input type="password" size="10" maxlength="256" name="new1" />
        </td>
      </tr>
      <tr>
        <td class="titre">
          Confirmation&nbsp;:
        </td>
        <td>
          <input type="password" size="10" maxlength="256" name="new2" />
        </td>
      </tr>
      <tr>
        <td class="titre">
          Sécurité
        </td>
        <td>
          {checkpasswd prompt="new1" submit="submitn"}
        </td>
      </tr>
      <tr>
        <td>
          <input type="hidden" name="username" value="{$hruid}" />
          <input type="hidden" name="password" value="" />
          <input type="hidden" name="domain" value="email" />
        </td>
        <td {popup caption='Connexion permanente' width='300' text='Décocher cette case pour que le site oublie ce navigateur.<br />
          Il est conseillé de décocher la case si cette machine n\'est pas <b>strictement</b> personnelle'} colspan="2">
          <label><input type="checkbox" name="remember" checked="checked" />
            Garder l'accès aux services après déconnexion.
          </label>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="center">
          <input type="hidden" name="pwhash" value="" />
          <input type="submit" value="{if t($xnet)}Créer{else}Changer{/if}" name="submitn" onclick="return hashResponse('new1', 'new2', true, {$do_auth});" />
        </td>
      </tr>
    </table>
  </form>
</fieldset>

<form action="{$smarty.server.REQUEST_URI}" method="post" id="loginsub">
  <div>
    <input type="hidden" name="challenge" value="{$smarty.session.challenge}" />
    <input type="hidden" name="username"  value="" />
    <input type="hidden" name="remember"  value="" />
    <input type="hidden" name="response"  value="" />
    <input type="hidden" name="xorpass"   value="" />
    <input type="hidden" name="domain"    value="" />
    <input type="hidden" name="auth_type" value="{if t($xnet)}xnet{/if}" />
    <input type="hidden" name="pwhash"    value="" />
    {if t($xnet)}<input type="hidden" name="wait" />{/if}
  </div>
</form>

{if !t($xnet)}{if !t($xnet_reset)}
<p>
  Note bien qu'il s'agit là du mot de passe te permettant de t'authentifier sur le site {#globals.core.sitename#}&nbsp;;
  le mot de passe te permettant d'utiliser le serveur <a
  href="{"./Xorg/SMTPSécurisé"|urlencode}">SMTP</a>
  et <a href="{"Xorg/NNTPSécurisé"|urlencode}">NNTP</a>
  de {#globals.core.sitename#} (si tu as <a href="./password/smtp">activé l'accès SMTP et NNTP</a>)
  est indépendant de celui-ci et tu peux le modifier <a href="./password/smtp">ici</a>.
</p>
{/if}{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
