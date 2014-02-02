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

<h1>{$name|ucfirst}</h1>

{foreach from=$lists item=list key=list_description}
{if $list_description eq "manquant"}
{assign var="fields" value=$main_fields}
{assign var="action" value="add"}
{else}
{assign var="fields" value=$all_fields}
{assign var="action" value="edit"}
{/if}

<h2>{$list|@count} {$name} {$list_description}{if $list|@count > 1}s{/if}.</h2>
{if $list|@count}
<table class="bicol">
  <tr>
    <th>{$id}</th>
    {foreach from=$fields item=field}
    <th>{$field}</th>
    {/foreach}
    <th>{$action}</th>
  </tr>
{foreach from=$list item=item key=key}
  <tr>
    <td>{$key}</td>
    {foreach from=$fields item=field}
    {assign var="error" value=$field|cat:'_error'}
    {assign var="warning" value=$field|cat:'_warning'}
    <td{if t($item.$error)} class="error"{elseif t($item.$warning)} class="warning"{/if}>{$item.$field}</td>
    {/foreach}
    <td><a href="admin/geocoding/{$category}/{$action}/{$key}">{icon name="page_edit"}</a></td>
  </tr>
{/foreach}
  <tr>
    <th>{$id}</th>
    {foreach from=$fields item=field}
    <th>{$field}</th>
    {/foreach}
    <th>{$action}</th>
  </tr>
</table>
{/if}
{/foreach}

<form method="post" action="admin/geocoding/{$category}/add">
  {xsrf_token_field}
  <p>
    Ajouter un champ (n'indiquer que l'indentifiant principal)&nbsp;:
    <input type="text" name="new_id" size="3" maxlength="3" />
    <input type="submit" value="Ajouter" />
  </p>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
