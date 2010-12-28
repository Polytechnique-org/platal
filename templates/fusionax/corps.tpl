{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

<h2><a href="fusionax">Fusion des annuaires X.org - AX</a> / corps</h2>

<p>
  Il y a {$missingCorpsCount} corps manquant{if $missingCorpsCount > 1}s{/if} dans notre base{if $missingCorpsCount eq 0}.</p>{else}&nbsp;:
</p>
<ul>
  {iterate from=$missingCorps item=corps}<li>{$corps.name}</li>{/iterate}
</ul>{/if}

<p>
  Il y a {$missingGradeCount} grade{if $missingGradeCount > 1}s{/if} manquant{if $missingGradeCount > 1}s{/if} dans
  notre base{if $missingGradeCount eq 0}.</p>{else}&nbsp;:
</p>
<ul>
  {iterate from=$missingGrade item=grade}<li>{$grade.name}</li>{/iterate}
</ul>{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
