{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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


{if !$smarty.request.topic}
<h1>
  Contacts
</h1>

<table class="bicol" cellspacing="0" cellpadding="4">
  <tr>
    <th>
      Merci de choisir une rubrique parmi les suivantes.
    </th>
  </tr>
  <tr class="impair">
    <td>
      <a href="contacts.php?topic=1">1) Je n'arrive pas à m'inscrire sur le site</a>
    </td>
  </tr>
  <tr class="pair">
    <td style="border-bottom: 1px solid inherit">
      <a href="contacts.php?topic=2">2) J'ai perdu mon mot de passe</a>
    </td>
  </tr>
  <tr class="impair">
    <td>
      <a href="contacts.php?topic=3">3) Ca ne marche pas, je ne comprends pas !</a>
    </td>
  </tr>
  <tr class="pair">
    <td style="border-bottom: 1px solid inherit">
      <a href="contacts.php?topic=4">4) J'ai une amélioration/correction à proposer</a>
    </td>
  </tr>
  <tr class="impair">
    <td>
      <a href="contacts.php?topic=5">5) Je voudrais ajouter un article dans la newsletter</a>
    </td>
  </tr>
  <tr class="pair">
    <td>
      <a href="contacts.php?topic=6">6) Je voudrais vous contacter</a>
    </td>
  </tr>
</table>
<p>
Nous vous remercions de bien choisir la rubrique qui est la plus adaptée à votre besoin.
Cela nous permettra d'être les plus efficaces possible et de traiter votre demande au plus vite.
</p>

{elseif $smarty.request.topic eq 1}
<h1>
  Je n'arrive pas à m'inscrire sur le site
</h1>
<p>
L'inscription se déroule en <a href="{rel}/register/">une étape sur notre site web</a>,
suivie d'une étape de confirmation basée sur l'e-mail que tu as donné.
</p>
<p>
<strong>En cas de problème pour vous enregistrer:</strong>
</p>
<ul>
  <li class="item"> <strong>Problème d'identification:</strong> écrivez-nous en précisant bien vos prénom,
  nom, nom de mariage, promo, date de naissance, matricule (pour les X des promos plus récentes que 1995 seulement), etc...
  </li>
  <li class="item"><strong>Problème avec le site:</strong>
  téléchargez la dernière version de votre navigateur et réessayez avant de nous écrire.
  </li>
  <li class="item"><strong>Vous ne recevez rien par e-mail:</strong> réessayez avec un autre email, celui
  que vous utilisiez était peut-être en panne ou mal orthographié.
  </li>
</ul>
Pour toute question ou problème relatif à l'inscription, merci
d'utiliser uniquement l'adresse
{mailto address='register@polytechnique.org' encode='hex'}

{elseif $smarty.request.topic eq 2}
<h1>
  J'ai perdu mon mot de passe
</h1>
<p>
Il y a deux façons de faire.
</p>
<p>
La première méthode est automatique, sécurisée et vous prendra environ 5 minutes.
Il faut que vous accèdiez encore à vos emails en polytechnique.org
pour récupérer vos paramètres par cette méthode.
</p>
<a href="{"recovery.php"|url}"><strong>Cliquez ici pour retrouver un mot de passe.</strong></a>
<p>
La seconde méthode est entièrement manuelle. Pour cette raison, assurez-vous de nous fournir
toutes les informations dont nous disposons sur vous dans votre dernière fiche. En particulier :
login, promo, date de naissance, matricule, adresse/téléphone mobile. Les mots de passe sont
réinitialisés environ toutes les deux semaines si vous avez été correctement identifié. Ainsi, <strong>merci
  d'attendre au minimum deux semaines</strong> dans le cas où vous ne receviez pas de réponse à votre première
demande avant de nous réécrire.
</p>
<p>
L'adresse à utiliser est uniquement <strong>{mailto address='resetpass@polytechnique.org' encode='hex'}</strong>.
</p>

{elseif $smarty.request.topic eq 3}
<h1>
  Ca ne marche pas, je ne comprends pas !
</h1>
<p>
Deux solutions, ou bien c'est un bug du site, ce qui est rare mais peut
encore arriver. Ou bien un problème de configuration sur votre ordinateur/réseau
vous empêche d'utiliser correctement le site. Avant de nous écrire,
mettez à jour votre navigateur et consultez également <a href="faq.php">notre FAQ</a>. Les réponses sur les
problèmes de connexion y sont toutes traitées.
</p>
<p>
En cas de problème persistant, vous pouvez nous écrire à l'adresse
<strong>{mailto address='support@polytechnique.org' encode='hex'}</strong>
</p>

{elseif $smarty.request.topic eq 4}
<h1>
  J'ai une amélioration/correction à proposer
</h1>
<p>
Pour toute suggestion concernant la liste des binets, des groupes x, des pays, des formations
complémentaires, écrivez-nous à l'adresse <strong>{mailto address='support@polytechnique.org' encode='hex'}</strong> :
nous essayerons de les rajouter au plus vite.
</p>
<p>
Pour les suggestions de fond, nous lisons les emails avec le plus grand
intérêt, mais réservons les changements à des versions ultérieures
du site (c'est à dire qu'il faut attendre quelques semaines avant que
l'innovation proposée, si elle est retenue, apparaisse sur le site).
</p>
<p>
Merci de nous aider à améliorer la qualité du site Polytechnique.org. Ecrivez à
<strong>{mailto address='support@polytechnique.org' encode='hex'}</strong>
ou postez un message sur le forum
<a href="../banana/?group=xorg.m4x.support">xorg.m4x.support</a>
pour toute idée de développement ou d'amélioration du site.
</p>

{elseif $smarty.request.topic eq 5}
<h1>
  Je voudrais ajouter un article dans la newsletter
</h1>
<p>
Par soucis de légèreté, nous devons imposer quelques contraintes sur les annonces de
la newsletter : le texte du message doit faire au plus <strong>8 lignes de 68 caractères</strong>
(le titre et les éventuels numéros de téléphones / sites web / adresses emails sont en
sus).
</p>
<p>
Un formulaire en ligne vous permet de nous soumettre un article, et se chargera de vérifier
ces contraintes pour vous : <a href='{"newsletter/submit.php"|url}'>soumission d'article</a>.
</p>
<p>
Sinon, une fois que votre article est prêt et qu'il ne dépasse pas la taille indiquée,
vous pouvez aussi nous le soumettre par email (éviter les pièces jointes) à cette adresse :
<strong>{mailto address='info_nlp@polytechnique.org' encode='hex'}</strong>.
</p>
<p>
Les anciennes newsletters de <strong>Polytechnique.org</strong> sont
<strong><a href="{"newsletter/"|url}">archivées</a></strong> si vous voulez vous en inspirer.
</p>

{elseif $smarty.request.topic eq 6}
<h1>
  Je voudrais vous contacter
</h1>
<p>
Polytechnique.org ne s'occupe que de l'Internet. Pour l'annuaire des
Polytechniciens sur papier et d'une manière générale le support papier, merci
de contacter l'Amicale des X à l'adresse <strong>{mailto address='info@amicale.polytechnique.org' encode='hex'}</strong>.
</p>
<p>
Pour toute demande qui concerne le recrutement de polytechniciens, vous pouvez consulter
<a href="http://www.manageurs.com/?langue=fr"><strong>le site Manageurs.com, dédié aux problématique d'emploi et de gestion de carrière.</strong></a>
</p>
<p>
Pour toute question n'ayant aucun rapport avec l'utilisation du site, vous pouvez nous contacter à
l'adresse <strong>{mailto address='contact@polytechnique.org' encode='hex'}</strong>.
</p>

{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
