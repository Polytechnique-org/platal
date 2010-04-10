{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

{if $profile}
{if $photo}
<td class="center" style="vertical-align: middle">
  <a href="profile/{$profile->hrid()}" class="popup2">
    <img src="photo/{$profile->hrid()}" width="110" alt=" [ PHOTO ] " />
  </a>
  {if $trombi_with_admin && hasPerm('admin')}
  <a href="{$mainsiteurl}admin/trombino/{$profile->id()}">{icon name=wrench title="[admin]"}</a>
  {/if}
</td>
{else}
<td class="center" style="vertical-align: bottom; padding-bottom: 15px">
  <a href="profile/{$profile->hrid()}" class="popup2">
    <span {if $profile->name_tooltip}class="hinted"
    title="{$profile->directory_name}"{/if}>{$profile->directory_name}</span> 
    {if $trombi_with_promo && $profile->promo()}({$profile->promo()}){/if}
  </a>
</td>
{/if}
{/if}
