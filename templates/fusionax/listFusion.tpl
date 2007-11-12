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

<table class="bicol">
    <tr>
        <th>Ancien</th>
        <th>Fiches</th>
        {if $field1}
        <th>{$namefield1}</th>
        {/if}
        {if $field2}
        <th>{$namefield2}</th>
        {/if}
        {if $fusionAction}
        <th>Action</th>
        {/if}
    </tr>
{if $fusionList}
{iterate from=$fusionList item=c}
    <tr class="{cycle values="pair,impair"}">
        <td>{$c.display_name} (X {$c.promo})</td>
        <td style="text-align:center">
            {if $c.user_id}<a href="admin/user/{$c.user_id}" class="popup2">{icon name="user_suit" title="Administrer utilisateur"}</a>{/if}
            {if $c.id_ancien}<a href="http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$c.id_ancien}" class="popup2">{icon name="user_gray" title="fiche AX"}</a>{/if}
        </td>
        {if $field1}
        <td>{$c.$field1}</td>
        {/if}
        {if $field2}
        <td>{$c.$field2}</td>
        {/if}
        {if $fusionAction}
        <td><a class="fusion-action" href="{$fusionAction}/{$c.user_id}/{$c.id_ancien}">{$name}</a></td>
        {/if}
    </tr>
{/iterate}
{/if}
</table>
