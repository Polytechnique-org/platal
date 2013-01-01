{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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


<table summary="abonnés à la liste" class="bicol" cellpadding="0" cellspacing="0">
  {if $details.own || hasPerms('admin,groupadmin')}
  <tr><td colspan="2">
    {include file="include/csv.tpl" url="`$platal->ns`lists/csv/`$platal->argv[1]`/`$platal->argv[1]`.csv"}
  </td></tr>
  {/if}

  {assign var=current_key value=''}
  {foreach from=$set item=obj}
    {assign var=user value=$obj|get_user}
    <tr>
      <td class="titre" style="width: 20%">
        {if $order eq 'promo'}
          {assign var=user_key value=$user->promo()}
        {elseif $order eq 'name'}
          {assign var=user_key value=$user->lastName()|string_format:'%.1s'|upper}
        {else}
          {assign var=user_key value=''}
        {/if}
        {if $user_key neq $current_key}
          {assign var=current_key value=$user_key}
          {$user_key}
        {/if}
      </td>
      <td>
        {if $user->hasProfile()}
          {if $user->lost}{assign var=lostUsers value=true}{/if}
          {profile user=$user}
        {else}
          {$user->displayName()}
        {/if}
      </td>
      {if t($delete)}
        <td class="center">
          {if t($user.uid)}
            <a href="{$platal->ns}member/{$user.uid}">{icon name=user_edit title='Éditer'}</a>&nbsp;
          {else}
            {icon name=null}&nbsp;
          {/if}
          <a href='{$platal->pl_self(1)}?{$delete}={$user.email}&amp;token={xsrf_token}'>{icon name=cross title='Retirer'}</a>
        </td>
      {/if}
    </tr>
  {/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
