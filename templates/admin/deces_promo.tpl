{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<form action="admin/deaths" method="post">
  <table class="bicol">
    <tr>
      <td>
        Promotion&nbsp;:
        <input type="text" name="promo" value="{$promo}" size="5" maxlength="5" />
        <input type="submit" value="Afficher" />
      </td>
    </tr>
  </table>
</form>

{if t($profileList)}
<form action="admin/deaths/{$promo}/validate" id="deathDateList" method="post">
  {xsrf_token_field}
  <table class="bicol" summary="liste des dates de décès">
    <tr>
      <th>Nom</th>
      <th>Date de décès</th>
    </tr>
    {iterate item=profile from=$profileList}
    <tr class="{cycle values="impair,pair"}">
      <td>{$profile.directory_name}</td>
      <td class="center">
        <input type="text" name="death_{$profile.pid}" value="{$profile.deathdate}" size="10" maxlength="10" />
      </td>
    </tr>
    {/iterate}
    <tr>
      <td class="center" colspan="2">
        <input type="submit" value="Valider" />
      </td>
    </tr>
  </table>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
