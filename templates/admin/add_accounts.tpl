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

<h1>Mise à jour de l'annuaire</h1>

{if $newAccounts}
<p>
  Les comptes suivants ont été ajoutés&nbsp;:
  <ul>
  {foreach from=$newAccounts key=hruid item=name}
    <li><a href="{$platal->ns}admin/user/{$hruid}">{$name}</a></li>
  {/foreach}
  </ul>
</p>
{/if}

{if !$add_type}
<form action="{$platal->pl_self()}" method="post">
<table class="tinybicol">
  <tr>
    <td class="center">
      <strong>Ajouter&nbsp;:</strong>
      <label><input type="radio" name="add_type" value="promo" checked="checked" /> une promotion</label> -
      <label><input type="radio" name="add_type" value="account" /> des comptes seuls</label><br />
    </td>
  </tr>
  <tr>
    <td class="center">
      <strong>Mettre à jour&nbsp;:</strong>
      <label><input type="radio" name="add_type" value="ax_id" /> les matricules AX</label>
    </td>
  </tr>
  <tr>
    <td class="center"><input type='submit' value='Continuer' /></td>
  </tr>
</table>
</form>

{else}
<form action="{$platal->pl_self()}" method="post">
<table class="tinybicol" style="margin-bottom: 1em">
  <tr>
    <td class="center">
      <strong>Promotion&nbsp;:</strong>
      <input type="text" name="promotion" size="4" maxlength="4" />
      <input type="hidden" name="add_type" value="{$add_type}" />
    </td>
  </tr>
{if $add_type eq 'promo'}
  <tr>
    <td class="center">
      <strong>Formation&nbsp;:</strong>
      <label><input type="radio" name="edu_type" value="X" checked="checked" /> X</label> -
      <label><input type="radio" name="edu_type" value="M" /> master</label> -
      <label><input type="radio" name="edu_type" value="D" /> doctorat</label>
    </td>
  </tr>
</table>
<table class="bicol">
  <tr>
    <td>Nom</td>
    <td>Prénom</td>
    <td>Date de naissance</td>
    <td>Sexe (F/M)</td>
    <td>Matricule École</td>
    <td>Matricule AX</td>
  </tr>
  <tr>
    <td colspan="6"><textarea name="people" rows="20" cols="80"></textarea></td>
  </tr>
</table>

{elseif $add_type eq 'account'}
  <tr>
    <td class="center">
      <strong>Type de compte&nbsp;:</strong>
      <select name="type">
      {foreach from=$account_types item=type}
        <option value="{$type}">{$type}</option>
      {/foreach}
      </select>
    </td>
  </tr>
</table>
<br />
<table class="bicol">
  <tr>
    <td>Nom</td>
    <td>Prénom</td>
    <td>Adresse email</td>
    <td>Sexe (F/M)</td>
  </tr>
  <tr>
    <td colspan="4"><textarea name="people" rows="20" cols="80"></textarea></td>
  </tr>
</table>

{elseif $add_type eq 'ax_id'}
</table>
<br />
<table class="bicol">
  <tr>
    <td>Nom</td>
    <td>Prénom</td>
    <td>Matricule AX</td>
  </tr>
  <tr>
    <td colspan="4"><textarea name="people" rows="20" cols="80"></textarea></td>
  </tr>
</table>
{/if}

<p class="center">
  <strong>Séparateur&nbsp;:</strong>
  <input type="text" name="separator" value=";" size="1" maxlength="1" /><br /><br />
  <input type='submit' value='Ajouter' />
</p>
</form>
{/if}

{* vim:set et sws=2 sts=2 sw=2 enc=utf-8: *}
