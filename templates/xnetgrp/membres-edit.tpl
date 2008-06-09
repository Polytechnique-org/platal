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

<script type="text/javascript">
{literal}
  function showInformations(box)
  {
      var state = (box.value == 'ext') ? '' : 'none';
      document.getElementById('prenom').style.display = state;
      document.getElementById('sexe').style.display = state;
      document.getElementById('make_X').style.display = state;
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

<h1>{$asso.nom}&nbsp;: gestion des membres</h1>

<p>
[<a href='{$platal->ns}annuaire'>Retour à l'annuaire</a>]
</p>

<h2>
  Édition du profil de {$user.prenom} {$user.nom}
  {if $user.origine eq 'X'}
  (X{$user.promo})
  <a href="https://www.polytechnique.org/profile/{$user.alias}">{icon name=user_suit title="fiche"}</a>
  {/if}
  <a href="{$platal->ns}member/del/{$user.email}">{icon name=delete title="Suppression du compte"}</a>
  <a href="mailto:{$user.email}">{icon name=email title="mail"}</a>
</h2>

<form method="post" action="{$platal->ns}member/{$platal->argv[1]}">
  <table cellpadding="0" cellspacing="0" class='tinybicol'>
    <tr class="pair">
      <td class="titre">
        Permissions:
      </td>
      <td>
        <select name="is_admin">
          <option value="0" {if !$user.perms}selected="selected"{/if}>Membre</option>
          <option value="1" {if $user.perms}selected="selected"{/if}>Animateur</option>
        </select>
      </td>
    </tr>
    {if $user.origine neq X}
    <tr class="impair">
      <td class="titre">
        Type d'utilisateur&nbsp;:
      </td>
      <td>
        <select name="origine" onchange="showInformations(this); return true">
          <option value="ext" {if $user.origine eq "ext"}selected="selected"{/if}>Personne physique</option>
          <option value="groupe" {if $user.origine eq "groupe"}selected="selected"{/if}>Personne morale</option>
        </select>
      </td>
    </tr>
    <tr id="prenom" class="impair" {if $user.origine eq "groupe"}style="display: none"{/if}>
      <td class="titre">
        Prénom&nbsp;:
      </td>
      <td>
        <input type="text" value="{$user.prenom}" name="prenom" size="40" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Nom&nbsp;:
      </td>
      <td>
        <input type="text" value="{$user.nom}" name="nom" size="40" />
      </td>
    </tr>
    <tr id="sexe" class="impair" {if $user.origine eq "groupe"}style="display: none"{/if}>
      <td class="titre">
        Sexe&nbsp;:
      </td>
      <td>
        <select name="sexe">
          <option value="0"{if $user.sexe eq 0} selected="selected"{/if}>Homme</option>
          <option value="1"{if $user.sexe eq 1} selected="selected"{/if}>Femme</option>
        </select>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Email:
      </td>
      <td>
        <input type="text" value="{$user.email}" name="email" size="40" />
      </td>
    </tr>
    <tr id="make_X" {if $user.origine eq "groupe"}style="display: none"{/if}>
      <td colspan="2">
        <span id="make_X_cb">
          <input type="checkbox" name="is_x" id="is_x" onclick="showXInput(this);" onchange="showXInput(this);" />
          <label for="is_x">coche cette case si il s'agit d'un X</label>
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

    {foreach from=$alias item=a}
    <tr>
      <td align='right'>
        <input type='hidden' name='ml3[{$a.alias}]' value='{$a.sub}' />
        <input type='checkbox' name='ml4[{$a.alias}]' {if $a.sub}checked="checked"{/if} />
      </td>
      <td>
        <a href='{$platal->ns}alias/admin/{$a.alias}'>{$a.alias}</a>
      </td>
    </tr>
    {foreachelse}
    <tr><td colspan='2'>Pas d'alias pour ce groupe</td></tr>
    {/foreach}
  </table>

  <div class="center">
    <br />
    <input type="submit" name='change' value="Valider ces changements" />
    &nbsp;
    <input type="reset" value="Annuler ces changements" />
  </div>                                                                      

</form>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
