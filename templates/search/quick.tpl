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

{include file=search/quick.form.tpl show_js=1}
<h1>Comment faire une recherche ?</h1>

<h2>Nom, Prenom, Promo ...</h2>

<p>
La ligne de recherche ci-dessus accepte non seulement des mélanges de <strong>noms</strong> et de <strong>prénoms</strong> ...
mais elle accepte de plus la syntaxe suivante pour les <strong>promos</strong> :
</p>
<ul>
  <li><code>1990</code> : signifie appartient à la promo 1990</li>
  <li><code>1990-2000</code> : signifie sur la promo 1990 à 2000</li>
  <li><code>&lt;1990</code> : signifie promos inférieures ou égales à 1990</li>
  <li><code>&gt;1990</code> : signifie promos supérieures ou égales à 1990</li>
</ul>
<p>
Ainsi, rechercher tous les "Dupont" sur les promos 1980 à 1990 et sur la promo 2000 se fait avec la recherche :
<code>Dupont 1980-1990 2000</code>
</p>

<h2>Astuce pour les noms ...</h2>
<p>
Parfois on ne sait plus si le nom qu'on recherche s'écrit « Lenormand », « Le Normand » ou « Le-Normand » ...
</p>
<p>
Pour éviter ce genre d'écueils, il suffit de chercher : <code>Le Normand</code><br />
En effet, le moteur de recherche va alors chercher tous les utilisateurs dont le nom 
contient 'Le' <strong>et</strong> 'Normand' sans distinction de casse et sans tenir compte des accents.
</p>
<p>
Il est conseillé d'omettre les particules car il est possible que celles-ci ne soient pas présentes dans
notre base de données.
</p>

<div id="searchbar" style="display: none">
<h2>Barre de recherche pour ton navigateur</h2>
<p>
  Si tu utilises un navigateur moderne tel que Firefox ou Internet Explore 7, tu peux ajouter un module de recherche
  directement dans ton navigateur. Pour ceci, <a href="javascript:addSearchEngine()">installe</a> la barre de recherche
  rapide.
</p>
</div>

<script type="text/javascript">//<![CDATA[
  {literal}
  if (canAddSearchEngine()) {
    document.getElementById('searchbar').style.display = '';
  }
  {/literal}
//]]></script>

<h2>Polytechniciens des promotions 1919 et précédentes</h2>
<p>Notre base de données ne contient que les polytechniciens depuis la promotion 1920. Pour effectuer des recherches dans les
promotions précédentes, il faut utiliser l'<a href="http://biblio.polytechnique.fr/F/">annuaire en ligne de la bibliothèque de
l'École</a>.</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
