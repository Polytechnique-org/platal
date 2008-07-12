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

{capture name=pages}
{if $plview->pages > 1}
<div class="center pages">
  {if $show_bounds}<small><strong>[{$first} - {$last}]&nbsp;</strong></small>{/if}
  {if $plview->page neq 1}
  <a href="{$platal->pl_self()}{$plset_search}order={$order}&amp;page=1">{icon name=resultset_first title="Première page"}</a>{*
  *}<a href="{$platal->pl_self()}{$plset_search}order={$order}&amp;page={$plview->page-1}">{icon name=resultset_previous title="Page précédente"}</a>
  {else}
  {icon name=null title=""}{icon name=null title=""}
  {/if}
  {section name=page loop=$plview->pages+1 start=1}
  {if $smarty.section.page.index eq $plview->page}
  <span style="color: red">{$plview->page}</span> 
  {else}
  <a href="{$platal->pl_self()}{$plset_search}order={$order}&amp;page={$smarty.section.page.index}">{$smarty.section.page.index}</a>
  {/if}
  {/section}
  {if $plview->page neq $plview->pages}
  <a href="{$platal->pl_self()}{$plset_search}order={$order}&amp;page={$plview->page+1}">{icon name=resultset_next title="Page suivante"}</a>{*
  *}<a href="{$platal->pl_self()}{$plset_search}order={$order}&amp;page={$plview->pages}">{icon name=resultset_last title="Dernière page"}</a>
  {else}
  {icon name=null title=""}{icon name=null title=""}
  {/if}
</div>
{/if}
{/capture}

{capture name=order}
{if $plset_count > 1}
<div>
  Trier par&nbsp;:
  {foreach from=$orders key=name item=sort}
  [
  {if $name eq $order}
  <img src='images/dn.png' alt='tri ascendant' />
  <a href="{$platal->pl_self()}{$plset_search}order=-{$name}">{$sort.desc}</a>
  {elseif $order eq "-$name"}
  <img src='images/up.png' alt='tri ascendant' />
  <a href="{$platal->pl_self()}{$plset_search}order={$name}">{$sort.desc}</a>
  {else}
  <a href="{$platal->pl_self()}{$plset_search}order={$name}">{$sort.desc}</a>
  {/if}
  ]&nbsp;
  {/foreach}
</div>
{/if}
{/capture}


{$smarty.capture.pages|smarty:nodefaults}

{$smarty.capture.order|smarty:nodefaults}

<div id="multipage_content" style="padding: 0.5em 0">
  {include file=$plview->templateName()}
</div>

{$smarty.capture.order|smarty:nodefaults}

{$smarty.capture.pages|smarty:nodefaults}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
