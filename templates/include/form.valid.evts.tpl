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


<tr class="pair">
  <td class="titre">Titre</td>
  <td>{$valid->titre}</td>
</tr>
<tr class="pair">
  <td class="titre">Texte</td>
  <td>{tidy}{$valid->texte|miniwiki|smarty:nodefaults}{/tidy}</td>
</tr>
<tr class="pair">
  <td class="titre">Péremption</td>
  <td>{$valid->peremption|date_format}</td>
</tr>
<tr class="pair">
  <td class="titre">Promos</td>
  <td>{$valid->pmin} - {$valid->pmax}</td>
</tr>
<tr class="pair">
  <td class="titre">Illustration</td>
  <td>
    {if $valid->imgtype}
    <img src="events/photo/valid/{$valid->id()}" alt="Image" />
    {else}
    Pas d'image définie
    {/if}
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
