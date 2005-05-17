{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

<h1>{$asso.nom} : gestion des memebres</h1>

<h2>
  Édition du profil de {$user.prenom} {$user.nom}
  {if $user.origine eq 'X'}
  (X{$user.promo})
  <a href="https://www.polytechnique.org/fiche.php?user={$user.alias}"><img src="{rel}/images/loupe.gif" alt="Voir la fiche" /></a>
  {/if}
  <a href="?del={$user.email}"><img src="{rel}/images/del.png" alt="Suppression du compte" /></a>
  <a href="mailto:{$user.email}"><img src="{rel}/images/mail.png" alt="Ecrire un mail" /></a>
</h2>

<form method="post" action="{$smarty.server.REQUEST_URI}">
  <table cellpadding="0" cellspacing="0" class='tiny'>
    <tr>
      <td class="titre">
        Permissions:
      </td>
      <td>
        <select name="is_admin">
          <option value="0" {if !$user.perms}selected="selected"{/if}>Membre</option>
          <option value="1" {if $user.perms}selected="selected"{/if}>Administrateur</option>
        </select>
      </td>
    </tr>
    {if $user.origine neq X}
    <tr>
      <td class="titre">
        Prénom:
      </td>
      <td>
        <input type="text" value="{$user.prenom}" name="prenom" size="40" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Nom:
      </td>
      <td>
        <input type="text" value="{$user.nom}" name="nom" size="40" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Email:
      </td>
      <td>
        <input type="text" value="{$user.email}" name="email" size="40" />
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
        <a href='listes-members.php?liste={$liste.list}'>{$liste.list}</a>
      </td>
      <td>{$liste.desc}</td>
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
        <a href='alias-admin.php?liste={$a.alias}'>{$a.alias}</a>
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


{* vim:set et sw=2 sts=2 sws=2: *}
