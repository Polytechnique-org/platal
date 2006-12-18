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

<table class="tinybicol" id="art{$art.id}">
  <tr>
    <th>
      {if $is_logged && !$admin}
      <div style="float: right">
        {if $is_admin}
          <a href="{$platal->ns}announce/edit/{$art.id}">{icon name=page_edit title="Editer cet article"}</a>
        {/if}
        <a href="{$platal->ns}?read={$art.id}">{icon name=cross title="Cacher cet article"}</a>
      </div>
      {/if}
      {tidy}
      {if $admin}Aperçu de : {/if}{$art.titre}
      {/tidy}
    </th>
  </tr>
  <tr>
    <td style="padding-bottom: 1em">
      {tidy}
      {$art.texte|nl2br}
      {/tidy}
    </td>
  </tr>
  {if ($is_logged || $admin) && $art.contacts}
  <tr class="pair">
    <td class="titre">Contacts :</td>
  </tr>
  <tr class="pair">
    <td style="padding-left: 20px">
      {tidy}
      {if $art.contact_html}
      {$art.contact_html|nl2br|smarty:nodefaults}
      {else}
      {$art.contacts|url_catcher|nl2br|smarty:nodefaults}
      {/if}
      {/tidy}
    </td>
  </tr>
  {/if}
  <tr class="pair right">
    <td>
      <small>
        Annonce proposée par
        <a class="popup2" href="https://www.polytechnique.org/profile/{$art.forlife}">
          {$art.prenom} {$art.nom} (X{$art.promo})
        </a>
      </small>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
