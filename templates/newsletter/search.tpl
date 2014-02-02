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

{if $nl_search}
<h1>
  {$nl->name}
</h1>
{/if}

<h2>Rechercher dans les archives</h2>

<form action="{$nl->prefix()}/search" method="post">
  {xsrf_token_field}
  <p>
  <select name="nl_search_type">
    <optgroup label="Recherche gobale">
      <option value="1" {if $nl_search_type eq 1}selected="selected"{/if}>N'importe où dans les lettres</option>
    </optgroup>
    <optgroup label="Recherche dans les articles">
      <option value="2" {if $nl_search_type eq 2}selected="selected"{/if}>N'importe où dans les articles</option>
      <option value="3" {if $nl_search_type eq 3}selected="selected"{/if}>Dans les titres des articles</option>
      <option value="4" {if $nl_search_type eq 4}selected="selected"{/if}>Dans les corps des articles</option>
      <option value="5" {if $nl_search_type eq 5}selected="selected"{/if}>Dans les appendices des articles</option>
    </optgroup>
    <optgroup label="Recherche dans le reste des lettres">
      <option value="6" {if $nl_search_type eq 6}selected="selected"{/if}>N'importe où dans le reste des lettres</option>
      <option value="7" {if $nl_search_type eq 7}selected="selected"{/if}>Dans les titres des emails/lettres</option>
      <option value="8" {if $nl_search_type eq 8}selected="selected"{/if}>Dans les chapeaux/contenus des lettres</option>
      <option value="9" {if $nl_search_type eq 9}selected="selected"{/if}>Dans les signatures des lettres</option>
    </optgroup>
  </select>
  <input type="text" name="nl_search" value="{$nl_search}" />
  </p>
  <p class="center"><input type="submit" value="Chercher" /></p>
</form>

{if $nl_search}
<h2>{$results_count} résultat{if $results_count > 1}s{/if} pour cette recherche</h2>

{if t($res_articles) && $res_articles|@count}
{$articles_count} article{if $articles_count > 1}s{/if} correspondant{if $articles_count > 1}s{/if}&nbsp;:
<ul>
  {foreach from=$res_articles item=article}
  <li><a href="{$nl->prefix()}/show/{$article.short_name}#art{$article.aid}">{$article.title|default:"[Sans titre]"}</a></li>
  {/foreach}
</ul>
{/if}

{if t($res_issues) && $res_issues|@count}
{$issues_count} lettre{if $issues_count > 1}s{/if} correspondate{if $issues_count > 1}s{/if}&nbsp;:
<ul>
  {foreach from=$res_issues item=issue}
  <li><a href="{$nl->prefix()}/show/{$issue->id()}">{$issue->title()|default:"[Sans titre]"}</a></li>
  {/foreach}
</ul>
{/if}

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
