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

<h1><a href="admin/geocoding/{$category}">{$name|ucfirst}&nbsp;: édition</a></h1>

<form method="post" action="admin/geocoding/{$category}/edit/{$id}">
  {xsrf_token_field}

  {if $iso|@count eq 0}
  <p class="error">Cet élément ne figure pas dans la liste ISO.</p>
  {/if}
  <table class="tinybicol">
    <tr>
      <th>Champs</th>
      <th>Élément</th>
      <th>Données ISO</th>
    </tr>
    {foreach from=$all_fields item=field}
    <tr>
      <th>{$field}</th>
      <td>
        <input type="text" name="{$field}" value="{$item.$field}"
               {if t($iso.$field)}{if !$item.$field} class="warning"{elseif $iso.$field neq $item.$field} class="error"{/if}{/if} />
      </td>
      <td>{if t($iso.$field)}{$iso.$field}{/if}</td>
    </tr>
    {/foreach}
  </table>

  <p class="center">
    <input type="submit" name="edit" value="Éditer" />&nbsp;&nbsp;&nbsp;
    <input type="submit" name="del" value="Supprimer" onclick="return confirm('Es-tu sûr de vouloir supprimer cet élément&nbsp;?')" />
  </p>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
