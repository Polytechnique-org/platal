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

{capture name=pages}
{if $plview->pages > 1}
<div class="center pages">
  {if $plview->page neq 1}
  <a href="{$platal->pl_self()}?page=1">&lt;&lt;</a>
  <a href="{$platal->pl_self()}?page={$plview->page-1}">&lt;</a>
  {/if}
  {section name=page loop=$plview->pages+1 start=1}
  {if $smarty.section.page.index eq $plview->page}
  <span style="color: red">{$plview->page}</span> 
  {else}
  <a href="{$platal->pl_self()}?page={$smarty.section.page.index}">{$smarty.section.page.index}</a>
  {/if}
  {/section}
  {if $plview->page neq $plview->pages}
  <a href="{$platal->pl_self()}?page={$plview->page+1}">&gt;</a>
  <a href="{$platal->pl_self()}?page={$plview->pages}">&gt;&gt;</a>
  {/if}
</div>
{/if}
{/capture}

{$smarty.capture.pages|smarty:nodefaults}

<div id="multipage_content" style="padding: 0.5em 0">
  {include file=$plview->templateName()}
</div>

{$smarty.capture.pages|smarty:nodefaults}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
