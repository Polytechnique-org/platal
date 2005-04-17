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

<table id="liste">
  <tr>
    <td style="width: 25%">
      <table>
        <tr>
          <td class="oval"><a href="groupes.php?cat=GroupesX">Groupes X</a></td>
        </tr>
        <tr>
          <td class="liste">
            {foreach from=$groupesx key=id item=dom}
            <a class="cat" href="sommaire.php?cat=GroupesX&amp;dom={$id}">{$dom[0].domnom}</a>
            {foreach from=$dom item=g}
            <a href="{$g.diminutif}/asso.php">{$g.nom}</a>
            {/foreach}
            {/foreach}
          </td>
        </tr>
      </table>
    </td>

    <td style="width: 25%">
      <table>
        <tr>
          <td class="oval"><a href="groupes.php?cat=Binets">Binets</a></td>
        </tr>
        <tr>
          <td class="liste">
            {foreach from=$binets key=id item=dom}
            <a class="cat" href="sommaire.php?cat=BinetsX&amp;dom={$id}">{$dom[0].domnom}</a>
            {foreach from=$dom item=g}
            <a href="{$g.diminutif}/asso.php">{$g.nom}</a>
            {/foreach}
            {/foreach}
          </td>
        </tr>
      </table>
    </td>

    <td style="width: 25%">
      <table>
        <tr>
          <td class="oval"><a href="groupes.php?cat=Promotions">Promotions</a></td>
        </tr>
        <tr>
          <td class="listec">
            {iterate from=$promos item=g}
            <a href="{$g.diminutif}/asso.php">{$g.nom}</a>
            {/iterate}
          </td>
        </tr>
      </table>
    </td>

    <td style="width: 25%">
      <table>
        <tr>
          <td class="oval"><a href="groupes.php?cat=Institutions">Institutions</a></td>
        </tr>
        <tr>
          <td class="listec">
            {iterate from=$inst item=g}
            <a href="{$g.diminutif}/asso.php">{$g.nom}</a>
            {/iterate}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
