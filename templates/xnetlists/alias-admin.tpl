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

<p>[<a href='{$platal->ns}lists'>retour à la page des listes</a>]</p>

<h1>Membres de {$platal->argv[1]}</h1>
<table class='tinybicol'>
  {if $members.users|@count}
  {foreach from=$members.users item=member}
  <tr>
    <td>
      {if $member->hasProfile()}
      <a href="https://www.polytechnique.org/profile/{$member->hruid}" class="popup2">{$member->fullName()}</a>
      {else}
      {$member->fullName()}
      {/if}
    </td>
    <td class="right">{$member->promo()}</td>
    <td class="center">
      <a href='{$platal->ns}alias/admin/{$platal->argv[1]}?del_member={$member->id()}&amp;token={xsrf_token}'>
      {icon name=delete title='retirer membre'}
      </a>
    </td>
  </tr>
  {/foreach}
  {/if}
  {if $members.nonusers|@count}
  {foreach from=$members.nonusers item=member}
  <tr>
    <td>{$member}</td>
    <td></td>
    <td class="center">
      <a href='{$platal->ns}alias/admin/{$platal->argv[1]}?del_member={$member}&amp;token={xsrf_token}'>
      {icon name=delete title='retirer membre'}
      </a>
    </td>
  </tr>
  {/foreach}
  {/if}
  {if $members.users|@count eq 0 && $members.nonusers|@count eq 0}
  <tr>
    <td colspan="3">
      <em>aucun membre&hellip;</em>
    </td>
  </tr>
  {/if}
  <tr>
    <th colspan="3">Ajouter</th>
  </tr>
  <tr>
    <td colspan="3" class="center">
      <form method="post" action="{$platal->ns}alias/admin/{$platal->argv[1]}">
        {xsrf_token_field}
        <div>
        <input type='text' name='add_member' />
        &nbsp;
        <input type='submit' value='ajouter' />
        </div>
      </form>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
