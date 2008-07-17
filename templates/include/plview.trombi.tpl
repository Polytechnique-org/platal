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
<table cellpadding="8" cellspacing="2" style="width: 100%">
  {foreach from=$set item=p}
  {cycle values="1,2,3" assign="loop"}
  {if $loop eq "1"}
  <tr>
  {/if}
    <td class="center" style="vertical-align: bottom">
      <a href="{$mainsiteurl}profile/{$p.forlife}" class="popup2">
        <img src="photo/{$p.forlife}" width="110" alt=" [ PHOTO ] " />
      </a>
      {if $trombi_with_admin && hasPerm('admin')}
      <a href="{$mainsiteurl}admin/trombino/{$p.user_id}">{icon name=wrench title="[admin]"}</a>
      {/if}
      <br />
      <a href="{$mainsiteurl}profile/{$p.forlife}" class="popup2">
        {$p.prenom} {$p.nom}{if $trombi_with_promo} ({$p.promo}){/if}
      </a>
    </td>
  {if $loop eq "3"}
  </tr>
  {/if}
  {/foreach}
  {if $loop neq "3"}
  {if $lopp eq "1"}
  <td></td>
  {/if}
  <td></td></tr>
  {/if}
</table>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
