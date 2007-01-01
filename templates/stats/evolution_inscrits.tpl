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

<h1>Evolution du nombre d'inscrits au site polytechnique.org</h1>

<div class="center">
{if $jours eq 1825}
[<strong>depuis 5 ans</strong>]
{else}
[<a href="stats/evolution/1825">depuis 5 ans</a>]
{/if}
{if $jours eq 730}
[<strong>depuis 2 ans</strong>]
{else}
[<a href="stats/evolution/730">depuis 2 ans</a>]
{/if}
{if $jours eq 365}
[<strong>depuis un an</strong>]
{else}
[<a href="stats/evolution/365">depuis un an</a>]
{/if}
{if $jours eq 30}
[<strong>depuis un mois</strong>]
{else}
[<a href="stats/evolution/30">depuis 1 mois</a>]
{/if}
</div>
<div class="center">
  <img src="stats/graph/evolution/{$jours}" alt=" [ INSCRITS ] " />
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
