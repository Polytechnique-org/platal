{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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
  Édition de tes données dans l'annuaire
</h1>

<form method="post" action="{$platal->ns}edit">
  {xsrf_token_field}
  <table cellpadding="0" cellspacing="0" class='tinybicol'>
    <tr id="prenom" class="impair">
      <td class="titre">
        Nom affiché&nbsp;:
      </td>
      <td>
        <input type="text" value="{$user->displayName()}" name="display_name" size="40" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Nom complet&nbsp;:
      </td>
      <td>
        <input type="text" value="{$user->fullName()}" name="full_name" size="40" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Nom annuaire&nbsp;:
      </td>
      <td>
        <input type="text" value="{$user->directoryName()}" name="directory_name" size="40" />
      </td>
    </tr>
    <tr id="sexe" class="impair">
      <td class="titre">
        Sexe&nbsp;:
      </td>
      <td>
        <select name="sex">
          <option value="male"{if !$user->isFemale()} selected="selected"{/if}>Homme</option>
          <option value="female"{if $user->isFemale()} selected="selected"{/if}>Femme</option>
        </select>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Email&nbsp;:
      </td>
      <td>
        <input type="text" value="{$user->forlifeEmail()}" name="email" size="40" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Mot de passe&nbsp;:</td>
      <td>
        <input type="text" name="password1" size="10" maxlength="256" value="********" />
        <input type="hidden" name="pwhash" value="" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Retape-le une fois&nbsp;:</td>
      <td>
        <input type="text" name="password2" size="10" maxlength="256" value="" />
      </td>
    </tr>
    <tr>
      <td></td>
      <td>{checkpasswd prompt="password1" submit="dummy_none"}</td>
    </tr>
    <tr id="make_X">
      <td colspan="2">
        <span id="make_X_cb">
          <input type="checkbox" name="is_x" id="is_x" onclick="$('#make_X_cb').hide(); $('#make_X_login').show()" />
          <label for="is_x">coche cette case si tu es en fait un X</label>
        </span>
        <span id="make_X_login" style="display: none">
          <span class="titre">Identifiant (prenom.nom.promo)&nbsp;:</span>
          <input type="text" name="login_X" value="" />
        </span>
      </td>
    </tr>
  </table>
  {if $user->groupCount() eq 0}
  <p>
    Supprimer mon compte&nbsp;:&nbsp;<input type="text" name="delete" value="NON" size="3" maxlength="3" /><br />
    <small>(Écrire « OUI » en majuscule pour que la suppression soit prise en compte.)</small>
  </p>
  {/if}

  <div class="center">
    <br />
    <input type="submit" name='change' value="Valider ces changements" onclick="return hashResponse('password1', 'password2', true);" />
    &nbsp;
    <input type="reset" value="Annuler ces changements" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
