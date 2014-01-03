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

<tr class="pair">
  <td class="titre">Groupe demandeur&nbsp;:</td>
  <td><a href="http://polytechnique.net/{$valid->dim}">{$valid->group}</a></td>
</tr>
<tr class="pair">
  <td class="titre">Tous les groupes&nbsp;:</td>
  <td>
  {foreach from=$valid->groups item=group}
    <a href="http://polytechnique.net/{$group.diminutif}">{$group.nom}</a>
  {/foreach}
  </td>
</tr>
<tr class="pair">
  <td class="titre">Adresse email&nbsp;:</td>
  <td><a href="http://polytechnique.net/{$valid->dim}/member/{$valid->hruid}">{$valid->email}</a></td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
