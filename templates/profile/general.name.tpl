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


<table class="bicol" style="margin-bottom: 1em" summary="Profil : Noms">
  <tr>
    <th colspan="2">
      Noms
    </th>
  </tr>
  <tr>
    <td>
      <span class="titre">Nom</span><br/>
    </td>
    <td>
      {$nom}
      <input type='hidden' name='nom' {if $errors.nom}class="error"{/if} value="{$nom}" />
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Prénom</span><br/>
    </td>
    <td>
      {$prenom}
      <input type='hidden' name='prenom' {if $errors.prenom}class="error"{/if} value="{$prenom}" />
    </td>
  </tr>
  <tr>
    <td>
      <span class="titre">Affichage de ton nom</span>
    </td>
    <td>
      {if $tooltip_name}<span title="{$tooltip_name}" class="hint">{$display_name}</span>{else}{$display_name}{/if}
      <a href="profile/edit#names_advanced" onclick="$('.names_advanced').show();$(this).hide();document.location = document.location + '#names_advanced';return false">
        {icon name="page_edit" title="Plus de détail"}
      </a>
    </td>
  </tr>
  <tr class="names_advanced" style="display: none">
    <td>
      <span class="flags">
        <input type="checkbox" checked="checked" disabled="disabled" />
        {icon name="flag_green" title="site public"}
      </span>&nbsp;
      <span class="titre">Affichage courant de ton nom</span>
      <a class="popup3" href="Xorg/Profil#name_displayed">{icon name="information" title="aide"}</a>
    </td>
    <td>
      <input type="text" name="display_name" value="{$display_name}" size="40"/>
    </td>
  </tr>
  <tr class="names_advanced" style="display: none">
    <td>
      <span class="titre">explication</span>
      <a class="popup3" href="Xorg/Profil#name_tooltip">{icon name="information" title="aide"}</a>
    </td>
    <td>
      <input type="text" name="tooltip_name" value="{$tooltip_name}" size="40"/>
    </td>
  </tr>
  <tr class="names_advanced" style="display: none">
    <td>
      <span class="titre">ranger ce nom à</span>
      <a class="popup3" href="Xorg/Profil#name_order">{icon name="information" title="aide"}</a>
    </td>
    <td>
      <input type="text" name="sort_name" value="{$sort_name}" size="40"/>
    </td>
  </tr>
  <tr class="names_advanced" style="display: none">
    <td>
      <span class="flags">
        <input type="checkbox" checked="checked" disabled="disabled" />
        {icon name="flag_red" title="privé"}
      </span>&nbsp;
      <span class="titre">Comment on doit t'appeller</span>
      <a class="popup3" href="Xorg/Profil#name_yourself">{icon name="information" title="aide"}</a>
    </td>
    <td>
      <input type="text" name="yourself" value="{$yourself}" size="40"/>
    </td>
  </tr>
  <tr class="names_advanced" style="display: none">
    <td colspan="2">
      <span class="titre">Recherche</span><span class="smaller">, ta fiche apparaît quand on cherche un de ces noms</span>
      <a class="popup3" href="Xorg/Profil#name_search">{icon name="information" title="aide"}</a>
      {iterate from=$search_names item="sn"}
      <div id="search_name_{$sn.sn_id}" style="padding:2px" class="center">
        {include file="profile/general.searchname.tpl" i=$sn.sn_id sn=$sn}
      </div>
      {/iterate}
      <div id="add_search_name" class="center" style="clear: both">
        <a href="javascript:addSearchName()">
          {icon name=add title="Ajouter un nom de recherche"} Ajouter un nom
        </a>
      </div>
    </td>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
