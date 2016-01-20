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

<table class="tinybicol" id="art{$art.id}">
  <tr>
    <th {if $art.photo}colspan="2"{/if}>
      {if $is_logged && !$admin}
      <div style="float: right">
        {if $is_admin}
          <a href="{$platal->ns}announce/edit/{$art.id}">{icon name=page_edit title="Editer cet article"}</a>
        {/if}
        <a href="{$platal->ns}?read={$art.id}">{icon name=cross title="Cacher cet article"}</a>
      </div>
      {/if}
      {tidy}
      {if $admin}Aperçu de&nbsp;: {/if}{$art.titre}
      {/tidy}
    </th>
  </tr>
  <tr>
    {if $art.photo}
    <td rowspan="{if ($is_logged || $admin) && $art.contacts}3{else}2{/if}" style="width: 100px">
      <img src="{$platal->ns}announce/photo/{$art.id}" alt="{$art.titre}" style="width: 100px" />
    </td>
    {/if}
    <td style="padding-bottom: 1em">
      {$art.texte|miniwiki|smarty:nodefaults}
    </td>
  </tr>
  {if ($is_logged || $admin) && $art.contacts}
  <tr class="pair">
    <td class="titre">Contacts&nbsp;:</td>
  </tr>
  <tr class="pair">
    <td style="padding-left: 20px">
      {if $art.contact_html}
      {tidy}
      {$art.contact_html|nl2br|smarty:nodefaults}
      {/tidy}
      {else}
      {$art.contacts|miniwiki|smarty:nodefaults}
      {/if}
    </td>
  </tr>
  {/if}
  <tr class="pair">
    <td {if $art.photo}colspan="2"{/if}>
      <div style="float: right">
      <small>
        Annonce proposée par {profile user=$art.uid sex=false promo=true groupperms=false}
      </small>
      </div>
      <small>
      {if $art.post_id}
      <a href="{$platal->ns}forum/read/{$art.post_id}">{icon name=comments title="Discussions"}Discuter</a>
      {/if}
      </small>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
