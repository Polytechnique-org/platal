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

<h2><a href="fusionax">Fusion des annuaires X.org - AX</a> / Promotions</h2>

<p></p>

{if $nbMissmatchingPromosTotal > 0}
<p>Il y a {$nbMissmatchingPromosTotal} différences entre les deux bases dans pour les promotions.</p>

<p>Grosses différences ({$nbMissmatchingPromos} camarades) :</p>
{include file='fusionax/listFusion.tpl' fusionList=$missmatchingPromos field1='pid' namefield1='ID X.org' field3='promo_etude_xorg'
namefield3='etude_xorg' field4='promo_sortie_xorg' namefield4='sortie_xorg' field2='promo_etude_ax' namefield2='etude_ax'}

<p>Petites différences (oranjisation) ({$nbMissmatchingPromos1} camarades) :</p>
{include file='fusionax/listFusion.tpl' fusionList=$missmatchingPromos1 field1='pid' namefield1='ID X.org' field3='promo_etude_xorg'
namefield3='etude_xorg' field4='promo_sortie_xorg' namefield4='sortie_xorg' field2='promo_etude_ax' namefield2='etude_ax'}

<p>Petites différences (oranjisation + étrangers) ({$nbMissmatchingPromos3} camarades) :</p>
{include file='fusionax/listFusion.tpl' fusionList=$missmatchingPromos3 field1='pid' namefield1='ID X.org' field3='promo_etude_xorg'
namefield3='etude_xorg' field4='promo_sortie_xorg' namefield4='sortie_xorg' field2='promo_etude_ax' namefield2='etude_ax'}

<p>Petites différences : (étrangers mal inclus) ({$nbMissmatchingPromos2} camarades)</p>
{include file='fusionax/listFusion.tpl' fusionList=$missmatchingPromos2 field1='pid' namefield1='ID X.org' field3='promo_etude_xorg'
namefield3='etude_xorg' field4='promo_sortie_xorg' namefield4='sortie_xorg' field2='promo_etude_ax' namefield2='etude_ax'}

<p>Masters : ({$nbMissmatchingPromosM} personnes)</p>
{include file='fusionax/listFusion.tpl' fusionList=$missmatchingPromosM field1='pid' namefield1='ID X.org' field3='promo_etude_xorg'
namefield3='etude_xorg' field4='promo_sortie_xorg' namefield4='sortie_xorg' field2='promo_etude_ax' namefield2='etude_ax'}

<p>Docteurs : ({$nbMissmatchingPromosD} personnes)</p>
{include file='fusionax/listFusion.tpl' fusionList=$missmatchingPromosD field1='pid' namefield1='ID X.org' field3='promo_etude_xorg'
namefield3='etude_xorg' field4='promo_sortie_xorg' namefield4='sortie_xorg' field2='promo_etude_ax' namefield2='etude_ax'}
{/if}
