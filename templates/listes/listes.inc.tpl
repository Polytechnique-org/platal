{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: listes.inc.tpl,v 1.2 2004-09-21 15:40:36 x2000habouzit Exp $
 ***************************************************************************}

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th>Diffusion</th>
    <th>Inscription</th>
    <th></th>
  </tr>
  {foreach from=$listes item=liste}
  {if $liste.priv >= $min && $liste.priv <= $max|default:$min}
  <tr class='{cycle values="impair,pair"}'>
    <td>
      <a href='liste.php?liste={$liste.list}'>{$liste.list}</a>
      {if $liste.you>1}[<a href='moderate.php?liste={$liste.list}'>mod</a>]{/if}
    </td>
    <td>{$liste.desc}</td>
    <td class='center'>{if $liste.diff}modérée{else}libre{/if}</td>
    <td class='center'>{if $liste.ins}modérée{else}libre{/if}</td>
    <td class='right'>
      {if $liste.you is odd}
      <a href='{$smarty.server.PHP_SELF}?del={$liste.list}'>me désinscrire</a>
      {elseif $liste.ins}
      <a href='{$smarty.server.PHP_SELF}?add={$liste.list}'>m'inscrire</a>
      {/if}
    </td>
  </tr>
  {/if}
  {/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
