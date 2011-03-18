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

<h1>Identification</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post" id="login" onsubmit='doChallengeResponse(); return false;'>
  <table class="bicol">
    <tr>
      <td class="titre">Identifiant&nbsp;:</td>
      <td>
        <input type="text" name="username" />
        <input type="hidden" name="domain" value="hruid" />
      </td>
    </tr>
    <tr>
      <td class="titre">Mot de passe&nbsp;:</td>
      <td><input type="password" name="password" size="10" maxlength="256" /></td>
    </tr>
    <tr>
      <td {popup caption='Connexion permanente' width='300' text='Décocher cette case pour que le site oublie ce navigateur.<br />
        Il est conseillé de décocher la case si cette machine n\'est pas <b>strictement</b> personnelle'} colspan="2">
        <label><input type="checkbox" name="remember" checked="checked" />
          Garder l'accès aux services après déconnexion.
        </label>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center"><input  type="submit" name="submitbtn" value="Envoyer" /></td>
    </tr>
  </table>
</form>

<form action="{$smarty.server.REQUEST_URI}" method="post" id="loginsub">
  <div>
    <input type="hidden" name="challenge" value="{$smarty.session.challenge}" />
    <input type="hidden" name="username"  value="" />
    <input type="hidden" name="remember"  value="" />
    <input type="hidden" name="response"  value="" />
    <input type="hidden" name="xorpass"   value="" /> 
    <input type="hidden" name="domain"    value="" />
    <input type="hidden" name="auth_type" value="xnet" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
