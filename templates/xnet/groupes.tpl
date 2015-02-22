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

{include file="xnet/include/descr.tpl"}

{assign var="has_inactive" value=false}
<table id="content" cellspacing="0" cellpadding="4">
  <tr>
    <td style="vertical-align: top">
      <div class="cat {if $cat eq 'groupesx'}sel{/if}"><a href="groups/groupesx">Groupes X</a></div>
      <div class="cat {if $cat eq 'binets'}sel{/if}"><a href="groups/binets">Binets</a></div>
      <div class="cat {if $cat eq 'institutions'}sel{/if}"><a href="groups/institutions">Institutions</a></div>
      <div class="cat {if $cat eq 'promotions'}sel{/if}"><a href="groups/promotions">Promotions</a></div>
      <div class="cat {if $cat eq 'deads'}sel{/if}"><a href="groups/deads">Groupes archivés</a></div>
    </td>

    {if $doms}
    <td style="vertical-align: top">
      {foreach from=$doms item=g}
      <div class="cat {if $g.id eq $dom}sel{/if}">
        <a href="groups/{$cat}/{$g.id}">{$g.nom}</a>
      </div>
      {/foreach}
    </td>
    {/if}

    <td style="text-align:right;{if $doms} width: 180px{/if}">
      {if $gps}
      <table style="width: 100%">
        {foreach from=$gps item=g name=all key=i}
        {if $doms || $i is even}
        <tr>
        {/if}
          <td class="oval{if $doms}2{/if} {if $cat eq 'promotions'}{if $g.diminutif is odd}jone{else}rouje{/if}{/if}">
          {if $g.status eq 'inactive'}
            <a href="{$g.diminutif}/" class="grp_inactive{if $g.site} has_site{/if}" title="Groupe inactif">{$g.nom}&nbsp;*</a>
            {assign var="has_inactive" value=true}
          {else}
            <a href="{$g.diminutif}/" {if $g.site} has_site{/if}">{$g.nom}</a>
          {/if}
          </td>
        {if !$doms && $i is even && $smarty.foreach.all.last}<td></td>{/if}
        {if $doms || $i is odd || $smarty.foreach.all.last}
        </tr>
        {/if}
      {/foreach}
      </table>
      {/if}
    </td>
  </tr>
</table>

{if $has_inactive}
<p class="descr">
(*) Groupe inactif
</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
