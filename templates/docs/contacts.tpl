{* $Id: contacts.tpl,v 1.1 2004-01-28 17:19:47 x2000habouzit Exp $ *}

{if !$smarty.request.topic}
<div class="rubrique">
  Contacts
</div>

<table class="bicol" width="95%" align="center" cellspacing="0" cellpadding="4">
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
<p class="normal">
Nous te remercions de bien choisir la rubrique qui est la plus adaptée à ton besoin.
Celà nous permettra d'être les plus efficaces possible et de traiter ta demande au plus vite.
</p>

{elseif $smarty.request.topic eq 1}
<div class="rubrique">
  Je n'arrive pas à m'inscrire sur le site
</div>
<p class="normal">
L'inscription se déroule en <a href="inscrire.php">une étape sur notre site web</a>,
suivie d'une étape de confirmation basée sur l'e-mail que tu as donné.
</p>
<p class="normal">
<b>En cas de problème pour t'enregistrer:</b>
</p>
<ul>
  <li class="item"> <b>Problème d'identification:</b> écris-nous en précisant bien tes prénom,
  nom, nom de mariage, promo, date de naissance, matricule (pour les X des promos plus récentes que 1995 seulement), etc...
  </li>
  <li class="item"><b>Problème avec le site:</b>
  télécharge la dernière version de ton navigateur et réessaie avant de nous écrire.
  </li>
  <li class="item"><b>Tu ne reçois rien par e-mail:</b> réessaie avec un autre email, celui
  que tu utilisais était peut-être en panne ou mal orthographié.
  </li>
</ul>
Pour toute question ou problème relatif à l'inscription, merci
d'utiliser uniquement l'adresse
{mailto address='register@polytechnique.org' encode='hex'}

{elseif $smarty.request.topic eq 2}
<div class="rubrique">
  J'ai perdu mon mot de passe
</div>
<p class="normal">
Il y a deux façons de faire.
</p>
<p class="normal">
La première méthode est automatique, sécurisée et te prendra environ 5 minutes.
Il faut que tu accèdes encore à tes emails en polytechnique.org
pour récupérer tes paramètres par cette méthode.
</p>
<a href="recovery.php"><b>Clique ici pour retrouver un mot de passe.</b></a>
<p class="normal">
La seconde méthode est entièrement manuelle. Pour cette raison, assure-toi de nous fournir
toutes les informations dont nous disposons sur toi dans ta dernière fiche. En particulier :
login, promo, date de naissance, matricule, adresse/téléphone mobile. Les mots de passe sont
réinitialisés environ toutes les deux semaines si tu as été correctement identifié. Ainsi, <b>merci
  d'attendre au minimum deux semaines</b> dans le cas où tu ne reçois pas de réponse à ta première
demande avant de nous réécrire.
</p>
<p class="normal">
L'adresse à utiliser est uniquement <strong>{mailto address='resetpass@polytechnique.org' encode='hex'}</strong>.
</p>

{elseif $smarty.request.topic eq 3}
<div class="rubrique">
  Ca ne marche pas, je ne comprends pas !
</div>
<p class="normal">
Deux solutions, ou bien c'est un bug du site, ce qui est rare mais peut
encore arriver. Ou bien un problème de configuration sur ton ordinateur/réseau
t'empêche d'utiliser correctement le site. Avant de nous écrire,
mets à jour ton navigateur et consulte également <a href="faq.php">notre FAQ</a>. Les réponses sur les
problèmes de connexion y sont toutes traitées.
</p>
<p class="normal">
En cas de problème persistant, tu peux nous écrire à l'adresse
<strong>{mailto address='support@polytechnique.org' encode='hex'}</strong>
</p>

{elseif $smarty.request.topic eq 4}
<div class="rubrique">
  J'ai une amélioration/correction à proposer
</div>
<p class="normal">
Pour toute suggestion concernant la liste des binets, des groupes x, des pays, des formations
complémentaires, écris-nous à l'adresse <strong>{mailto address='support@polytechnique.org' encode='hex'}</strong> :
nous essayerons de les rajouter au plus vite.
</p>
<p class="normal">
Pour les suggestions de fond, nous lisons les emails avec le plus grand
intérêt, mais réservons les changements à des versions ultérieures
du site (c'est à dire qu'il faut attendre quelques semaines avant que
l'innovation proposée, si elle est retenue, apparaisse sur le site).
</p>
<p class="normal">
Merci de nous aider à améliorer la qualité du site Polytechnique.org. Ecris à
<strong>{mailto address='support@polytechnique.org' encode='hex'}</strong>
ou poste un message sur le forum
<a href="<?php echo url('banana/thread.php?group=xorg.m4x.support') ?>">xorg.m4x.support</a>
pour toute idée de développement ou d'amélioration du site.
</p>

{elseif $smarty.request.topic eq 5}
<div class="rubrique">
  Je voudrais ajouter un article dans la newsletter
</div>
<p class="normal">
Par soucis de légèreté, nous devons imposer quelques contraintes sur les annonces de
la newsletter : le texte du message doit faire au plus <b>8 lignes de 68 caractères</b>
(le titre et les éventuels numéros de téléphones / sites web / adresses emails sont en
sus).
</p>
<p class="normal">
Une fois que ton article est prêt et qu'il ne dépasse pas la taille indiquée, il te suffit
de nous le soumettre par email (éviter les pièces jointes) à cette adresse :
<strong>{mailto address='info_nlp@polytechnique.org' encode='hex'}</strong>.
</p>
<p class="normal">
Les anciennes newsletters de <b>Polytechnique.org</b> sont
<b><a href="newsletter.php">archivées</a></b> si tu veux t'en inspirer.
</p>

{elseif $smarty.request.topic eq 6}
<div class="rubrique">
  Je voudrais vous contacter
</div>
<p class="normal">
Polytechnique.org ne s'occupe que de l'Internet. Pour l'annuaire des
Polytechniciens sur papier et d'une manière générale le support papier, merci
de contacter l'Amicale des X à l'adresse <strong>{mailto address='ax@polytechnique.org' encode='hex'}</strong>.
</p>
<p class="normal">
Pour toute demande qui concerne le recrutement de polytechniciens, vous pouvez consulter
<a href="emploi_public.php"><b>la page spécifiquement consacrée à l'emploi sur notre site.</b></a>
</p>
<p class="normal">
Pour toute question n'ayant aucun rapport avec l'utilisation du site, vous pouvez nous contacter à
l'adresse <strong>{mailto address='contact@polytechnique.org' encode='hex'}</strong>.
</p>

{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
