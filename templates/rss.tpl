<?xml version="1.0" encoding="ISO-8859-1"?>
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

<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"
 "http://my.netscape.com/publish/formats/rss-0.91.dtd">

<rss version="0.91">

<channel>
<title>Polytechnique.org :: News</title>
<link>http://{$smarty.server.SERVER_NAME}/</link>
<description>L'actualité polytechnicienne...{if $promo} Promotion {$promo}{/if}</description>
<language>fr</language>

{foreach item=line from=$rss}
<item>
<title>{$line.titre|strip_tags}</title>
<link>http://{$smarty.server.SERVER_NAME}/login.php#newsid{$line.id}</link>
</item>
{/foreach}

</channel>
</rss>
{* vim:set et sw=2 sts=2 sws=2: *}
