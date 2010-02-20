#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

require('./connect.db.inc.php');

function copyTable($source, $target)
{
    XDB::execute('CREATE TABLE  ' . $target . '
                          LIKE  ' . $source);
    XDB::execute('INSERT INTO  ' . $target . '
                       SELECT  *
                         FROM  ' . $source);
}

copyTable('#forums#.list', 'forums');
copyTable('#forums#.abos', 'forum_subs');
copyTable('#forums#.innd', 'forum_innd');
copyTable('#forums#.profils', 'forum_profiles');

copyTable('#logger#.actions', 'log_actions');
copyTable('#logger#.events', 'log_events');
copyTable('#logger#.last_sessions', 'log_last_sessions');
copyTable('#logger#.sessions', 'log_sessions');

copyTable('#paiement#.paiements', 'payments');
copyTable('#paiement#.codeC', 'payment_codeC');
copyTable('#paiement#.codeRCB', 'payment_codeRCB');
copyTable('#paiement#.methodes', 'payment_methods');
copyTable('#paiement#.transactions', 'payment_transactions');

copyTable('#groupex#.announces', 'group_announces');
copyTable('#groupex#.announces_photo', 'group_announces_photo');
copyTable('#groupex#.announces_read', 'group_announces_read');
copyTable('#groupex#.asso', 'groups');
copyTable('#groupex#.dom', 'group_dom');
copyTable('#groupex#.evenements', 'group_events');
copyTable('#groupex#.evenements_items', 'group_event_items');
copyTable('#groupex#.evenements_participants', 'group_event_participants');
copyTable('#groupex#.membres', 'group_members');
copyTable('#groupex#.membres_sub_requests', 'group_member_sub_requests');
copyTable('#x4dat#.groupesx_auth'), 'group_auth');

copyTable('#x4dat#.photo', 'profile_photos');

copyTable('#x4dat#.search_autocomplete', 'search_autocomplete');
copyTable('#x4dat#.register_marketing', 'register_marketing');
copyTable('#x4dat#.watch_profile', 'watch_profile');

// Should be renamed to geoloc_country
copyTable('#x4dat#.geoloc_pays', 'geoloc_pays');

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
