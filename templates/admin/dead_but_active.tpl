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

<h1>Décédés encore actifs</h1>

<p>
  Liste des polytechniciens décédés, mais dont le compte est encore actif
  (veufs/veuves, ...).
</p>

<table class="bicol">
  <tr>
    <th>Promo</th>
    <th colspan="2">État civil</th>
    <th>Décès</th>
    <th>Dernière activité</th>
  </tr>
  {iterate from=$dead item=d}
  <tr class="{cycle values="impair,pair"}">
    <td style="text-align: center">{$d.promo}</td>
    <td>
      <a href="profile/{$d.alias}" class="popup2">{icon name=user_suit title='Afficher la fiche'}</a>
      <a href="http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$d.matricule_ax}">{*
        *}{icon name=user_gray title="fiche AX"}</a>
      <a href="admin/user/{$d.alias}">{icon name=wrench title='Administrer user'}</a>
    </td>
    <td>{$d.prenom} {$d.nom}</td>
    <td style="text-align: center">{$d.deces}</td>
    <td style="text-align: center">
      {if $d.last gt $d.deces}<strong>{$d.last}</strong>{elseif $d.last}{$d.last}{else}-{/if}
    </td>
  </tr>
  {/iterate}
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
