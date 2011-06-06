{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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
    <td class="date-heure"></td>
    <td class="inscrits">
      {$globals->core->NbIns|number_format} étudiants et anciens de l'X sur le web
      {if t($smarty.request.quick)}
        {assign var=requestQuick value=$smarty.request.quick|smarty:nodefaults}
      {else}
        {assign var=requestQuick value='Recherche dans l\'annuaire'}
      {/if}
      <form action="search" method="get">
          <div>
              <button id="quick_button" type="submit" style="display: none">
                OK
              </button>
              <input type="text" size="20" name="quick" id="quick" class="quick_search"
                     value="{$requestQuick}" />
          </div>
      </form>
      {if $smarty.session.auth gt AUTH_PUBLIC && $smarty.session.notifs}
      <a href="carnet/panel">{$smarty.session.notifs} événement{if $smarty.session.notifs gt 1}s{/if}</a>
      {/if}
    </td>
  </tr>
</table>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
