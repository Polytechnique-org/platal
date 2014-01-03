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

<p>L'adresse saisie ci-dessous sert uniquement pour la géolocalisation ;
utiliser le bouton <em>Éditer</em> pour visualiser sur la carte la nouvelle position de l'adresse.</p>
<p>La case <em>Utiliser la version modifiée</em> va remplacer l'adresse saisie par l'utilisateur par l'adresse modifiée ci-dessous.
À n'utiliser qu'en cas d'adresse manifestement invalide, ou pour corriger une faute d'orthographe.</p>

{include file="geoloc/form.address.tpl" prefname="valid" prefid=0 address=$valid->address validation=1}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
