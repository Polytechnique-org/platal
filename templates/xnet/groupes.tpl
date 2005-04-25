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

<table id="content" cellspacing="0" cellpadding="4">
  <tr>
    <td colspan="2">
      {include file="xnet/include/descr.tpl" cat=$smarty.get.cat}
    </td>
  </tr>
  <tr>
    {if !$doms || !$gps}
    <td style="vertical-align: top">
      <div class="cat {if $smarty.get.cat eq groupesx}sel{/if}"><a href="?cat=groupesx">Groupes X</a></div>
      <div class="cat {if $smarty.get.cat eq binets}sel{/if}"><a href="?cat=binets">Binets</a></div>
      <div class="cat {if $smarty.get.cat eq institutions}sel{/if}"><a href="?cat=institutions">Institutions</a></div>
      <div class="cat {if $smarty.get.cat eq promotions}sel{/if}"><a href="?cat=promotions">Promotions</a></div>
    </td>
    {/if}
    
    {if $doms}
    <td style="vertical-align: top">
      {foreach from=$doms item=g}
      <div class="cat {if $g.id eq $smarty.get.dom}sel{/if}">
        <a href="?cat={$smarty.get.cat}&amp;dom={$g.id}">{$g.nom}</a>
      </div>
      {/foreach}
    </td>
    {/if}

    {if $gps}
    <td style="text-align:right;">
      {iterate from=$gps item=g}
      <table style="float: left;" cellspacing="2" cellpadding="0">
        <tr><td class="oval{if $doms}2{/if}"><a href="{rel}/{$g.diminutif}/asso.php">{$g.nom}</a></td></tr>
      </table>
      {/iterate}
    </td>
    {/if}
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
