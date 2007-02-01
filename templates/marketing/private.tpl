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

<h1>Marketing de {$prenom} {$nom}</h1>

<h2>Matricules</h2>

Matricule polytechnique.org : {$matricule}
{if $matricule_X}
<br />
Matricule &Eacute;cole (à rentrer lors de l'inscription) : <strong>{$matricule_X}</strong>
{/if}

{if $pending}

<h2>Inscription en cours</h2>

<p>
Cet utilisateur a une inscription en cours depuis le {$pending|date_format}.
</p>
<p>
{if $relance eq '0000-00-00'}
il n'a jamais été relancé.
{else}
sa dernière relance date du {$relance|date_format}
{/if}
</p>

<p>[<a href='{$path}/insrel'>le relancer</a>]</p>

{/if}

<h2>Adresses connues</h2>

<form action="{$path}/add" method="post">
  <table class="bicol" cellpadding="0" cellspacing="0">
    <tr>
      <th>Adresse</th>
      <th>Marketeur</th>
      <th>Date</th>
      <th>Envois</th>
      <th>Nb.</th>
      <th>&nbsp;</th>
    </tr>
    {iterate from=$addr item=a}
    <tr class="{cycle values='impair,pair'}">
      <td>{$a.email}</td>
      <td><a href="profile/{$a.alias}" class="popup2">{$a.alias}</a> {if $a.type eq user}(*){/if}</td>
      <td>{$a.date|date_format|default:'-'}</td>
      <td>{$a.last|date_format|default:'-'}</td>
      <td class='center'>{$a.nb|default:"-"}</td>
      <td class='action'>
        <a href='{$path}/del/{$a.email}'>del</a><br />
        <a href='{$path}/rel/{$a.email}'>relance</a>
      </td>
    </tr>
    {/iterate}
    <tr>
      <td></td>
      <td colspan='5' class='smaller'>(*): mail perso</td>
    </tr>
    <tr>
      <td>
        <input type='text' name='email' />
      </td>
      <td colspan="4">
        <select name="type">
          <option value="staff">staff</option>
          <option value="user">user</option>
        </select>
      </td>
      <td class='action'>
        <input type='submit' value='ajouter' />
      </td>
    </tr>
  </table>
</form>

{if $rel_to}
<form action="{$path}/relforce/{$email}" method="post">
  <table class="bicol">
    <tr class="pair">
      <th colspan="2">Edition du mail de relance</th>
    </tr>
    <tr class="pair">
      <td align="right"><strong>From:</strong></td>
      <td><select name="from">
        <option value="staff">{$rel_from_staff}</option>
        <option value="user">{$rel_from_user}</option>
      </select></td>
    </tr>
    <tr class="pair">
      <td align="right"><strong>To:</strong></td>
      <td>
        <input type="text" value="{$rel_to}" name="to" size="40" maxlength="100" />
        <input type="submit" name="valider" value="Envoyer" />
      </td>
    </tr>
    <tr class="pair">
      <td align="right"><strong>Objet:</strong></td>
      <td><input type="text" name="title" value="{$rel_title}" size="50" maxlength="100" /></td>
    </tr>
    <tr class="pair">
      <td align="right"><strong>Message:</strong></td>
      <td>
        <textarea name="message" rows="40" cols="60">{$rel_text}</textarea>
      </td>
    </tr>
  </table>
  <div class="center">
    <input type="reset" value="Recommencer" />
    <input type="submit" name="valider" value="Envoyer" />
  </div>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
