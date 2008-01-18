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

{include file="skin/common.doctype.tpl"}
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="css/base.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="css/igoogle.css" media="all"/>
    <script type="text/javascript" src="javascript/ajax.js"></script>
    <script type="text/javascript" src="javascript/base.js"></script>
    <script type="text/javascript" src="javascript/igoogle.js"></script>
    <script type="text/javascript" src="javascript/xorg.js"></script>
    {foreach from=$gadget_js item=js}
    <script type="text/javascript" src="{$js}"></script>
    {/foreach}
    <script type="text/javascript">var platal_baseurl = "{$globals->baseurl}/";</script>
  </head>
  <body onload="igOnLoadHandler();">
{if $gadget_tpl}{include file=$gadget_tpl}{/if}
  </body>
</html>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
