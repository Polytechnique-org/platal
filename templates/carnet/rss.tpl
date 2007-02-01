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
    <title>Polytechnique.org :: Carnet</title>
    <language>fr</language>
    <link>{#globals.baseurl#}/carnet/panel</link>
    <description>Ton carnet polytechnicien...</description>
    <image>
      <title>Polytechnique.org</title>
      <url>{#globals.baseurl#}/images/logo.png</url>
      <link>{#globals.baseurl#}/</link>
    </image>
    {foreach from=$notifs->_data item=c key=cid}
    {foreach from=$c item=promo}
    {foreach from=$promo item=x}
    <item>
      <title>
        [{$notifs->_cats[$cid].short}] {$x.prenom} {$x.nom} ({$x.promo}) - le {$x.date|date_format|utf8_encode}
      </title>
      <link>{#globals.baseurl#}/profile/private/{$x.bestalias}</link>
      <guid isPermaLink="false">carnet{$x.known}{$cid}{$x.bestalias}</guid>
      <description><![CDATA[
        {if !$x.contact and !$x.dcd}
        <a href="{#globals.baseurl#}/carnet/contacts?action=ajouter&amp;user={$x.bestalias}">
          {icon name=add title="Ajouter" full=true} Ajouter &agrave; mes contacts
        </a><br />
        {/if}
        {if !$x.dcd}
        <a href="{#globals.baseurl#}/vcard/{$x.bestalias}.vcf">
          {icon name=vcard title="Carte de visite" full=true} T&eacute;l&eacute;charger la carte de visite &eacute;lectronique
        </a>
        {/if}
        ]]></description>
      <author>{$x.prenom} {$x.nom} (X{$x.promo})</author>
      <pubDate>{$x.known|rss_date}</pubDate>
    </item>
    {/foreach}
    {/foreach}
    {/foreach}
  </channel>
</rss>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
