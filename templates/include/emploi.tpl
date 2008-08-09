{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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
{if $address.entreprise || $address.secteur || $address.fonction ||
  $address.poste || $address.fonction || $address.email || $address.web}
      <div class="adresse" style="float: left">
      	<table>
        {if $address.entreprise || $address.web}
        <tr>
          <td><em>Ent/Org&nbsp;: </em></td>
          <td><strong>{$address.entreprise}{if $address.web} [<a href='{$address.web}'>site</a>]{/if}</strong></td>
        </tr>
        {/if}
        {if $address.secteur}
        <tr>
          <td><em>Secteur&nbsp;: </em></td>
          <td><strong>{$address.secteur}{if $address.ss_secteur} ({$address.ss_secteur}){/if}</strong></td>
        </tr>
        {/if}

        {if $address.fonction}
        <tr>
          <td><em>Fonction&nbsp;: </em></td>
          <td><strong>{$address.fonction}</strong></td>
        </tr>
        {/if}
        {if $address.poste}
        <tr>
          <td><em>Poste&nbsp;: </em></td>
          <td><strong>{$address.poste}</strong></td>
        </tr>
        {/if}
        {if $address.email}
        <tr>
          <td><em>Email&nbsp;: </em></td>
          <td><strong>{$address.email}</strong></td>
        </tr>
        {/if}
        </table>
      </div>
{/if}

{* vim:set et sws=2 sts=2 sw=2 enc=utf-8: *}
