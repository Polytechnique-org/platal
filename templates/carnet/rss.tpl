<?xml version="1.0"?>
{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
 ***************************************************************************}
<rss version="2.0">
  <channel>
    <title>Polytechnique.org :: Carnet</title>
    <language>fr</language>
    <link>{#globals.baseurl#}/carnet/panel.php</link>
    <description>Ton carnet polytechnicien...</description>
    <image>
      <title>Polytechnique.org</title>
      <url>{#globals.baseurl#}/images/logo.png</url>
      <link>{#globals.baseurl#}/</link>
    </image>
    {foreach from=$notifs->_data item=c key=cid}
    {foreach from=$c item=promo}
    {section name=row loop=$promo}
    <item>
      <title>
        [{$notifs->_cats[$cid].short}] {$promo[row].prenom} {$promo[row].nom} (le {$promo[row].date|date_format})
      </title>
      <link>{#globals.baseurl#}/fiche.php?user={$promo[row].bestalias}</link>
      <pubDate>{$promo[row]->known|rss_date}</pubDate>
    </item>
    {/section}
    {/foreach}
    {/foreach}
  </channel>
</rss>
{* vim:set et sw=2 sts=2 sws=2: *}
