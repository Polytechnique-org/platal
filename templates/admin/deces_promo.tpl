{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************}


{dynamic}

<form action="{$smarty.server.PHP_SELF}" method="get">
  <table class="tinybicol">
    <tr>
      <td><input type="submit" value="&lt;&lt;" name="sub10" /></td>
      <td><input type="submit" value="&lt;"  name="sub01" /></td>
      <td>
        Promotion :
        <input type="text" name="promo" value="{$promo}" size="4" maxlength="4" />
        <input type="submit" value="GO" />
      </td>
      <td><input type="submit" value="&gt;"  name="add01" /></td>
      <td><input type="submit" value="&gt;&gt;" name="add10" /></td>
    </tr>
  </table>
</form>

<form action="{$smarty.server.REQUEST_URI}" method="post">
  <table class="bicol" summary="liste des dates de décès">
    <tr>
      <th>Nom</th>
      <th>Date de décès</th>
    </tr>
    {foreach item=x from=$decedes}
    <tr class="{cycle values="impair,pair"}">
      <td>{$x.nom} {$x.prenom}</td>
      <td class="center">
        <input type="text" name="{$x.matricule}" value="{$x.deces}" size="10" maxlength="10" />
      </td>
    </tr>
    {/foreach}
    <tr>
      <td class="center" colspan="2">
        <input type="hidden" name="promo" value="{$promo}" />
        <input type="submit" name="valider" value="Valider" />
      </td>
    </tr>
  </table>
</form>

{/dynamic}
{* vim:set et sw=2 sts=2 sws=2: *}
