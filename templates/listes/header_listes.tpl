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
 ***************************************************************************}

{if $it_is_xnet}
{assign var=index value="listes.php"}
{assign var=prefix value="listes-"}
{else}
{assign var=index value="index.php"}
{assign var=prefix value=""}
{/if}

<table>
  <tr>
    <td colspan='2'>
      [<a href='{$index}'>Voir toutes les listes</a>]
    </td>
  </tr>
  <tr>
    <td><strong>Liste {$smarty.request.liste} :</strong></td>
    <td>
      {if $on neq members}
      [<a href='{$prefix}members.php?liste={$smarty.request.liste}'>liste des membres</a>]
      {else}
      [liste des membres]
      {/if}
      {if $on neq trombi}
      [<a href='{$prefix}trombi.php?liste={$smarty.request.liste}'>trombinoscope</a>]
      {else}
      [trombinoscope]
      {/if}
      {if $on neq archives}
      [<a href='{$prefix}archives.php?liste={$smarty.request.liste}'>archives</a>]
      {else}
      [archives]
      {/if}
    </td>
  </tr>
  {if $details.own || $smarty.session.perms eq admin}
  <tr>
    <td><strong>Administrer la liste :</strong></td>
    <td>
      {if $on neq moderate}
      [<a href='{$prefix}moderate.php?liste={$smarty.get.liste}'>modération</a>]
      {else}
      [modération]
      {/if}
      {if $on neq admin}
      [<a href='{$prefix}admin.php?liste={$smarty.get.liste}'>ajout/retrait de membres</a>]
      {else}
      [ajout/retrait de membres]
      {/if}
      {if $on neq options}
      [<a href='{$prefix}options.php?liste={$smarty.get.liste}'>options</a>]
      {else}
      [options]
      {/if}
      {if $on neq delete}
      [<a href='{$prefix}delete.php?liste={$smarty.get.liste}'>détruire</a>]
      {else}
      [détruire liste]
      {/if}
    </td>
  </tr>
  {/if}
  {perms level=admin}
  <tr>
    <td><strong>Administrer (avancé) :</strong></td>
    <td>
      {if $on neq soptions}
      [<a href='{$prefix}soptions.php?liste={$smarty.get.liste}'>options avancées</a>]
      {else}
      [options avancées]
      {/if}
      {if $on neq check}
      [<a href='{$prefix}check.php?liste={$smarty.get.liste}'>vérifications</a>]
      {else}
      [vérifications]
      {/if}
    </td>
  </tr>
  {/perms}
</table>


{* vim:set et sw=2 sts=2 sws=2: *}
