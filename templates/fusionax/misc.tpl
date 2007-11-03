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

<h2><a href="fusionax">Fusion des annuaires X.org - AX<a> / Divers</h2>

<h3>Décès</h3>

{if $deceasedErrors}

<p>Voici les {$deceasedErrors} différences entre les deux annuaires pour les renseignements de
décès.</p>

<p>Anciens déclarés décédés dans l'annuaire AX mais pas sur Xorg</p>

{include file='fusionax/listFusion.tpl' fusionList=$deceasedMissingInXorg field1='Date_décès' namefield1='Décès AX'}

<p>Anciens déclarés décédés dans l'annuaire Xorg mais pas chez l'AX</p>

{include file='fusionax/listFusion.tpl' fusionList=$deceasedMissingInAX field1='deces' namefield1='Décès X.org'}

<p>Anciens déclarés décédés dans les deux annuaires mais pas avec la même date</p>

{include file='fusionax/listFusion.tpl' fusionList=$deceasedDifferent field1='deces' field2='Date_décès' namefield1='Décès X.org' namefield2='Décès AX'}

{else}

<p>Aucune différence pour les renseignements de décès entre les deux annuaires.</p>

{/if}
