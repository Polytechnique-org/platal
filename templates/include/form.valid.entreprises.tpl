{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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
  <td class="titre">Nom&nbsp;:</td>
  <td>{$valid->name}</td>
</tr>
<tr class="pair">
  <td class="titre">Noms similaires existants&nbsp;:</td>
  <td>
    <span id="{$valid->type}_{$valid->profile->id()}_{$valid->stamp}_truncated"
          onclick="$('#{$valid->type}_{$valid->profile->id()}_{$valid->stamp}_truncated').hide();
                   $('#{$valid->type}_{$valid->profile->id()}_{$valid->stamp}_full').show()">
      {$valid->suggestions|truncate:200:"&hellip; <strong><small>(cliquez pour tout afficher)</small></strong>"}
    </span>
    <span id="{$valid->type}_{$valid->profile->id()}_{$valid->stamp}_full" style="display: none" >
      <span onclick="$('#{$valid->type}_{$valid->profile->id()}_{$valid->stamp}_truncated').show();
                     $('#{$valid->type}_{$valid->profile->id()}_{$valid->stamp}_full').hide();">
        {icon name=delete title="Cacher les suggestions"}
      </span>
      {$valid->suggestions}
      <span onclick="$('#{$valid->type}_{$valid->profile->id()}_{$valid->stamp}_truncated').show();
                     $('#{$valid->type}_{$valid->profile->id()}_{$valid->stamp}_full').hide();">
        {icon name=delete title="Cacher les suggestions"}
      </span>
    </span>
  </td>
</tr>
<tr class="pair">
  <td class="titre">Acronyme&nbsp;:</td>
  <td>{$valid->acronym}</td>
</tr>
<tr class="pair">
  <td class="titre">Site web&nbsp;:</td>
  <td>{$valid->url}</td>
</tr>
<tr class="pair">
  <td class="titre">Email&nbsp;:</td>
  <td>{$valid->email}</td>
</tr>
<tr class="pair">
  <td class="titre">Holding&nbsp;:</td>
  <td>{$valid->holdingid}</td>
</tr>
<tr class="pair">
  <td class="titre">Code SIREN&nbsp;:</td>
  <td>{$valid->SIREN}</td>
</tr>
<tr class="pair">
  <td class="titre">Code NAF&nbsp;:</td>
  <td>{$valid->NAF_code}</td>
</tr>
<tr class="pair">
  <td class="titre">Code AX&nbsp;:</td>
  <td>{$valid->AX_code}</td>
</tr>
<tr class="pair">
  <td class="titre">Adresse&nbsp;:</td>
  <td>{$valid->address}</td>
</tr>
<tr class="pair">
  <td class="titre">Téléphone&nbsp;:</td>
  <td>{$valid->tel}</td>
</tr>
<tr class="pair">
  <td class="titre">Fax&nbsp;:</td>
  <td>{$valid->fax}</td>
</tr>
<tr class="pair">
  <td colspan="2" class="center">
    <small>Remplir tous les champs possibles, en particulier le <a href="http://societe.com/">code NAF</a> et l'adresse du siège social si possible.</small>
  </td>
</tr>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
