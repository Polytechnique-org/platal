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
        $Id: listes.inc.tpl,v 1.8 2004-09-25 16:30:26 x2000habouzit Exp $
 ***************************************************************************}

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
  {if $liste.priv >= $min && $liste.priv <= $max|default:$min}
  <tr class='{cycle values="impair,pair"}'>
    <td>
      <a href='members.php?liste={$liste.list}'>{$liste.list}</a>
      {if $liste.own}
      [<a href='admin.php?liste={$liste.list}'>adm</a>]
      {elseif $smarty.session.perms eq admin}
      [<span class='erreur'><a href='admin.php?liste={$liste.list}'>adm</a></span>]
      {/if}
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
      <a href='{$smarty.server.PHP_SELF}?del={$liste.list}'>
        <img src="{"images/retirer.gif"|url}" alt="[ désinscription ]" />
      </a>
      {elseif $liste.sub eq 1}
      <img src="{"images/flag.png"|url}" alt="[ en cours ]" />
      {else}
      <a href='{$smarty.server.PHP_SELF}?add={$liste.list}'>
        <img src="{"images/ajouter.gif"|url}" alt="[ inscription ]" />
      </a>
      {/if}
    </td>
  </tr>
  {/if}
  {/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
