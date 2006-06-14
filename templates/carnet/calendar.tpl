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
BEGIN:VCALENDAR
{display_ical name="prodid" value="-//Polytechnique.org//Plat-al//FR"}
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
{display_ical name="x-wr-calname" value="Anniversaires des X"}
X-WR-TIMEZONE:Europe/Paris
{foreach from=$events item=e}
BEGIN:VEVENT
DTSTAMP:{$e.timestamp|date_format:"%Y%m%dT%H%M%SZ"}
DTSTART;VALUE=DATE:{$e.date|date_format:"%Y%m%d"}
DTEND;VALUE=DATE:{$e.tomorrow|date_format:"%Y%m%d"}
UID:anniv-{$e.date|date_format:"%Y%m%d"}-{$e.bestalias}@polytechnique.org
CLASS:PUBLIC
{display_ical name="summary" value=$e.summary}
END:VEVENT
{/foreach}
END:VCALENDAR