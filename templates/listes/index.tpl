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
 ***************************************************************************
        $Id: index.tpl,v 1.1 2004-09-10 11:52:37 x2000habouzit Exp $
 ***************************************************************************}

 {dynamic}

<div class="rubrique">
  Listes de diffusion publiques
</div>

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th>Diffusion</th>
    <th>Inscription</th>
  </tr>
  {foreach from=$listes item=liste}
  {if $liste.priv eq 0}
  <tr class='{cycle values="impair,pair"}'>
    <td>{$liste.list}{if $liste.you>1}*{/if}</td>
    <td>{$liste.desc}</td>
    <td class='center'>{if $liste.diff}modérée{else}libre{/if}</td>
    <td class='right'>{if $liste.you is odd}désinscription{elseif $liste.ins}ins modérée{else}inscription{/if}</td>
  </tr>
  {/if}
  {/foreach}
</table>

<div class="rubrique">
  Listes de diffusion privées
</div>

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th>Diffusion</th>
    <th>Inscription</th>
  </tr>
  {foreach from=$listes item=liste}
  {if $liste.priv eq 1}
  <tr class='{cycle values="impair,pair"}'>
    <td>{$liste.list}{if $liste.you>1}*{/if}</td>
    <td>{$liste.desc}</td>
    <td class='center'>{if $liste.diff}modérée{else}libre{/if}</td>
    <td class='right'>{if $liste.you is odd}désinscription{elseif $liste.ins}ins modérée{else}inscription{/if}</td>
  </tr>
  {/if}
  {/foreach}
</table>

<div class="rubrique">
  Listes d'administration
</div>

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Liste</th>
    <th>Description</th>
    <th>Diffusion</th>
    <th>Inscription</th>
  </tr>
  {foreach from=$listes item=liste}
  {if $liste.priv > 1}
  <tr class='{cycle values="impair,pair"}'>
    <td>{$liste.list}{if $liste.you>1}*{/if}</td>
    <td>{$liste.desc}</td>
    <td class='center'>{if $liste.diff}modérée{else}libre{/if}</td>
    <td class='right'>{if $liste.you is odd}désinscription{elseif $liste.ins}ins modérée{else}inscription{/if}</td>
  </tr>
  {/if}
  {/foreach}
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
