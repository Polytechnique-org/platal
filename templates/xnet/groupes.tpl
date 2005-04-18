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

<table id="content">
  <tr>
    <td id="menu">
    </td>
    <td>
      <table cellspacing="0" cellpadding="4">
        <tr>
          <td colspan="2">
            {include file="xnet/include/descr.tpl" cat=$smarty.get.cat}
          </td>
        </tr>

        <tr>
          <!-- Enumération de tous les domaines existants dans la catégorie concernées -->
          <td style="vertical-align: top">
            {iterate from=$doms item=g}
            <div class="cat {if $g.id eq $smarty.get.dom}sel{/if}">
              <a href="groupes.php?cat={$smarty.get.cat}&amp;dom={$g.id}">{$g.nom}</a>
            </div>
            {/iterate}
          </td>
          <td>
            {if $gps}
            {iterate from=$gps item=g}
            <table style="float: left;" cellspacing="2" cellpadding="0">
              <tr>
                <td class="oval"><a href="...">{$g.nom}</a></td>
              </tr>
            </table>
            {/iterate}
            {else}
            <img src="images/carre2.jpg" alt="logos_associations" width="201" height="165" />
            {/if}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
