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

<script type="text/javascript">
{literal}
  function showInformations(box)
  {
      var state = (box.value != 'virtual') ? '' : 'none';
      document.getElementById('prenom').style.display = state;
      document.getElementById('sexe').style.display = state;
      document.getElementById('make_X').style.display = state;
      document.getElementById('password').style.display = state;
  }

  function showXInput(box)
  {
     if (box.checked) {
       document.getElementById('make_X_cb').style.display = 'none';
       document.getElementById('make_X_login').style.display = '';
     }
  }
{/literal}
</script>

<h1>{$asso->nom}&nbsp;: gestion des membres</h1>

<p>
[<a href='{$platal->ns}annuaire'>Retour à l'annuaire</a>]
</p>

<h2>
  Édition du profil de {profile user=$user groupperms=false sex=false promo=true}
  <a href="mailto:{$user->bestEmail()}">{icon name=email title="mail"}</a>
</h2>

<form method="post" action="{$platal->ns}member/{$platal->argv[1]}">
  {xsrf_token_field}
  <table cellpadding="0" cellspacing="0" class='tinybicol'>
    <tr class="pair">
      <td class="titre">
        Permissions&nbsp;:
      </td>
      <td>
        <select name="group_perms">
          <option value="membre" {if $user->group_perms eq 'membre'}selected="selected"{/if}>Membre</option>
          <option value="admin" {if $user->group_perms eq 'admin'}selected="selected"{/if}>Animateur</option>
        </select>
      </td>
    </tr>
    {if !$user->profile()}
    <tr class="impair">
      <td class="titre">
        Type d'utilisateur&nbsp;:
      </td>
      <td>
        <select name="type" onchange="showInformations(this); return true">
          <option value="xnet" {if $user->type neq 'virtual'}selected="selected"{/if}>Personne physique</option>
          <option value="virtual" {if $user->type eq "virtual"}selected="selected"{/if}>Personne morale</option>
        </select>
      </td>
    </tr>
      <tr id="prenom" class="impair" {if $user->type eq "virtual"}style="display: none"{/if}>
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
    <tr id="sexe" class="impair" {if $user->type eq "virtual"}style="display: none"{/if}>
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
    {/if}
    {if !$user->profile() || !$user->perms}
    <tr class="impair">
      <td class="titre">
        Email&nbsp;:
      </td>
      <td>
        <input type="text" value="{$user->forlifeEmail()}" name="email" size="40" />
      </td>
    </tr>
    {/if}
    <tr class="impair">
      <td class="titre">
        Commentaire&nbsp;:
      </td>
      <td>
        <input type="text" name="comm" value="{$user->group_comm}" size="40" maxlength="255" /><br />
        <small>Poste, origine&hellip; (accessible à toutes les personnes autorisées à consulter l'annuaire)</small>
      </td>
    </tr>
    {if $user->type eq 'xnet'}
    <tr class="impair" id="password">
      <td class="titre">Mot de passe&nbsp;:</td>
      <td>
        <div style="float: left">
          <input type="text" name="new_plain_password" size="10" maxlength="256" value="********" />
          <input type="hidden" name="pwhash" value="" />
        </div>
        <div style="float: left; margin-top: 5px;">
          {checkpasswd prompt="new_plain_password" submit="dummy_none"}
        </div>
        {if !$onlyGroup}
        <div style="clear: both">
          <small class="error">
            Attention, cet utilisateur est inscrit à d'autres groupes, changer son mot de passe modifiera aussi ses accès aux autres groupes.
          </small>
        </div>
        {/if}
      </td>
    </tr>
    <tr id="make_X">
      <td colspan="2">
        <span id="make_X_cb">
          <input type="checkbox" name="is_x" id="is_x" onclick="showXInput(this);" onchange="showXInput(this);" />
          <label for="is_x">coche cette case s'il s'agit d'un X</label>
        </span>
        <span id="make_X_login" style="display: none">
          <span class="titre">Identifiant (prenom.nom.promo)&nbsp;:</span>
          <input type="text" name="login_X" value="" />
        </span>
      </td>
    </tr>
    {/if}
  </table>

  <h2>Abonnement aux listes</h2>

  <table cellpadding="0" cellspacing="0" class='large'>
    <tr>
      <th>&nbsp;</th>
      <th>Liste</th>
      <th>Description</th>
      <th>Nb</th>
    </tr>
    {foreach from=$listes item=liste}
    <tr>
      <td class='right'>
        <input type='hidden' name='ml1[{$liste.list}]' value='{$liste.sub}' />
        <input type='checkbox' name='ml2[{$liste.list}]' {if $liste.sub eq 2}checked="checked"{/if} />
      </td>
      <td>
        <a href='{$platal->ns}lists/members/{$liste.list}'>{$liste.list}</a>
      </td>
      <td>{$liste.desc|smarty:nodefaults}</td>
      <td class='right'>{$liste.nbsub}</td>
    </tr>
    {foreachelse}
    <tr><td colspan='4'>Pas de listes pour ce groupe</td></tr>
    {/foreach}
  </table>

  <h2>Abonnement aux alias</h2>

  <table cellpadding="0" cellspacing="0" class='large'>
    <tr>
      <th>&nbsp;</th>
      <th>Alias</th>
    </tr>

    {foreach from=$alias key=address item=sub}
    <tr>
      <td align='right'>
        <input type='hidden' name='ml3[{$address}]' value='{$sub}' />
        <input type='checkbox' name='ml4[{$address}]' {if $sub}checked="checked"{/if} />
      </td>
      <td>
        <a href='{$platal->ns}alias/admin/{$address}'>{$address}</a>
      </td>
    </tr>
    {foreachelse}
    <tr><td colspan='2'>Pas d'alias pour ce groupe</td></tr>
    {/foreach}
  </table>

  <div class="center">
    <br />
    <input type="submit" name='change' value="Valider ces changements" onclick="return hashResponse('new_plain_password', false, false);" />
    &nbsp;
    <input type="reset" value="Annuler ces changements" />
  </div>

</form>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
