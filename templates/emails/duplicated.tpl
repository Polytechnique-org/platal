{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>Gestion des adresses en doublon</h1>

{if $action eq "list"}
<table class="bicol">
  <tr>
    <th>Adresse</th>
    <th>Etat</th>
    <th>Utilisateurs</th>
    <th></th>
  </tr>
  <tr class="impair">
    <td colspan="2">
      <strong>Ajouter une entrée manuellement</strong>
    </td>
    <td colspan="2" class="right">
      <strong><a href="admin/emails/duplicated/create">créer{icon name=add}</a></strong>
    </td>
  </tr>
  {foreach from=$table item=doublon}
  <tr class="{cycle values="pair,impair"}">
    <td>
      <strong>{$doublon.mail}</strong><br />
      Détecté le {$doublon.detection|date_format}
    </td>
    <td>
      {$doublon.state}
    </td>
    <td class="right">
      {foreach from=$doublon.users item=user name=all}
      <a href="profile/{$user}" class="popup2">{$user}{icon name=user_suit title="Fiche"}</a>
      <a href="admin/user/{$user}">{icon name=wrench title="Administrer}</a>{if !$smarty.foreach.all.last}<br />{/if}
      {/foreach}
    </td>
    <td class="right">
      <a href="admin/emails/duplicated/edit/{$doublon.mail}">{icon name=page_edit title="Editer"}</a>
      <a href="admin/emails/duplicated/delete/{$doublon.mail}">{icon name=delete title="Supprimer"}</a>
    </td>
  </tr>
  {/foreach}
</table>
{elseif $action eq "create" || $action eq "edit"}
[<a href="admin/emails/duplicated">Retour à la liste des doublons</a>]<br /><br />
<form method="post" action="admin/emails/duplicated">
<table class="tinybicol">
  <tr>
    <th colspan="2">Commenter le doublon</th>
  </tr>
  <tr class="impair">
    <td class="titre">Adresse mail</td>
  {if $action eq "create"}
    <td><input type="text" name="emailN" /></td>
  {else}
    <td>
      <a href="mailto:{$doublon.mail}">{icon name=email title="Envoyer un mail"}</a>
      &nbsp;{$doublon.mail}
      <input type="hidden" name="emailN" value="{$doublon.mail}" />
    </td>
  </tr>
  {foreach from=$doublon.users key=i name=all item=user}
    {if $i is even}<tr class="impair">{/if}
    <td>
      <a href="profile/{$user}" class="popup2">{$user}{icon name=user_suit title="Fiche"}</a>
      <a href="admin/user/{$user}">{icon name=wrench title="Administrer}</a>{if !$smarty.foreach.all.last}<br />{/if}
    </td>
    {if $i is even && $smarty.foreach.all.last}<td></td>{/if}
    {if $i is odd || $smarty.foreach.all.last}</tr>{/if}
  {/foreach}
  <tr class="pair">
    <td class="titre">Date de détection</td>
    <td>{$doublon.detection|date_format}</td>
  {/if}
  </tr>
  <tr class="pair">
    <td class="titre">Danger</td>
    <td>
      <select name="stateN">
        {foreach from=$states key=state item=text}
        <option value="{$state}"{if $doublon.state eq $state} selected="selected"{/if}>{$text}</option>
        {/foreach}
      </select>
    </td>
  </tr>
  <tr class="impair">
    <td colspan="2" class="titre">Description</td>
  {if $doublon.edit}
  </tr>
  <tr class="impair">
    <td colspan="2">
      <small>Dernière édition par {$doublon.edit} le {$doublon.last|date_format}</small>
    </td>
  {/if}
  </tr>
  <tr class="impair">
    <td colspan="2" class="center">
      <textarea cols="50" rows="10" name="descriptionN">{$doublon.description}</textarea>
    </td>
  </tr>
  <tr>
    <th colspan="2">
      <input type="hidden" name="action" value="{$action}" />
      <input type="submit" name="valid"  value="Valider" />
    </th>
  </tr>
</table>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
