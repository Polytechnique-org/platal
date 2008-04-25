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

<h1>Gestion des IPs surveillées</h1>

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
      <strong>Ajouter une entrée</strong>
    </td>
    <td colspan="2" class="right">
      <strong><a href="admin/ipwatch/create">créer{icon name=add}</a></strong>
    </td>
  </tr>
  {foreach from=$table item=ip}
  <tr class="{cycle values="pair,impair"}">
    <td>
      <strong>{$ip.ip}/{$ip.mask}</strong><br />
      <small>{$ip.host}</small><br />
      Ajoutée le {$ip.detection|date_format}
    </td>
    <td>
      {$ip.state}
    </td>
    <td class="right">
      {foreach from=$ip.users item=user name=all}
      {if $user}
      <a href="profile/{$user}" class="popup2">{$user}</a>
      <a href="admin/user/{$user}">{icon name=wrench title=Administrer}</a>
      <a href="admin/logger/user/{$user}">{icon name=information title="Logs"}</a>{if !$smarty.foreach.all.last}<br />{/if}
      {/if}
      {/foreach}
    </td>
    <td class="right">
      <a href="admin/ipwatch/edit/{$ip.ip}">{icon name=page_edit title="Editer"}</a>
      <a href="admin/ipwatch/delete/{$ip.ip}?token={xsrf_token}">{icon name=delete title="Supprimer"}</a>
    </td>
  </tr>
  {/foreach}
</table>
{elseif $action eq "create" || $action eq "edit"}
[<a href="admin/ipwatch">Retour à la liste des IPs surveillées</a>]<br /><br />
<form method="post" action="admin/ipwatch">
{xsrf_token_field}
<table class="tinybicol">
  <tr>
    <th colspan="2">Commenter une adresse IP</th>
  </tr>
  <tr class="impair">
  {if $action eq "create"}
    <td class="titre">Adresse IP</td>
    <td><input type="text" name="ipN" /></td>
  {else}
    <td colspan="2">
      <strong>{$ip.ip}</strong> ({$ip.host})
      <input type="hidden" name="ipN" value="{$ip.ip}" />
    </td>
  </tr>
  {foreach from=$ip.users key=i name=all item=user}
    {if $user}
    {if $i is even}<tr class="impair">{/if}
    <td>
      <a href="profile/{$user}" class="popup2">{$user}</a>
      <a href="admin/user/{$user}">{icon name=wrench title="Administrer}</a>
      <a href="admin/logger/user/{$user}">{icon name=information title="Logs"}</a>{if !$smarty.foreach.all.last}<br />{/if}
    </td>
    {if $i is even && $smarty.foreach.all.last}<td></td>{/if}
    {if $i is odd || $smarty.foreach.all.last}</tr>{/if}
    {/if}
  {/foreach}
  <tr class="pair">
    <td class="titre">Date de détection</td>
    <td>{$ip.detection|date_format}</td>
  {/if}
  </tr>
  <tr class="pair">
    <td class="titre">Masque d'influence</td>
    <td><input type="text" name="maskN" value="{$ip.mask}" /></td>
  </tr>
  <tr class="pair">
    <td class="titre">Danger</td>
    <td>
      <select name="stateN">
        {foreach from=$states key=state item=text}
        <option value="{$state}"{if $ip.state eq $state} selected="selected"{/if}>{$text}</option>
        {/foreach}
      </select>
    </td>
  </tr>
  <tr class="impair">
    <td colspan="2" class="titre">Description</td>
  {if $ip.edit}
  </tr>
  <tr class="impair">
    <td colspan="2">
      <small>Dernière édition par {$ip.edit} le {$ip.last|date_format}</small>
    </td>
  {/if}
  </tr>
  <tr class="impair">
    <td colspan="2" class="center">
      <textarea cols="50" rows="10" name="descriptionN">{$ip.description}</textarea>
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

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
