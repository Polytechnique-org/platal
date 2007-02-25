{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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



<h1>
  Gestion des événements :
  [&nbsp;
  {if $arch || $action eq 'edit'}
  <a href="admin/events">Actualités</a>
  {else}
  Actualités
  {/if}
  &nbsp;|&nbsp;
  {if !$arch || $action eq 'edit'}
  <a href="admin/events/archives">Archives</a>
  {else}
  Archives
  {/if}
  &nbsp;]
</h1>

{if $action eq 'edit'}

{include file="events/form.tpl"}

{else}

<table class="bicol">
  <tr>
    <th>Titre</th>
    <th>Péremption</th>
    <th></th>
  </tr>
  {iterate from=$evs item=ev}
  {cycle values="impair,pair" assign=class}
  <tr class="{$class}">
    <td>
      <a id="event{$ev.id}"></a>
      {if !$ev.fvalide}<strong>{/if}
      <a href="admin/events/preview/{$ev.id}#event{$ev.id}">{$ev.titre}</a><br />
      {if !$ev.fvalide}</strong>{/if}
      <small>
        Proposée par <a href="profile/{$ev.forlife}" class='popup2'>{$ev.prenom} {$ev.nom} (X{$ev.promo})</a>
      </small>
    </td>
    <td class="right">{if !$ev.fvalide}<strong>{/if}{$ev.peremption}{if !$ev.fvalide}</strong>{/if}</td>
    <td class="right" style="width: 42px">
      {if $arch}
        <a href="admin/events/unarchive/{$ev.id}">{icon name=package_delete title="Désarchiver"}</a><br />
      {else}
        {if $ev.fvalide}
        <a href="admin/events/unvalid/{$ev.id}">{icon name=thumb_down title="Invalider"}</a>
        <a href="admin/events/archive/{$ev.id}">{icon name=package_add title="Archiver"}</a><br />
        {else}
        <a href="admin/events/valid/{$ev.id}">{icon name=thumb_up title="Valider"}</a><br />
        {/if}
      {/if}
      <a href="admin/events/edit/{$ev.id}">{icon name=page_edit title="Editer"}</a>
      <a href="admin/events/delete/{$ev.id}">{icon name=delete title="Supprimer"}</a>
    </td>
  </tr>
  {if $ev.preview}
  <tr class="{$class}">
    <td colspan="3" style="border-top: 1px dotted #777">
      {$ev.texte|smarty:nodefaults|nl2br}
    </td>
  </tr>
  {/if}
  {/iterate}
</table>

{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
