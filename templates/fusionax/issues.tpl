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

<h2>Fusion des annuaires X.org - AX</h2>

{if $issues.total > 0}
<p>
  Il reste les problèmes suivants dus à la fusion des annuaires à corriger sur les profils&nbsp;:
</p>
<ul>
  {if $issues.deathdate > 0}<li>{$issues.deathdate} erreur{if $issues.deathdate > 1}s{/if} sur les <a href="fusionax/deathdate_issues">dates de décès</a></li>{/if}
  {if $issues.promo > 0}<li>{$issues.promo} erreur{if $issues.promo > 1}s{/if} sur les <a href="fusionax/promo_issues">promotions</a></li>{/if}
  {if $issues.name > 0}<li>{$issues.name} erreur{if $issues.name > 1}s{/if} sur les <a href="fusionax/name_issues">noms</a></li>{/if}
  {if $issues.phone > 0}<li>{$issues.phone} erreur{if $issues.phone > 1}s{/if} sur les <a href="fusionax/phone_issues">téléphones</a></li>{/if}
  {if $issues.education > 0}<li>{$issues.education} erreur{if $issues.education > 1}s{/if} sur les <a href="fusionax/education_issues">formations</a></li>{/if}
  {if $issues.address > 0}<li>{$issues.address} erreur{if $issues.address > 1}s{/if} sur les <a href="fusionax/address_issues">adresses</a></li>{/if}
  {if $issues.job > 0}<li>{$issues.job} erreur{if $issues.job > 1}s{/if} sur les <a href="fusionax/job_issues">emplois</a></li>{/if}
</ul>
{else}
<p>Il ne reste plus d'erreurs liées à la fusion des annuaires&nbsp;!</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
