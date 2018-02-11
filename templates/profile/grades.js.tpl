{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

subgrades = new Array();
names     = new Array();
multiple  = new Array();
{foreach from=$medal_list key=type item=list}
  {foreach from=$list item=m}
    names[{$m.id}] = "{$m.text|regex_replace:"/\r?\n/":"\\n"}";
    {if t($grades[$m.id]) && $grades[$m.id]|@count}
      subgrades[{$m.id}] = new Array({$grades[$m.id]|@count});
      {foreach from=$grades[$m.id] item=g name=subgrade}
        subgrades[{$m.id}][{$smarty.foreach.subgrade.index}] = [{$g.gid},"{$g.text|regex_replace:"/\r?\n/":"\\n"}"];
      {/foreach}
    {/if}
    {if $m.type != 'ordre'}
      multiple[{$m.id}] = true;
    {/if}
  {/foreach}
{/foreach}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
