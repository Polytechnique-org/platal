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

{if $plset_count eq 0}
<p class="erreur">
  Aucun des camarades concernés n'a de photographie sur sa fiche
</p>
{else}
<table cellpadding="0" cellspacing="2" style="width: 100%">
  {section name=trombi loop=$set start=0}
  {if $smarty.section.trombi.index % 3 == 1}
  <tr>
    <td class="center" style="vertical-align: middle">
      <a href="{$mainsiteurl}profile/{$set[trombi.index_prev].forlife}" class="popup2">
        <img src="photo/{$set[trombi.index_prev].forlife}" width="110" alt=" [ PHOTO ] " />
      </a>
      {if $trombi_with_admin && hasPerm('admin')}
      <a href="{$mainsiteurl}admin/trombino/{$set[trombi.index_prev].user_id}">{icon name=wrench title="[admin]"}</a>
      {/if}
    </td>
    <td class="center" style="vertical-align: middle">
      <a href="{$mainsiteurl}profile/{$set[trombi].forlife}" class="popup2">
        <img src="photo/{$set[trombi].forlife}" width="110" alt=" [ PHOTO ] " />
      </a>
      {if $trombi_with_admin && hasPerm('admin')}
      <a href="{$mainsiteurl}admin/trombino/{$set[trombi].user_id}">{icon name=wrench title="[admin]"}</a>
      {/if}
    </td>
    <td class="center" style="vertical-align: middle">
    {if $set[trombi.index_next]}
      <a href="{$mainsiteurl}profile/{$set[trombi.index_next].forlife}" class="popup2">
        <img src="photo/{$set[trombi.index_next].forlife}" width="110" alt=" [ PHOTO ] " />
      </a>
      {if $trombi_with_admin && hasPerm('admin')}
      <a href="{$mainsiteurl}admin/trombino/{$set[trombi.index_next].user_id}">{icon name=wrench title="[admin]"}</a>
      {/if}
    {/if}
    </td>
  </tr>
  <tr>
    <td class="center" style="vertical-align: bottom; padding-bottom: 15px">
      <a href="{$mainsiteurl}profile/{$set[trombi.index_prev].forlife}" class="popup2">
        {$set[trombi.index_prev].prenom} {$set[trombi.index_prev].nom}{if $trombi_with_promo} ({$set[trombi.index_prev].promo}){/if}
      </a>
    </td>
    <td class="center" style="vertical-align: bottom; padding-bottom: 15px">
      <a href="{$mainsiteurl}profile/{$set[trombi].forlife}" class="popup2">
        {$set[trombi].prenom} {$set[trombi].nom}{if $trombi_with_promo} ({$set[trombi].promo}){/if}
      </a>
    </td>
    <td class="center" style="vertical-align: bottom; padding-bottom: 15px">
    {if $set[trombi.index_next]}
      <a href="{$mainsiteurl}profile/{$set[trombi.index_next].forlife}" class="popup2">
        {$set[trombi.index_next].prenom} {$set[trombi.index_next].nom}{if $trombi_with_promo} ({$set[trombi.index_next].promo}){/if}
      </a>
    {/if}
    </td>
  </tr>
  {elseif ($smarty.section.trombi.index % 3 == 0) && ($smarty.section.trombi.last)}
  <tr>
    <td class="center" style="vertical-align: middle; padding-bottom: 15px">
      <a href="{$mainsiteurl}profile/{$set[trombi].forlife}" class="popup2">
        <img src="photo/{$set[trombi].forlife}" width="110" alt=" [ PHOTO ] " />
      </a>
      {if $trombi_with_admin && hasPerm('admin')}
      <a href="{$mainsiteurl}admin/trombino/{$set[trombi].user_id}">{icon name=wrench title="[admin]"}</a>
      {/if}
    </td>
    <td></td><td></td>
  </tr>
  <tr style="margin-top: 0; padding-top: 0">
    <td class="center" style="vertical-align: bottom">
      <a href="{$mainsiteurl}profile/{$set[trombi].forlife}" class="popup2">
        {$set[trombi].prenom} {$set[trombi].nom}{if $trombi_with_promo} ({$set[trombi].promo}){/if}
      </a>
    </td>
    <td></td><td></td>
  </tr>
  {/if}
  {/section}
</table>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
