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

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th>Diff.</th>
    <th>Inscr.</th>
    <th>Nb</th>
    <th></th>
  </tr>
  {foreach from=$listes item=liste}
  {if $liste.priv eq $priv}
  <tr class='{cycle values="impair,pair"}'>
    <td>
      <a href='{$platal->ns}lists/members/{$liste.list}'>{$liste.list}{if $liste.own}&nbsp;*{/if}</a> 
    </td>
    <td>{$liste.desc}</td>
    <td class='center'>
      {if $liste.diff eq 2}modérée{elseif $liste.diff}restreinte{else}libre{/if}
    </td>
    <td class='center'>
      {if $liste.ins}modérée{else}libre{/if}
    </td>
    <td class='right'>{$liste.nbsub}</td>
    <td class='right'>
      {if $liste.sub eq 2}
      <a href='{$platal->ns}lists?del={$liste.list}'>
        {icon name=cross title="me désinscrire"}
      </a>
      {elseif $liste.sub eq 1}
      {icon name=flag_orange title='inscription en attente de modération'}
      {else}
      <a href='{$platal->ns}lists?add={$liste.list}'>
        {icon name=add title="m'inscrire"}</a>
      {/if}
    </td>
  </tr>
  {/if}
  {/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
