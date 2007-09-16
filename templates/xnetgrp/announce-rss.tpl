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

<?xml version="1.0"?>
<rss version="2.0">
  <channel>
    <title>Polytechnique.net :: {$asso.nom} :: News</title>
    <language>fr</language>
    <link>{#globals.baseurl#}/{$asso.diminutif}/</link>
    <description>L'actualite polytechnicienne...</description>
    <image>
      <title>{#globals.core.sitename#}</title>
      <url>{#globals.baseurl#}/images/logo.png</url>
      <link>{#globals.baseurl#}/{$asso.diminutif}/</link>
    </image>
    {iterate item=line from=$rss}
    <item>
      <title>{$line.titre|strip_tags}</title>
      <guid isPermaLink="false">{$line.id}</guid>
      <link>{#globals.baseurl#}/{$asso.diminutif}/#art{$line.id}</link>
      <description><![CDATA[{$line.texte|miniwiki}{if $line.contacts}<br/><br/><strong>Contacts :</strong><br/>{$line.contacts|miniwiki}{/if}]]></description>
      <author>{$line.prenom} {$line.nom} (X{$line.promo})</author>
      <pubDate>{$line.create_date|rss_date}</pubDate>
    </item>
    {/iterate}
  </channel>
</rss>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
