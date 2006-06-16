{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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


<h1>
  Evolution du nombre d'inscrits au site polytechnique.org
</h1>

<div class="center">
{if $smarty.request.jours eq 1826}
[<strong>depuis 5 ans</strong>]
{else}
[<a href="?jours=1826">depuis 5 ans</a>]
{/if}
{if $smarty.request.jours eq 731}
[<strong>depuis 2 ans</strong>]
{else}
[<a href="?jours=731">depuis 2 ans</a>]
{/if}
{if (!$smarty.request.jours) or ($smarty.request.jours eq 364)}
[<strong>depuis un an</strong>]
{else}
[<a href="?jours=364">depuis un an</a>]
{/if}
{if $smarty.request.jours eq 30}
[<strong>depuis un mois</strong>]
{else}
[<a href="?jours=30">depuis 1 mois</a>]
{/if}
</div>
<div class="center">
  <img src="{"stats/graph_evolution.php?jours="|url}{$smarty.request.jours}" alt=" [ INSCRITS ] " />
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
