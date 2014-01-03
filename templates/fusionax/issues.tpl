{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

<h2>Fusion des annuaires X.org - AX</h2>

{if $issues.total > 0}
<p>
  Il reste {$issues.total} problème{if $issues.total > 1}s{/if} du{if $issues.total > 1}s{/if} à la fusion des annuaires à corriger sur les profils&nbsp;:
</p>
<ul>
  {foreach from=$issueList key=issue item=name}
  {assign var=issueNb value=$issues.$issue}
  {if $issueNb > 0}<li>{$issueNb} erreur{if $issueNb > 1}s{/if} sur les <a href="fusionax/issues/{$issue}">{$name}</a></li>{/if}
  {/foreach}
</ul>
{else}
<p>Il ne reste plus d'erreur liée à la fusion des annuaires&nbsp;!</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
