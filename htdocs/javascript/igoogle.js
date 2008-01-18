/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

function igOnLoadHandler()
{
    if (typeof(_IG_AdjustIFrameHeight) != 'undefined') {
        _IG_AdjustIFrameHeight();
    }
}

function markEventAsRead(event_id)
{
    _toggle(_gel("mark-read-" + event_id));
    _gel("evt-" + event_id).setAttribute("class", "read");
    _gel("link-" + event_id).setAttribute("href", "events/unread/" + event_id);
    Ajax.update_html(null, "events/read/" + event_id);
    return false;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
