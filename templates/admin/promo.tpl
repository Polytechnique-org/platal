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

<h1>Mise à jour de l'annuaire</h1>

<form action="{$platal->pl_self()}" method="post">
<table class="tinybicol">
  <tr>
    <td class="center">
      <strong>Promotion&nbsp;:</strong>
      <input type="text" size="4" name="promo" value="{$promo}" /><br />
      <input type="submit" name="valid_promo" value="Ajouter des membres" />
      <input type="submit" name="valid_promo" value="Mettre à jour les matricules AX" />
    <td>
  </tr>
</table>
</form>

{if $promo}
{include file="include/csv-importer.tpl"}
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
