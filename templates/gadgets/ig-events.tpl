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

<div class="events">
  <ul>
  {iterate from=$events item=ev}
    <li class="{if $ev.nonlu}unread{else}read{/if}" id="evt-{$ev.id}">
      {if $ev.nonlu}
      <div  id="mark-read-{$ev.id}" style="float: right">
        <a href="events/read/{$ev.id}" target="_top" onclick="return markEventAsRead({$ev.id})">{*
          *}{icon name=tick title="Marquer comme lu"}</a>
      </div>
      {/if}
      <a href="events{if !$ev.nonlu}/unread/{$ev.id}{else}#newsid{$ev.id}{/if}" target="_blank" id="link-{$ev.id}"
         title="Ajouté le {$ev.creation_date|date_format} par {$ev.prenom} {$ev.nom} (X{$ev.promo})">
        {tidy}
          {$ev.titre|nl2br}
        {/tidy}
      </a>
    </li>
  {assign var="has_evts" value=true}
  {/iterate}
  {if !$has_evts}
    <li><em>Aucun article actuellement.</em></li>
  {/if}
  </ul>
</div>
<div class="more">
  <a href="events" target="_blank">{$event_count} événements au total</a> &gt;&gt;&gt;
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
