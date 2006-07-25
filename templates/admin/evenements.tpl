{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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
  {if $arch eq 'archives'}
  [&nbsp;<a href="admin/events/actu">Actualités</a>&nbsp;|&nbsp;Archives&nbsp;]
  {else}
  [&nbsp;Actualités&nbsp;|&nbsp;<a href="admin/events/archives">Archives</a>&nbsp;]
  {/if}
</h1>

{if $mode}

{include file="include/form.evenement.tpl"}

{else}

{iterate from=$evs item=ev}
<table class="bicol">
  <tr>
    <th>
      Posté par <a href="profile/{$ev.forlife}" class='popup2'>{$ev.prenom} {$ev.nom} (X{$ev.promo})</a>
    </th>
  </tr>
  <tr class="{if $ev.fvalide}impair{else}pair{/if}">
    <td>
      <strong>{$ev.titre}</strong>
    </td>
  </tr>
  <tr class="{if $ev.fvalide}impair{else}pair{/if}">
    <td>
      {tidy}{$ev.texte|nl2br}{/tidy}
    </td>
  </tr>
  <tr class="{if $ev.fvalide}impair{else}pair{/if}">
    <td>
      Création : {$ev.creation_date}<br />
      Péremption : {$ev.peremption}<br />
      Promos : {$ev.promo_min} - {$ev.promo_max}<br />
    </td>
  </tr>
  <tr>
    <th>
      <form action="admin/events/{if $ev.arch}archives{else}actu{/if}" method="post">
        <div>
          <input type="hidden" name="evt_id" value="{$ev.id}" />
          {if $ev.farch}
          <input type="submit" name="action" value="Desarchiver" />
          {else}
          <input type="submit" name="action" value="Editer" />
          {if $ev.fvalide}
          <input type="submit" name="action" value="Invalider" />
          <input type="submit" name="action" value="Archiver" />
          {else}
          <input type="submit" name="action" value="Valider" />
          {/if}
          <input type="submit" name="action" value="Supprimer" />
          {/if}
        </div>
      </form>
    </th>
  </tr>
</table>

<br />
{/iterate}

{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
