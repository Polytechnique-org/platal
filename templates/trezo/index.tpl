{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>Tr&eacute;sorerie pour {$mon_sel}</h1>

{perms level=admin}
<p>[<a href="{rel}/trezo/ops">éditer les comptes</a>]</p>
{/perms}

<table class="bicol">
<tr>
  <th>Solde en début de période</th>
</tr>
<tr>
  <td class="right">{$from_solde}</td>
</tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th>Date</th>
    <th>Description</th>
    <th>D&eacute;penses</th>
    <th>Recettes</th>
  </tr>
{iterate item=op from=$ops}
  <tr class="{cycle values="impair,pair"}">
    <td>{$op.date|date_format}</td>
    <td>{$op.label}</td>
    <td class="right">{$op.debit}</td>
    <td class="right">{$op.credit}</td>
  </tr>
{/iterate}
</table>

<br />

<table class="bicol">
<tr>
  <th>Solde en fin de mois</th>
</tr>
<tr>
  <td class="right">{$to_solde}</td>
</tr>
</table>

<br />

{include file=trezo/choix_date.tpl month_arr=$month_arr}


{* vim:set et sw=2 sts=2 sws=2: *}
