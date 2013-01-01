{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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

<h2>Fusion des annuaires X.org - AX&nbsp;: {$issue}</h2>

{if $total > 0}
<p>
  Il reste {$total} problème{if $total > 1}s{/if} du{if $total > 1}s{/if} à la
  fusion des annuaires lié{if $total > 1}s{/if} aux {$issue} à corriger sur les profils&nbsp;:
</p>
<ul>
  {foreach from=$issues item=profile}
  <li><a href="profile/edit/{$profile.hrpid}/{$type}">{$profile.directory_name} ({$profile.promo})</a></li>
  {/foreach}
</ul>
{else}
<p>Il ne reste plus d'erreur liée à la fusion des annuaires concernant les {$issue}&nbsp;!</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
