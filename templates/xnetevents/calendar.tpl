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
BEGIN:VCALENDAR
{display_ical name="prodid" value="-//Polytechnique.org//Plat-al//FR"}
VERSION:2.0
CALSCALE:GREGORIAN
X-WR-TIMEZONE:Europe/Paris
METHOD:PUBLISH
{display_ical name="x-wr-calname" value=$asso->nom}
BEGIN:VEVENT
DSTAMP:{$timestamp|date_format:"%Y%m%dT%H%M%SZ"}
DTSTART;VALUE=DATE;TZID=Europe/Paris:{$e.debut}
DTEND;VALUE=DATE;TZID=Europe/Paris:{$e.fin}
ORGANIZER;CN="{$e.prenom} {$e.nom}":MAILTO:{$e.alias}@polytechnique.org
UID:event-{$e.short_name}-{$e.eid}@{$asso->diminutif}.polytechnique.org
{if $admin}
{foreach from=$participants item=m}
ATTENDEE;CN="{$m.user->fullName('promo')}":MAILTO:{$m.user->bestEmail()}
{/foreach}
{/if}
{if $e.accept_nonmembre}
CLASS:PUBLIC
{else}
CLASS:PRIVATE
{/if}
{display_ical name="summary" value=$e.intitule}
{display_ical name="description" value=$e.descriptif}
END:VEVENT
END:VCALENDAR
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
