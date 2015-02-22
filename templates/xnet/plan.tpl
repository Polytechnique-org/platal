{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<table id="liste">
  <tr>
    <td class="fourth">
      <table>
        <tr>
          <td class="oval"><a href="groups/groupesx">Groupes X</a></td>
        </tr>
        <tr>
          <td class="liste">
            {foreach from=$groupesx key=id item=dom}
            <a class="cat" href="groups/groupesx/{$id}">{$dom[0].domnom}</a>
            {foreach from=$dom item=g}
              {if $g.status eq 'inactive'}
                <a class="grp_inactive" href="{$g.diminutif}/" title="Groupe inactif">{$g.nom}&nbsp;*</a>
              {else}
                <a href="{$g.diminutif}/">{$g.nom}</a>
              {/if}
            {/foreach}
            {/foreach}
          </td>
        </tr>
      </table>
    </td>

    <td class="fourth">
      <table>
        <tr>
          <td class="oval"><a href="groups/binets">Binets</a></td>
        </tr>
        <tr>
          <td class="liste">
            {foreach from=$binets key=id item=dom}
            <a class="cat" href="groups/binets/{$id}">{$dom[0].domnom}</a>
            {foreach from=$dom item=g}
              {if $g.status eq 'inactive'}
                <a class="grp_inactive" href="{$g.diminutif}/" title="Groupe inactif">{$g.nom}&nbsp;*</a>
              {else}
                <a href="{$g.diminutif}/">{$g.nom}</a>
              {/if}
            {/foreach}
            {/foreach}
          </td>
        </tr>
      </table>
    </td>

    <td class="fourth">
      <table>
        <tr>
          <td class="oval"><a href="groups/promotions">Promotions</a></td>
        </tr>
        <tr>
          <td class="listec">
            {iterate from=$promos item=g}
              {if $g.status eq 'inactive'}
                <a class="grp_inactive" href="{$g.diminutif}/" title="Groupe inactif">{$g.nom}&nbsp;*</a>
              {else}
                <a href="{$g.diminutif}/">{$g.nom}</a>
              {/if}
            {/iterate}
          </td>
        </tr>
      </table>
    </td>

    <td class="fourth">
      <table>
        <tr>
          <td class="oval"><a href="groups/institutions">Institutions</a></td>
        </tr>
        <tr>
          <td class="listec">
            {iterate from=$inst item=g}
              {if $g.status eq 'inactive'}
                <a class="grp_inactive" href="{$g.diminutif}/" title="Groupe inactif">{$g.nom}&nbsp;*</a>
              {else}
                <a href="{$g.diminutif}/">{$g.nom}</a>
              {/if}
            {/iterate}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<p class="descr">
(*) Groupe inactif
</p>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
