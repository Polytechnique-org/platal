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

<h2><a href="fusionax">Fusion des annuaires X.org - AX</a> / formations</h2>

<p>
  Il y a {$missingEducationCount} université{if $missingEducationCount > 1}s{/if} manquante{if $missingEducationCount > 1}s{/if} dans
  notre base{if $missingEducationCount eq 0}.</p>{else}&nbsp;:
</p>
<ul>
  {iterate from=$missingEducation item=education}<li>{$education.Intitule_formation}</li>{/iterate}
</ul>{/if}

<p>
  Il y a {$missingDegreeCount} diplôme{if $missingDegreeCount > 1}s{/if} manquant{if $missingDegreeCount > 1}s{/if} dans
  notre base{if $missingDegreeCount eq 0}.</p>{else}&nbsp;:
</p>
<ul>
  {iterate from=$missingDegree item=degree}<li>{$degree.Intitule_diplome}</li>{/iterate}
</ul>{/if}

<p>
  Il y a {$missingCoupleCount} couple{if $missingCoupleCount > 1}s{/if} manquant{if $missingCoupleCount > 1}s{/if} dans
  notre base{if $missingCoupleCount eq 0}.</p>{else}&nbsp;:
</p>
<ul>
  {iterate from=$missingCouple item=couple}<li>{$couple.edu}, {$couple.degree} ({$couple.eduid}, {$couple.degreeid})</li>{/iterate}
</ul>{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
