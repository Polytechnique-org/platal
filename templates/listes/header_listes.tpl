{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http ://opensource.polytechnique.org/                                   *
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
        $Id: header_listes.tpl,v 1.2 2004-11-07 14:58:35 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

<table>
  <tr>
    <td colspan='2'>
      [<a href='index.php'>Voir toutes les listes</a>]
    </td>
  </tr>
  <tr>
    <td><strong>Liste {$smarty.request.liste} :</strong></td>
    <td>
      {if $on neq members}
      [<a href='members.php?liste={$smarty.request.liste}'>liste des membres</a>]
      {else}
      [liste des membres]
      {/if}
      {if $on neq trombi}
      [<a href='trombi.php?liste={$smarty.request.liste}'>trombinoscope</a>]
      {else}
      [trombinoscope]
      {/if}
    </td>
  </tr>
  {if $details.own || $smarty.session.perms eq admin}
  <tr>
    <td><strong>Administration de la liste :</strong></td>
    <td>
      {if $on neq moderate}
      [<a href='moderate.php?liste={$smarty.get.liste}'>modération</a>]
      {else}
      [modération]
      {/if}
      {if $on neq admin}
      [<a href='admin.php?liste={$smarty.get.liste}'>ajout/retrait de membres</a>]
      {else}
      [ajout/retrait de membres]
      {/if}
      {if $on neq options}
      [<a href='options.php?liste={$smarty.get.liste}'>options</a>]
      {else}
      [options]
      {/if}
    </td>
  </tr>
  {/if}
  {perms level=admin}
  <tr>
    <td><strong>Administration avancée :</strong></td>
    <td>
      {if $on neq soptions}
      [<a href='soptions.php?liste={$smarty.get.liste}'>options avancées</a>]
      {else}
      [options avancées]
      {/if}
      {if $on neq check}
      [<a href='check.php?liste={$smarty.get.liste}'>vérifications</a>]
      {else}
      [vérifications]
      {/if}
    </td>
  </tr>
  {/perms}
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
