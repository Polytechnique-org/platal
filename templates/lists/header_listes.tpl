{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

<table>
  <tr>
    <td colspan='2'>
      [<a href='{$platal->ns}lists'>Voir toutes les listes</a>]
    </td>
  </tr>
  <tr>
    <td><strong>Liste {$platal->argv[1]} :</strong></td>
    <td>
      {if $on neq members}
      [<a href='{$platal->ns}lists/members/{$platal->argv[1]}'>liste des membres</a>]
      {else}
      [liste des membres]
      {/if}
      {if $on neq trombi}
      [<a href='{$platal->ns}lists/trombi/{$platal->argv[1]}'>trombinoscope</a>]
      {else}
      [trombinoscope]
      {/if}
      {if $on neq archives}
      [<a href='{$platal->ns}lists/archives/{$platal->argv[1]}'>archives</a>]
      {else}
      [archives]
      {/if}
    </td>
  </tr>
  {if $details.own || $smarty.session.perms eq admin || ($it_is_xnet && $is_admin)}
  <tr>
    <td><strong>Administrer la liste :</strong></td>
    <td>
      {if $on neq moderate}
      [<a href='{$platal->ns}lists/moderate/{$platal->argv[1]}'>modération</a>]
      {else}
      [modération]
      {/if}
      {if $on neq admin}
      [<a href='{$platal->ns}lists/admin/{$platal->argv[1]}'>ajout/retrait de membres</a>]
      {else}
      [ajout/retrait de membres]
      {/if}
      {if $on neq options}
      [<a href='{$platal->ns}lists/options/{$platal->argv[1]}'>options</a>]
      {else}
      [options]
      {/if}
      {if $on neq delete}
      [<a href='{$platal->ns}lists/delete/{$platal->argv[1]}'>détruire</a>]
      {else}
      [détruire liste]
      {/if}
    </td>
  </tr>
  {/if}
  {if $smarty.session.perms eq admin || ($it_is_xnet && $is_admin)}

  <tr>
    <td><strong>Administrer (avancé) :</strong></td>
    <td>
      {if $on neq soptions}
      [<a href='{$platal->ns}lists/soptions/{$platal->argv[1]}'>options avancées</a>]
      {else}
      [options avancées]
      {/if}
      {if $on neq check}
      [<a href='{$platal->ns}lists/check/{$platal->argv[1]}'>vérifications</a>]
      {else}
      [vérifications]
      {/if}
    </td>
  </tr>
  {/if}
  {if $it_is_xnet && ($details.own || $is_admin)}
  <tr>
    <td><strong>Synchroniser</strong></td>
    {if $on neq sync}
    <td>[<a href="{$platal->ns}lists/sync/{$platal->argv[1]}">Synchroniser avec l'annuaire</a>]</td>
    {else}
    <td>[Synchroniser avec l'annuaire]</td>
    {/if}
  </tr>
  {/if}
</table>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
