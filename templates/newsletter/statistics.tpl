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

{include file="newsletter/header.tpl" current="stats"}

<p>
  Il y a actuellement {$count} inscrits aux envois, parmi lesquels {$lost} n'ont aucune redirection active.
  En particulier, il y a {$count_female} femmes, parmi lesquelles {$lost_female} n'ont aucune redirection active.
</p>

<table class="bicol">
{foreach from=$data item=education key=cycle}
  <tr>
    <th colspan="3">Cycle {$cycle}</th>
  </tr>
  <tr>
    <td class="titre">Décade</td>
    <td class="titre">Total (dont perdus)</td>
    <td class="titre">Femmes (dont perdues)</td>
  </tr>
  {foreach from=$education item=counts key=period}
  <tr>
    <td>{$period}</td>
    <td>{$counts.count} ({$counts.lost})</td>
    <td>{$counts.count_female} ({$counts.lost_female})</td>
  </tr>
  {/foreach}
{/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
