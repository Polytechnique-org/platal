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
        $Id: minifiche.tpl,v 1.9 2004-10-29 13:26:15 x2000habouzit Exp $
 ***************************************************************************}


<div class="contact" {if $inscrit}{min_auth level='cookie'}title="fiche mise à jour le {$c.date|date_format:"%d %b %Y"}"{/min_auth}{/if}>
  <div class="nom">
    {if $c.sexe}&bull;{/if}
    {min_auth level="cookie"}
    {if !$c.dcd && $inscrit}
    <a href="javascript:x()" onclick="popWin('fiche.php?user={$c.forlife}','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=500')">
    {/if}
    {/min_auth}
    {if $c.epouse}{$c.epouse} {$c.prenom}<br />(née {$c.nom}){else}{$c.nom} {$c.prenom}{/if}
    {min_auth level="cookie"}
    {if !$c.dcd && $inscrit}
    </a>
    {/if}
    {/min_auth}
  </div>
  <div class="appli">
    {strip}
    {if $c.iso3166}
    <img src='{"images/"|url}flags/{$c.iso3166}.gif' alt='{$c.nat}' height='14' title='{$c.nat}' />&nbsp;
    {/if}
    (X {$c.promo}
    {if $c.app0text},
      {applis_fmt type=$c.app0type text=$c.app0text url=$c.app0url}
    {/if}
    {if $c.app1text},
      {applis_fmt type=$c.app1type text=$c.app1text url=$c.app1url}
    {/if})
    {if $c.dcd}&nbsp;décédé le {$c.deces}{/if}
    {/strip}
  </div>
  {min_auth level="cookie"}
  {include file="include/minifiche_pvt.tpl"}
  {/min_auth}
  {only_public}
  <div class="long"></div>
  {/only_public}
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
