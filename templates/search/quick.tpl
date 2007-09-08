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

{if $smarty.session.perms->hasFlag('user')}
<h1>Voir le trombi d'une promotion</h1>

<div id="message" style="position:absolute;"></div><br />

<script type="text/javascript">
  <!--
  {literal}
  function showPromo()
  {
      var value = document.getElementById('promo').value;
      if (value < {/literal}{$promo_min}{literal} || value > {/literal}{$promo_max}{literal}) {
        showTempMessage('message', "La promotion doit être entre {/literal}{$promo_min} et {$promo_max}{literal}.", false);
        return false;
      }
      window.open("http://www.polytechnique.net/login/" + value + "/annuaire/trombi");
      return false;
  }
  {/literal}
  -->
</script>

<form action="" method="post" onsubmit="return showPromo();">
<table class="tinybicol" style="width: 35%; margin-right: auto; margin-left: auto">
  <tr>
    <td class="titre">Promotion&nbsp;:</td>
    <td>
      <input type="text" name="promo" id="promo" size="4" value="" />
      <input type="submit" name="submit_promo" value="Voir" />
    </td>
  </tr>
</table>
</form>

{/if}

<h1>Comment faire une recherche ?</h1>

<h2>Nom, Prénom, Promotion...</h2>

<p>
La ligne de recherche ci-dessus accepte non seulement des mélanges de <strong>noms</strong> et de <strong>prénoms</strong>...
mais elle accepte de plus la syntaxe suivante pour les <strong>promotions</strong> :
</p>
<ul>
  <li><code>1990</code> : signifie appartient à la promotion 1990&nbsp;;</li>
  <li><code>1990-2000</code> : signifie sur la promotion 1990 à 2000&nbsp;;</li>
  <li><code>&lt;1990</code> : signifie promotions inférieures ou égales à 1990&nbsp;;</li>
  <li><code>&gt;1990</code> : signifie promotions supérieures ou égales à 1990.</li>
</ul>
<p>
Ainsi, rechercher tous les "Dupont" sur les promotions 1980 à 1990 et sur la promotion 2000 se fait avec la recherche :
<code>Dupont 1980-1990 2000</code>
</p>

<h2>Astuce pour les noms...</h2>
<p>
Parfois on ne sait plus si le nom qu'on recherche s'écrit « Lenormand », « Le Normand » ou « Le-Normand »...
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

{if $smarty.session.perms->hasFlag('user')}
<h2>Raccourcis...</h2>
<p>
  Un certain nombre de raccourcis permettent d'accéder plus rapidement au contenu du site :
  <ul>
    <li><code>fiche:prenom.nom.promo</code> ouvre la fiche du camarade indiquée&nbsp;;</li>
    <li><code>ref:prenom.nom.promo</code> ouvre la fiche référent du camarade indiquée&nbsp;;</li>
    <li><code>doc:phrase</code> recherche <em>phrase</em> dans la documentation du site&nbsp;;</li>
    {if $smarty.session.perms->hasFlag('admin')}
    <li><code>admin:prenom.nom.promo</code> ouvre la fiche d'administration du camarade indiquée&nbsp;;</li>
    <li><code>ax:prenom.nom.promo</code> ouvre la fiche ax du camarade concerné.</li>
    {/if}
  </ul>
</p>

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

{javascript name=jquery}
<script type="text/javascript">//<![CDATA[
  {literal}
  if (!canAddSearchEngine()) {
    $(".searchbar").hide();
  }
  {/literal}
//]]></script>
{/if}

<h2>Polytechniciens des promotions 1919 et précédentes</h2>
<p>Notre base de données ne contient que les polytechniciens depuis la promotion 1920. Pour effectuer des recherches dans les
promotions précédentes, il faut utiliser l'<a href="http://bibli.polytechnique.fr/F/?func=file&amp;file_name=find-b&amp;local_base=BCX2">annuaire en ligne de la bibliothèque de
l'École</a>.</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
