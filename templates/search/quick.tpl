{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<h1>Comment faire une recherche&nbsp;?</h1>

<h2>Nom, Prénom, Promotion&hellip;</h2>

<p>
La ligne de recherche ci-dessus accepte non seulement des mélanges de <strong>noms</strong> et de <strong>prénoms</strong>&hellip;
mais elle accepte de plus la syntaxe suivante pour les <strong>promotions</strong>&nbsp;:
</p>
<ul>
  <li><code>1990</code>&nbsp;: signifie appartient à la promotion 1990&nbsp;;</li>
  <li><code>1990-2000</code>&nbsp;: signifie sur les promotions 1990 à 2000&nbsp;;</li>
  <li><code>&lt;1990</code>&nbsp;: signifie promotions antérieures ou égales à 1990&nbsp;;</li>
  <li><code>&gt;1990</code>&nbsp;: signifie promotions postérieures ou égales à 1990.</li>
</ul>
<p>
Ainsi, rechercher tous les "Dupont" sur les promotions 1980 à 1990 et sur la promotion 2000 se fait avec la recherche&nbsp;:
<code>Dupont 1980-1990 2000</code>
</p>

<h2>Astuce pour les noms&hellip;</h2>
<p>
Parfois on ne sait plus si le nom qu'on recherche s'écrit «&nbsp;Lenormand&nbsp;», «&nbsp;Le Normand&nbsp;» ou «&nbsp;Le-Normand&nbsp;»&hellip;
</p>
<p>
Pour éviter ce genre d'écueils, il suffit de chercher&nbsp;: <code>Le Normand</code><br />
En effet, le moteur de recherche va alors chercher tous les utilisateurs dont le nom
contient 'Le' <strong>et</strong> 'Normand' sans distinction de casse et sans tenir compte des accents.
</p>
<p>
Il est conseillé d'omettre les particules car il est possible que celles-ci ne soient pas présentes dans
notre base de données.
</p>

{if hasPerm('user')}
<h2>Raccourcis&hellip;</h2>
<p>
  Un certain nombre de raccourcis permettent d'accéder plus rapidement au contenu du site&nbsp;:
</p>
<ul>
  <li><code>fiche:prenom.nom.promo</code> ouvre la fiche du camarade indiquée&nbsp;;</li>
  <li><code>trombi:promo</code> affiche le trombinoscope de la promotion indiquée&nbsp;;</li>
  <li><code>ref:prenom.nom.promo</code> ouvre la fiche référent du camarade indiquée&nbsp;;</li>
  <li><code>doc:phrase</code> recherche <em>phrase</em> dans la documentation du site&nbsp;;</li>
  {if hasPerm('admin')}
  <li><code>admin:prenom.nom.promo</code> ouvre la fiche d'administration du camarade indiquée&nbsp;;</li>
  <li><code>ax:prenom.nom.promo</code> ouvre la fiche ax du camarade concerné.</li>
  {/if}
</ul>

<p>
  Ces raccourcis fonctionnement également depuis le lien de recherche rapide disponible sur toutes les pages
  du site<span class="searchbar"> et depuis la barre de recherche de ton navigateur si tu installes le module
  ci-dessous</span>.
</p>

<div class="searchbar">
<h2>Barre de recherche pour ton navigateur</h2>
<p>
  Si tu utilises un navigateur moderne tel que Firefox ou Internet Explorer 7, tu peux ajouter un module de recherche
  directement dans ton navigateur. Pour ceci, <a href="javascript:addSearchEngine()">installe</a> la barre de recherche
  rapide.
</p>
</div>

<script type="text/javascript">//<![CDATA[
  {literal}
  if (!canAddSearchEngine()) {
    $(".searchbar").hide();
  }
  {/literal}
//]]></script>
{/if}

<h2>Polytechniciens des promotions 1920 et précédentes</h2>
<p>Notre base de données ne contient que les polytechniciens depuis la promotion 1921. Pour effectuer des recherches dans les
promotions précédentes, il faut utiliser l'<a href="http://bibli.polytechnique.fr/F/?func=file&amp;file_name=find-b&amp;local_base=BCXC2">annuaire en ligne de la bibliothèque de l'École</a>.</p>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
