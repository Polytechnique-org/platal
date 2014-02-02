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

{assign var=lostUsers value=false}
{foreach from=$list item=members key=sort_key}
{foreach from=$members item=member name=all}
  {assign var=user value=$member.user}
  {assign var=profile value=$member.profile}
  {assign var=email value=$member.email}

<tr>
  <td class='titre' style="width: 20%">
    {if $smarty.foreach.all.first}
    {if $sort_key neq 'AAAAA'}{$sort_key}{else}{$no_sort_key}{/if}
    {/if}
  </td>
  <td>
    {if t($profile)}
      {if $user->lost}{assign var=lostUsers value=true}{/if}
      {profile user=$user profile=$profile promo=$promo}
    {elseif t($user)}
      <a href="mailto:{$email}">{if $user->directoryName}{$user->directoryName}{else}{$email}{/if}{if not t($promo)} (extérieur){/if}</a>
    {else}{* Email without account or email *}
      <a href="mailto:{$email}">{$email}</a>
    {/if}
  </td>
  {if t($delete)}
  <td class="center">
    {if t($member.user)}
    <a href="{$platal->ns}member/{$member.user->uid}">{icon name=user_edit title='Éditer'}</a>&nbsp;
    {else}
    {icon name=null}&nbsp;
    {/if}
    <a href='{$platal->pl_self(1)}?{$delete}={$member.email}&amp;token={xsrf_token}'>{icon name=cross title='Retirer'}</a>
  </td>
  {/if}
</tr>
{/foreach}
{/foreach}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
