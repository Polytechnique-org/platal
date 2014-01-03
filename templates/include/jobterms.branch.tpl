{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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
[
{assign var=started value=0}
{iterate from=$subTerms item=term}
  {if $started},{/if}
  {assign var=started value=1}
  {ldelim}
    "data" :
    {ldelim}
      "title" : "{$term.name|replace:'"':'\\"'}{if t($filter)} ({$term.nb} {$filter}{if $term.nb > 1}s{/if}){/if}",
      "attr" : {ldelim}
        {if !$jtid}"href" : "javascript:void(0)",
        {elseif $attrfunc}"href" : "javascript:{$attrfunc}('{$treeid}','{$term.jtid}',\"{$term.full_name|replace:'"':'\\\\\\"'}\")",{/if}
        "title" : "{$term.full_name|replace:'"':'\\"'}"
      {rdelim}
    {rdelim},
    "attr" : {ldelim} "id" : "job_terms_tree_{$treeid}_{$term.jtid}" {rdelim}
    {if !$term.leaf}
      ,"state": "closed"
    {/if}
  {rdelim}
{/iterate}
]

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
