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

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="Le Portail des Polytechniciens" />
    <meta name="keywords" content="Ecole polytechnique, anciens eleves, portail, alumni, AX, X, routage, reroutage, e-mail, email, mail" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="/images/favicon.png" type="image/png" />
    <link rel="index"  href="" />
    <link rel="author" href="changelog" />
    <link rel="search" href="search" />
    <link rel="search" type="application/opensearchdescription+xml" href="/xorg.opensearch.xml" title="Annuaire Polytechnique.org" />
    <link rel="help"   href="Xorg/FAQ" />
    <link rel="bookmark" href="http://www.polytechnique.fr/"        title="| École polytechnique" />
    <link rel="bookmark" href="http://www.polytechnique.edu/"       title="| Institutionnal site" />
    <link rel="bookmark" href="http://www.fondationx.org/"          title="| FX" />
    <link rel="bookmark" href="http://www.polytechniciens.com/"     title="| AX" />
    <link rel="bookmark" href="http://www.polytechnique.org/"       title="| Polytechnique.org" />
    <link rel="bookmark" href="http://www.polytechnique.fr/eleves/" title="| Site d'élèves" />

    <link rel="stylesheet" type="text/css" href="css/base.css" media="all"/>
    {foreach from=$xorg_css item=css}
    <link rel="stylesheet" type="text/css" href="css/{$css}" media="all"/>
    {/foreach}
    {foreach from=$xorg_inline_css item=css}
    <style type="text/css">
    {$css|smarty:nodefaults}
    </style>
    {/foreach}
    <link rel="stylesheet" type="text/css" href="css/print.css" media="print"/>
    {foreach from=$xorg_js item=js}
    <script type="text/javascript" src="javascript/{$js}"></script>
    {/foreach}
    {javascript name=overlib}
    {javascript name=md5}
    {javascript name=sha1}
    {javascript name=secure_hash}

    {if $xorg_rss}
    <link rel="alternate" type="application/rss+xml" title="{$xorg_rss.title}" href="{$xorg_rss.href}" />
    {/if}

    {if $xorg_extra_header}
    {$xorg_extra_header|smarty:nodefaults}
    {/if}

    <title>{$xorg_title|default:"Anciens eleves Polytechnique, annuaire email, Alumni"}</title>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
