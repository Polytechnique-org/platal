{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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

{if t($pl_css)}
{foreach from=$pl_css item=css}
<link rel="stylesheet" type="text/css" href="css/{$css}" media="all"/>
{/foreach}
{/if}
{if t($pl_inline_css)}
{foreach from=$pl_inline_css item=css}
<style type="text/css">
{$css|smarty:nodefaults}
</style>
{/foreach}
{/if}
{if t($pl_link)}
{foreach from=$pl_link item=link}
<link rel="{$link.rel}" href="{$link.href}" />
{/foreach}
{/if}
<script type="text/javascript">
  var platal_baseurl = "{$globals->baseurl}/";
</script>
{if t($pl_js)}
{foreach from=$pl_js item=js}
<script type="text/javascript" src="{$js}"></script>
{/foreach}
{/if}
{if t($pl_rss)}
<link rel="alternate" type="application/rss+xml" title="{$pl_rss.title}" href="{$pl_rss.href}" />
{/if}
{if t($pl_extra_header)}
{$pl_extra_header|smarty:nodefaults}
{/if}
{if t($pl_title)}
<title>{$pl_title}</title>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
