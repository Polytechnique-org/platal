{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

<h1>Gestion des homonymes</h1>

{if $op eq 'list' || $op eq 'mail' || $op eq 'correct'}

{if $homonyms_to_fix|@count}
<p>
  Liste des homonymies à corriger, celles en rouge devraient déjà être traitées.<br />

  Dans un premier temps, envoyer un mail. Ensuite (typiquement une semaine plus tard), cliquer sur "corriger".
</p>

<table class="bicol">
  <tr>
    <th>alias concerné</th>
    <th>date de péremption de l'alias</th>
    <th>actions</th>
  </tr>
  {foreach from=$homonyms_to_fix key=login item=row}
  <tr class="pair">
    <td>
      {if $row.0.urgent}
      <span class="erreur"><strong>{$login}</strong></span>
      {else}
      <strong>{$login}</strong>
      {/if}
    </td>
    <td>{$row.0.expire|date_format}</td>
    <td>
      <a href="admin/homonyms/mail-conf/{$row.0.uid}">envoyer un email</a>
      <a href="admin/homonyms/correct-conf/{$row.0.uid}">corriger</a>
    </td>
  </tr>
  {foreach from=$row item=user}
  <tr class="impair">
    <td>&nbsp;&nbsp;{$user.forlife}</td>
    <td></td>
    <td>
      <a href="profile/{$user.forlife}" class='popup2'>fiche</a>
      <a href="admin/user/{$user.forlife}">edit</a>
    </td>
  </tr>
  {/foreach}
  {/foreach}
</table>
{/if}

<p>
  Liste des homonymies déjà corrigées.
</p>

<table class="bicol">
  <tr>
    <th>alias concerné</th>
    <th>alias prémimé depuis</th>
    <th>actions</th>
  </tr>
  {foreach from=$homonyms key=login item=row}
  <tr class="pair">
    <td><strong>{$login}</strong></td>
    <td>{if $row.0.expire eq '0000-00-00'}---{else}{$row.0.expire|date_format}{/if}</td>
    <td></td>
  </tr>
  {foreach from=$row item=user}
  <tr class="impair">
    <td>&nbsp;&nbsp;{$user.forlife}</td>
    <td></td>
    <td>
      <a href="profile/{$user.forlife}" class='popup2'>fiche</a>
      <a href="admin/user/{$user.forlife}">edit</a>
    </td>
  </tr>
  {/foreach}
  {/foreach}
</table>

{elseif $op eq 'mail-conf'}

<form method="post" action="admin/homonyms/mail/{$target}">
  {xsrf_token_field}
  <table class="bicol">
    <tr>
      <th>Envoyer un email pour prévenir l'utilisateur</th>
    </tr>
    <tr>
      <td>
        <textarea cols="80" rows="20" name="mailbody">{$warning_mail_text}</textarea>
      </td>
    </tr>
    <tr>
      <td>
        <input type="submit" value="Envoyer" />
      </td>
    </tr>
  </table>
</form>

{elseif $op eq 'correct-conf'}

<form method="post" action="admin/homonyms/correct/{$target}">
  {xsrf_token_field}
  <table class="bicol">
    <tr>
      <th>Mettre en place le robot {$loginbis}@{$user->mainEmailDomain()}</th>
    </tr>
    <tr>
      <td>
        <textarea cols="80" rows="20" name="mailbody">{$robot_mail_text}</textarea>
      </td>
    </tr>
    <tr>
      <td>
        <input type="submit" value="Envoyer et corriger" />
      </td>
    </tr>
  </table>
</form>

{/if}


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
