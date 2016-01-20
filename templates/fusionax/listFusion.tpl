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

<table class="bicol">
    <tr>
        <th>Ancien</th>
        <th>Fiches</th>
        {if t($field1)}
        <th>{$namefield1}</th>
        {/if}
        {if t($field2)}
        <th>{$namefield2}</th>
        {/if}
        {if t($field3)}
        <th>{$namefield3}</th>
        {/if}
        {if t($field4)}
        <th>{$namefield4}</th>
        {/if}
        {if t($fusionAction)}
        <th>Action</th>
        {/if}
    </tr>
{if t($fusionList)}
{iterate from=$fusionList item=c}
    <tr class="{cycle values="pair,impair"}">
        <td>{$c.private_name} ({$c.promo})</td>
        <td style="text-align:center">
            {if t($c.pid)}<a href="profile/{$c.pid}" class="popup2">{icon name="user_suit" title="Administrer utilisateur"}</a>{/if}
            {if t($c.ax_id)}<a href="http://kx.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;ancc_id={$c.ax_id}" class="popup2">{icon name="user_gray" title="fiche AX"}</a>{/if}
        </td>
        {if t($field1)}
        <td>{$c.$field1}</td>
        {/if}
        {if t($field2)}
        <td>{$c.$field2}</td>
        {/if}
        {if t($field3)}
        <td>{$c.$field3}</td>
        {/if}
        {if t($field4)}
        <td>{$c.$field4}</td>
        {/if}
        {if t($fusionAction)}
        <td><a class="fusion-action" href="{$fusionAction}/{$c.pid}/{$c.ax_id}">{$name}</a></td>
        {/if}
    </tr>
{/iterate}
{/if}
</table>
