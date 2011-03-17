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

{literal}
<script type="text/javascript">
/* <![CDATA[ */
function add_user_to_url(f) {
  f.action += '/' + f.login.value;
}
/* ]]> */
</script>
{/literal}

<fieldset>
  <legend>{icon name=user_edit} Administrer un compte</legend>
  <form method="post" action="admin/user" onsubmit="add_user_to_url(this); return true">
    {xsrf_token_field}
    <p>
      Il est possible d'entrer ici n'importe quelle adresse mail&nbsp;: redirection, melix, ou alias.
    </p>
    <p>
      <input type="text" name="login" size="40" maxlength="255"
             value="{if t($smarty.request.login)}{$smarty.request.login}{/if}" />
      <input type="submit" name="select" value="edit" />
      <input type="submit" name="su_account" value="su" />
      <input type="submit" name="log_account" value="logs" />
    </p>
  </form>
</fieldset>

{if $users->total() > 0}
<fieldset>
  <legend>Liste des comptes manuels</legend>

  <ul>
    {iterate item=user from=$users}
    <li>
      <a href="admin/user/{$user->hruid}">{$user->fullName()} ({$user->hruid} - {$user->type})</a>
    </li>
    {/iterate}
  </ul>
</fieldset>
{/if}

<fieldset>
  <legend>Nouveau compte</legend>

  <ul>
    <li><a href="admin/add_accounts">Ajout d'un ensemble d'utilisateurs</a></li>
    <li>Ajouter un compte manuel :</li>
  </ul>
  <p>
    <form action="admin/accounts" method="post">
      {xsrf_token_field}
      <table style="width: 75%; margin-left: auto; margin-right: auto">
        <tr>
          <td class="titre">Type de compte</td>
          <td>
            <select name="type">
              <option value="ax">Personnel de l'AX</option>
              <option value="fx">Personnel de la FX</option>
              <option value="school">Personnel de l'Ecole</option>
            </select>
            <a href="admin/account/types">Détail des permissions associées</a>
          </td>
        </tr>
        <tr>
          <td class="titre">Nom</td>
          <td><input type="text" name="lastname" size=60 maxlength="255" value="" /></td>
        </tr>
        <tr>
          <td class="titre">Prénom</td>
          <td><input type="text" name="firstname" size=60" maxlength="255" value="" /></td>
        </tr>
        <tr>
          <td class="titre">Sexe</td>
          <td>
            <select name="sex">
              <option value="female">Femme</option>
              <option value="male">Homme</option>
            </select>
          </td>
        </tr>
        <tr>
          <td class="titre">Email</td>
          <td><input type="text" name="email" size="60" maxlength="255" value="" /></td>
        </tr>
        <tr>
          <td class="titre">Mot de passe</td>
          <td>
            <div style="float: left">
              <input type="password" name="password" size="10" maxlength="256" />
              <input type="hidden" name="pwhash" value="" />
            </div>
            <div style="float: left; margin-top: 5px">
              {checkpasswd prompt="password" submit="create_account" text="Créer le compte"}
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="center">
            <input type="submit" name="create_account" value="Créer le compte"
                   onclick="return hashResponse('password', false, false, false);" />
          </td>
        </tr>
      </table>
    </form>
  </p>
</fieldset>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
