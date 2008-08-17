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

<?xml version="1.0"?>
<rss version="2.0">
  <channel>
    <title>{$feed->title}</title>
    <language>fr</language>
    <link>{$feed->link}</link>
    <description>{$feed->description}</description>
    <image>
      <title>{$feed->title}</title>
      <url>{$feed->img_link}</url>
      <link>{$feed->link}</link>
    </image>
    {iterate item=article from=$feed}
    <item>
      <title>{$article->title|strip_tags|htmlentities}</title>
      <guid isPermaLink="false">{$article->id}</guid>
      <link>{$article->link}</link>
      <description><![CDATA[{include file=$article->template article=$article}]]></description>
      <author>{$article->author}</author>
      <pubDate>{$article->publication|rss_date}</pubDate>
    </item>
    {/iterate}
  </channel>
</rss>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
