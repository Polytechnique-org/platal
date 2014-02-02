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

<tr>
  <td colspan="2">
    <input type="checkbox" id="x" name="x" onchange="xStateChange(this, '{$platal->ns}');" />
    <label for="x">Coche cette case s'il s'agit d'un X ou un master ou doctorant de l'X non inscrit à Polytechnique.org.</label>
  </td>
</tr>
<tr class="details" style="display: none">
  <td class="titre">Nom&nbsp;:</td>
  <td><input type="text" id="nom" name="nom" size="20" value="" onkeyup="searchX('{$platal->ns}');" /></td>
</tr>
<tr class="details" style="display: none">
  <td class="titre">Prénom&nbsp;:</td>
  <td><input type="text" id="prenom" name="prenom" size="20" value="" onkeyup="searchX('{$platal->ns}');" /></td>
</tr>
<tr class="details" style="display: none">
  <td class="titre">Promotion&nbsp;:</td>
  <td><input type="text" id="promo" name="promo" size="4" value="" onkeyup="searchX('{$platal->ns}');" /> <small>(X2004)</small></td>
</tr>
<tr class="details pair" style="display: none">
  <td colspan="2" id="search_result">
    {include file="xnetgrp/membres-new-search.tpl"}
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
