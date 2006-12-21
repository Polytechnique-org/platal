{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>Proposer un article pour la prochaine Lettre mensuelle</h1>

{if $submited}

<p>
ton article a bien été pris en compte.
</p>

<p>
Nous te recontacterons éventuellement (certainement vers la fin du mois) si nous avons des
renseignements à te demander à son sujet !
</p>

{else}


{if $art}

{if !$art->check()}
<p class='erreur'>
L'article que tu as proposé est trop long !<br />
Il te faut te limiter à 8 lignes de 68 caractères.
</p>
{/if}

<form action="nl/submit" method='post'>
  <table class='tinybicol'>
    <tr><th>Version texte</th></tr>
    <tr id='text'>
    <td><pre>{$art->toText()}</pre></td>
    </tr>
    {if $art->check()}
    <tr><th>Version html</th></tr>
    <tr id='html'>
      <td>
        <div class='nl'>
          {$art->toHtml()|smarty:nodefaults}
        </div>
      </td>
    </tr>
    <tr>
      <th>Soumettre</th>
    </tr>
    <tr>
      <td>
        Si tu es content de ton article, tu peux le soumettre.
        Sinon, tu peux continuer à l'éditer en dessous
      </td>
    </tr>
    <tr>
      <td class='center'>
        <input type='hidden' value="{$smarty.request.title}" name='title' />
        <input type='hidden' value="{$art->body()}" name="body" />
        <input type='hidden' value="{$art->append()}" name='append' />
        <input type='submit' name='valid' value='soumettre' />
      </td>
    </tr>
    {/if}
  </table>
</form>

<br />

{/if}

<h2>Proposer un article</h2>

<p>
Tu peux <a href='nl/submit#conseils'>lire les conseils de rédaction</a> avant de proposer ton article.
</p>
<form action="nl/submit" method='post'>
  <table class="bicol" cellpadding="3" cellspacing="0" summary="proposer un article">
    <tr>
      <th colspan='2'>Proposer un article</th>
    </tr>
    <tr class="impair">
        <td class='titre'>Sujet</td>
      <td>
        <input size='60' type='text' value='{$smarty.request.title}' name='title' />
      </td>
    </tr>
    <tr class="pair">
      <td class='titre'>Contenu</td>
      <td>
        <textarea cols="68" rows="8" name='body'>{if $art}{$art->body()}{/if}</textarea>
      </td>
    </tr>
    <tr class="impair">
      <td class='titre'>Ajouts (emails, contacts, tarifs, site web, ...)</td>
      <td>
        <textarea cols="68" rows="3" name='append'>{if $art}{$art->append()}{/if}</textarea>
      </td>
    </tr>
    <tr class='pair'>
      <td colspan='2' class='center'>
        <input type='submit' name='see' value='visualiser' />
      </td>
    </tr>
  </table>
</form>

<a id='conseils'></a>
<h2>Conseils de rédaction</h2>

<h3>Mises en garde</h3>

<p>
<em>La lettre mensuelle est envoyée à la quasi-totalité des X inscrits à polytechnique.org.</em> Cela impose la plus grande vigilance sur son contenu. Pour être publiée, une annonce doit concerner clairement et directement la vie de la communauté polytechnicienne ou une majorité des X.
</p>

<p>
Quand proposer une annonce ?
La lettre mensuelle est envoyée dans les premiers jours de chaque mois.
Nous ne publions pas deux mois de suite des articles annonçant un même événement.
Réfléchis donc bien au moment le plus opportun pour diffuser ton annonce !
</p>

<p>
L'équipe éditoriale relit les articles et les rectifie éventuellement pour préserver l'uniformité de la lettre mensuelle.
C'est plus simple pour nous si l'article respecte déjà les règles suivantes !
</p>

<h3>Longueur</h3>

<p>
Plus un article est court, plus il y a de chances qu'il soit lu !
Nous fixons une longueur maximale à 8 lignes non vides de 68 caractères.
</p>

<p>
S'il y a beaucoup d'informations annexes (programme détaillé, etc.), elles devraient être mises sur une page web, et l'article devrait contenir un lien vers cette page.
</p>

<p>
Les contacts, prix, adresses email, liens web, etc. sont à placer dans la case "Ajouts" ; il devraient prendre une à trois lignes.
</p>

<h3>Mise en forme</h3>

<p>
Il est possible de mettre du texte <strong>en gras</strong>, <em>en italique</em> ou de
<span style="text-decoration: underline">le souligner</span> en l'encadrant avec les caractères spéciaux
*, / et _ respectivement : *en gras*, /en italique/, _souligné_.
Merci de ne pas abuser du formatage !
</p>

<h3>Conventions</h3>

<p>
Voici quelques exemples à respecter pour garantir l'uniformité de la newsletter.
</p>

<ul>
<li>Noms : Jean DUPONT (48)</li>
<li>Date : le 1er avril, le jeudi 14 janvier (il n'est pas utile de préciser l'année)</li>
<li>Heures : 9h30, 19h00</li>
<li>Lieux : Maison des X, 12 rue de Poitiers, 75007 Paris</li>
<li>Site web : http://www.polytechnique.org</li>
<li>E-mail : utiliser de préférence une adresse @polytechnique.org !</li>
<li>Numéro de téléphone : 01 23 45 67 89</li>
</ul>

{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
