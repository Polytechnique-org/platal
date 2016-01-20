{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<tr>
  <td>
    {profile user=$user promo=false}
  </td>
  <td>
    {if $user->group_perms eq 'admin' && $user->category()}<strong>{/if}
    {$user->category()|default:"Extérieur"}
    {if $user->group_perms eq 'admin' && $user->category()}</strong>{/if}
  </td>
  <td>{if $user->group_comm}{$user->group_comm}{else}&nbsp;{/if}</td>
  <td class="right">
    {if $user->hasProfile()}
    <a href="https://www.polytechnique.org/vcard/{$user->login()}.vcf">{icon name=vcard title="[vcard]"}</a>
    {/if}
    <a href="mailto:{$user->bestEmail()}">{icon name=email title="email"}</a>
  </td>
  {if $is_admin && !t($groupmember_noadmin)}
  <td class="center">
    <a href="{$platal->ns}member/{$user->login()}">{icon name=user_edit title="Édition du profil"}</a>
    <a href="{$platal->ns}member/del/{$user->login()}">{icon name=delete title="Supprimer de l'annuaire"}</a>
  </td>
  {/if}
</tr>
