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
<config
grandOncleVisibility="-1"
tooltipSize="0"
tooltipDX="0"
tooltipDY="20"
panelSize="28"
citySize="6"
radius100="1.6"
radius0="3"
select="aim"
aimTween="5"
transition="2"
textLookInCity="Rechercher les x dans cette ville"
textZoomIn="Zoom"
textGoTo="Va à "
textZoomBackTo="Revient à "
textLoading="Chargement"
textYouAreIn="Tu es dans "
textSeeMapOfCity="Voir la carte de "
textYouHaveSelected="Tu as sélectionné "
{if $background}
background="{$background}"
zoomBarBackgroundColor="{$background}"
{/if} 
textCopyright="Les règles de l'annuaire s'appliquent aussi à cette application"
autofolder="true"
iconSwf="icon.swf"
scriptInfosArea="country?{$querystring}"/>
