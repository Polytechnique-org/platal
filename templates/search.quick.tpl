{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: search.quick.tpl,v 1.1 2004-11-04 13:50:45 x2000habouzit Exp $
 ***************************************************************************}

{include file="search.quick.form.tpl"}

<h1>Comment faire une recherche ?</h1>

<h2>Nom, Prenom, Promo ...</h2>

<p>
La ligne de recherche ci-dessus accepte non seulement que des mélanges de <strong>noms</strong> et de <strong>prénoms</strong> ...
mais elle accepte de plus la syntaxe suivante pour les <strong>promos</strong> :
</p>
<ul>
  <li>1990 : signifie appartient à la promo 1990</li>
  <li>1990-2000 : signifie sur la promo 1990 à 2000</li>
  <li>&lt;1990 : signifie promos inférieures ou égales à 1990</li>
  <li>&gt;1990 : signifie promos supérieures ou égales à 1990</li>
</ul>
<p>
Ainsi, rechercher tous les "Pierre" sur les promos 1980 à 1990 et sur la promo 2000 se fait avec la recherche :
[ Pierre 1980-1990 2000 ]
</p>

<h2>Astuce pour les noms ...</h2>
<p>
Parfois on ne sait plus si le nom qu'on recherche s'écrit « Lenormand », « Le Normand » ou « Le-Normand » ...
</p>
<p>
Pour éviter ce genre d'écueils, il suffit de chercher [ Le Normand ].<br />
En effet, le moteur de recherche va alors chercher tous les utilisateurs dont le nom contient 'Le' <strong>et</strong> 'Normand'
sans distinction de casse et sans tenir compte des accents.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
