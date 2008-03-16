{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<h1>{$asso.nom}&nbsp;: Administration des announces</h1>

<table class="bicol">
  <tr>
    <th>Titre</th>
    <th>Péremption</th>
    <th></th>
  </tr>
  {iterate item=art from=$articles}
  <tr class="{if $art.perime}im{/if}pair">
    <td><a href="{$platal->ns}announce/edit/{$art.id}">{$art.titre}</a></td>
    <td>{$art.peremption|date_format}</td>
    <td class="right"><a href="{$platal->ns}admin/announces?del={$art.id}">
        Supprimer l'annonce {icon name=cross}
      </a>
    </td>
  </tr>
  {/iterate}
  <tr>
    <td colspan="3" class="center">
      <a href="{$platal->ns}announce/new">
        {icon name=add title="Nouvelle annonce"} Écrire une nouvelle annonce.
      </a>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
