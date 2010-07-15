{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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
              <td class="date-heure">
                <script type="text/javascript">
                  <!--
                  document.write(getNow());
                  //-->
                </script>
              </td>
              <td class="inscrits">
                {$globals->core->NbIns|number_format} polytechniciens sur le web
                <form action="search" method="get">
                    <div>
                        <button id="quick_button" type="submit" style="display: none"
                                onclick="if ($('#quick').val() === 'Recherche dans l\'annuaire') $('#quick').val('')">
                          OK
                        </button>
                        <input type="text" size="20" name="quick" id="quick" class="quick_search"
                               value="{$smarty.request.quick|default:'Recherche dans l\'annuaire'}"
                               onfocus="if (this.value === 'Recherche dans l\'annuaire') this.value='';
                                        $('#quick_button').show()"
                               onblur="if (this.value === '') this.value='{$smarty.request.quick|default:'Recherche dans l\'annuaire'|escape:javascript}'"
                               />
                    </div>
                </form>
                {if $smarty.session.auth gt AUTH_PUBLIC && $smarty.session.notifs}
                <a href="carnet/panel">{$smarty.session.notifs} événement{if $smarty.session.notifs gt 1}s{/if}</a>
                {/if}
              </td>
            </tr>
          </table>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
