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

<table class="bicol" style="margin-bottom: 1em" summary="Profil&nbsp;: Informations Polytechniciennes">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left">
        <input type="checkbox" name="accesX" checked="checked" disabled="disabled" />
        {icon name="flag_red" title="privé"}
      </div>
      Informations polytechniciennes
    </th>
  </tr>
  <tr class="top">
    <td class="titre" style="width: 30%">{if $old}ex-{/if}Section</td>
    <td>
      <select name="section">
        {select_db_table table="sections" valeur=$section}
      </select>
    </td>
  </tr>
  <!-- Binets -->
  <tr id="binets">
    <td class="titre">{if $old}ex-{/if}Binet(s)</td>
    <td>
      <select name="binets_sel" onchange="updateGroup('binets')">
        {select_db_table table="binets_def" valeur=0 champ="text" pad='1'}
      </select>
      <a id="binets_add" style="display: none"
         href="javascript:addGroup('binets')">{icon name="add" title="Ajouter ce binet"}</a>
    </td>
  </tr>
  {foreach item=text key=bid from=$binets}
  <tr id="binets_{$bid}">
    <td>
      <input type="hidden" name="binets[{$bid}]" value="{$text}" />
    </td>
    <td>
      <div style="float: left; width: 70%">
        {$text}
      </div>
      <a href="javascript:removeGroup('binets', {$bid})">{icon name="cross" title="Supprimer ce binet"}</a>
    </td>
  </tr>
  {/foreach}
</table>
<table class="bicol" style="margin-bottom: 1em">
  <tr>
    <th colspan="2">Groupes et institutions X</th>
  </tr>
  <tr>
    <td colspan="2">
      <p>
        La gestion des annuaires des groupes X est réalisée grâce à
        <a href="http://www.polytechnique.net">Polytechnique.net</a>. Pour faire apparaître un groupe sur ta fiche
        il faut que soit inscrit dans ce groupe sur l'annuaire sur Polytechnique.net.
      </p>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <p>
        Tu peux demander ton inscription aux groupes X qui t'intéressent. Voici la liste de ceux disponibles actuellement
        sur Polytechnique.net et qui autorisent les demandes d'inscription&nbsp;:
      </p>
    </td>
  </tr>
  <tr>
    <td>
      {assign var=groupcat value=""}
      {assign var=ingroup value=false}
      <select name="groupesx_sub" onchange="updateGroupSubLink(this)">
        {iterate from=$listgroups item=group}
        {if $group.dom neq $groupcat}
          {if $ingroup}
          </optgroup>
          {assign var=ingroup value=false}
          {/if}
          {if $group.dom}
          <optgroup label="{$group.dom}">
          {assign var=ingroup value=true}
          {assign var=groupcat value="`$group.dom`"}
          {/if}
        {/if}
        <option value="{if $group.sub_url}{$group.sub_url}{else}http://www.polytechnique.net/{$group.diminutif}/subscribe{/if}">
          {$group.nom}
        </option>
        {/iterate}
        {if $ingroup}</optgroup>{/if}
      </select>
    </td>
    <td style="text-align: right">
      <a href="http://www.polytechnique.net" id="groupesx_sub">
        {icon name="add" title="Demander ton inscription"} Demander ton inscription
      </a>
    </td>
  </tr>
  {if $mygroups->total()}
  <tr class="pair">
    <td colspan="2">Tu es actuellement dans les groupes suivants&nbsp;:</td>
  </tr>
  {/if}
  {assign var=grp_admin value=false}
  {assign var=grp_public value=false}
  {iterate from=$mygroups item=group}
  <tr class="pair">
    <td class="titre">
      {if $group.pub neq 'public'}
        {icon name=error title="Liste de membres a accès restreint"}
        {assign var=grp_public value=true}
      {/if}
      {if $group.perms eq 'admin'}
        {icon name=wrench title="Tu es administrateur du groupe"}
        {assign var=grp_admin value=true}
      {/if}
      {if $group.site}<a href="{$group.site}">{else}<a href="http://www.polytechnique.net/{$group.diminutif}">{/if}
      {$group.nom}
      </a>
    </td>
    <td style="text-align: right; width: 30%">
      <a href="{if $group.unsub_url}{$group.unsub_url}{else}http://www.polytechnique.net/{$group.diminutif}/unsubscribe{/if}">
        {icon name=cross title="Demander désinscription"} Demander ta désinscription
      </a>
    </td>
  </tr>
  {/iterate}
  {if $grp_admin || $grp_public}
  <tr class="pair">
    <td colspan="2" class="smaller">
      <div class="titre">Signification des symboles&nbsp;:</div>
      {if $grp_public}{icon name=error} L'annuaire du groupe est à visibilité restreinte et ce groupe n'apparaîtra pas sur ta
      fiche{/if}
      {if $grp_public && $grp_admin}<br />{/if}
      {if $grp_admin}{icon name=wrench} Tu es administrateur du groupe{/if}
    </td>
  </tr>
  {/if}
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
