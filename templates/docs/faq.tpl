{* $Id: faq.tpl,v 1.4 2004-02-11 13:15:34 x2000habouzit Exp $ *}

<div class="rubrique">
  Foire aux questions
</div>
<p class="normal">
  Cette rubrique est sans doute incomplète. N'hésite pas à nous demander
  de la compléter sur un sujet en rapport avec le site si tu estimes
  que des informations manquent.
</p>
<hr />
<div class="ssrubrique">
  Questions générales
</div>
<ul>
  <li> 
    <a href="#pop">Comment récupérer mon courrier sur polytechnique.org ?</a>
  </li>
  <li>
    <a href="#smtp">Comment envoyer mon courrier avec comme champ expéditeur 
    (From) mon adresse en polytechnique.org ?</a>
  </li>
  <li>
    <a href="#nntp">Comment lire les forums avec mon logiciel de courrier éléctronique ?</a> 
  </li>
  <li>
    <a href="#carva">Quelle est l'origine du nom de domaine carva.org ?</a>
  </li>
</ul>
<div class="ssrubrique">
  Remplissage des champs
</div>
<ul>
  <li> 
    <a href="#mails1">Quelle est la différence entre les mails promo, emploi, 
    et les autres mails collectifs ?</a>
  </li>
  <li>
    <a href="#flags">Quelle est la différence entre les cases de visibilité vert ou orange ?</a>
  </li>
  <li>
    <a href="#niveau_langue">A quoi correspondent les niveaux de langues ?</a>
  </li>
  <li>
    <a href="#cv">Faut-il remplir le CV et comment ?</a>
  </li>
</ul>
<div class="ssrubrique">
  Problèmes de connexion
</div>
<ul>
  <li> 
    <a href="#config">Quels sont les paramètres et la configuration nécessaires 
    pour se connecter correctement ?</a>
  </li>
  <li>
    <a href="#passe">J'ai perdu mon mot de passe, que faire ?</a>
  </li>
  <li>
    <a href="#acces">Je n'arrive pas à me connecter ! Que faut-il essayer ?</a>
  </li>
</ul>
<div class="ssrubrique">
  Utilisation post-connexion
</div>
<ul>
  <li>
    <a href="#ethique">Quelle est l'éthique que vous privilégiez pour les mails 
    collectifs ?</a>
  </li>
  <li>
    <a href="#mails2">Puis-je envoyer un mail à des X et comment ?</a>
  </li>
  <li>
    <a href="#secu">Puis-je utiliser le même mot de passe qu'ailleurs ?</a>
  </li>
  <li>
    <a href="#secu2">Quel est le niveau de sécurité de Polytechnique.org ?</a>
  </li>
  <li>
    <a href="#panne">Vous tombez souvent en panne ?</a>
  </li>
</ul>
<br /><br />
<hr />
<div class="ssrubrique">
  Questions générales
</div>
<a id="pop" name="pop"></a>
<div class="sstitre">
  Comment récupérer mon courrier sur polytechnique.org ?
</div>
<div class="explication">
  <p>
    Les mails envoyés sur polytechnique.org sont redirigés vers la ou les adresses e-mails 
    que tu as précisée(s) dans ton profil (premier sous-menu dans "Mes Paramètres"). Tu 
    dois donc récupérer ton courrier sur cette (ces) adresse(s) comme tu en as l'habitude, 
    aucun changement n'est introduit par l'existence de ton adresse sur polytechnique.org.
  </p>
</div>   
<a id="smtp" name="smtp"></a> 
<div class="sstitre">
  Comment envoyer mon courrier avec comme champ expéditeur (From) mon adresse en 
  polytechnique.org ?
</div>
<div class="explication">
  <p>
    Normalement, il suffit de <a href="<?php echo url("docs/doc_emails.php")?>">configurer son identité</a>
    avec l'adresse en polytechnique.org, mais certains serveurs SMTP
    (la première machine qui prend en charge l'expédition du courrier)
    refusent que le champ From contienne une adresse quelconque
    (c'est le cas de ifrance, dont le serveur smtp.ifrance.com n'accepte que
    les champs contenant une adresse @ifrance.com). Dans ce cas, tu peux utiliser <a 
    href="<?php echo url("docs/doc_smtp.php")?>">le serveur SMTP de polytechnique.org</a>. 
    Quand tu n'as pas accès au un logiciel de courrier électronique, tu peux aussi 
    utiliser <a href="<?php echo url("sendmail.php")?>">cette page</a> pour envoyer un petit courriel.
  </p>
</div>

<a id="nntp" name="nntp"></a> 
<div class="sstitre">
  Comment lire les forums avec mon logiciel de courrier électronique ?
</div>
<div class="explication">
  <p>
    En complément de l'interface web il t'est possible d'accéder aux forums
    de Polytechnique.org directement depuis ton logiciel de courrier
    électronique.  Les explications se trouvent
    <a href="<?php echo url("docs/doc_nntp.php")?>">ici</a>.
  </p>
</div>

<a id="carva" name="carva"></a>
<div class="sstitre">
  Quelle est l'origine du nom de domaine carva.org ?
</div>
<div class="explication">
  <p>
    Dans le jargon de l'école, un 'carva' signifiait un 'X' lorsque celle-ci était 
    sur la montagne Ste Geneviève. <br /><br />
  </p>
  <p>
    <strong>Définition de Carva:</strong>
  </p>
  <ul>
    <li> 
      Mod. Ecole polytechnique, ou, n. Polytechnique (argot: l'X, Pipo, et, pour 
      les élèves Carva), nom donné en 1795 à l'école créée en 1794 pour former 
      les ingénieurs des divers services de l'Etat (mines, ponts et chaussées...) 
      et les officiers de certain armes (artillerie, génie...); Pépinière, cit. 2, 
      Balzac). Préparation à Polytechnique.
    </li>
    <li>
      Taupe. Elève ancien élève de Polytechnique. Promotions ("rouge" et "jaune") 
      de Polytechnique; Polytechnique et Normale (® Elite, cit.)
    </li>
    <li>
      Sortir de Polytechnique. 6. Botte, 2. bottier. 1. Je n'ose confier qu'à vous 
      le secret de sa nullité, abritée par le renom de l'Ecole Polytechnique. 
      (Balzac, Le Curé de village, Pl. t. VIII, p. 695).
    </li>
  </ul>
  <p>
    (Dictionnaire de la langue française, Le Robert, Paris 1987)
  </p>
</div>
<hr />
<div class="ssrubrique">
  Remplissage des champs
</div>
<a id="mails1" name="mails1"></a> 
<div class="sstitre">
  Quelle est la différence entre les mails promo, emploi, et les autres mails collectifs ?
</div>
<div class="explication">
  <p>
    Les mails promo concernent des événements promo, réunion, informations sur les 
    cocons, bref la vie d'une promo. Il y a seulement quelques personnes par promo qui 
    peuvent les envoyer, pour éviter que chacun le fasse de son côté. Le mieux si tu 
    souhaites envoyer une information à toute ta promo, est de passer par un kessier 
    ou un responsable du web de ta promo, qui se chargera éventuellement de nous demander 
    un envoi propre à tous les inscrits ayant accepté les mails promo dans leur profil.
  </p>
  <p>
    Les mails emplois sont assez mal définis à l'heure actuelle. Ca peut aller de 
    proposition d'embauche ou de stage venant de camarades ou d'entreprises, jusqu'à 
    présentation d'entreprises. Dans la mesure du possible, ces mails seront dirigés 
    vers les mailings lists correspondantes du secteur intéressé, et à défaut aux 
    e-mails des profils d'inscrits appropriés ayant par ailleurs accepté ce type de 
    mail collectif.
  </p>
  <p>
    Tous les autres mails collectifs, c'est-à-dire envoyer un mail à une liste de 
    destinataires, ensemble ou individuellement, supérieure à 20, ne sont pas 
    autorisés (sauf évidemment mailing lists). 
  </p>
</div>
<a id="flags" name="flags"></a> 
<div class="sstitre">
  Quelle est la différence entre les cases de visibilité ?
</div>
<div class="explication">
  <p>
    Pour chaque information, il est possible de choisir son degré de visibilité.
    Certaines informations peuvent être mises sur le site public accessible par
    les non-polytechniciens : si tu le souhaites, coche la case verte "site
    public" correspondante. Ces informations peuvent par ailleurs être
    transmises à l'AX pour la mise à jour de l'annuaire papier et de son
    annuaire en ligne sur polytechniciens.com : si tu le souhaites, coche la
    case orange "transmis à l'AX".
    Certains champs sont rouges comme le CV, c'est-à-dire qu'ils sont
    exclusivement vus sur la partie privée de Polytechnique.org, réservée
    aux polytechniciens.
  </p>
</div>
<a id="niveau_langue" name="niveau_langue"></a>
<div class="sstitre">
  A quoi correspondent les niveaux de langues ?
  </div>
  <div class="explication">
    <p>
    <ul>
    <li>Niveau 6 : Maîtrise complète de la langue.
    <p>Tu comprends tout ce que tu lis ou écoutes dans des
    domaines variés. Tu saisis les nuances de la langue et
    interprétes avec finesse des documents complexes.
    Tu t'exprimes spontanément avec justesse et fluidité.
    Tu sais argumenter sur des sujets complexes.
    </p>
    <li>Niveau 5 : Bonne maîtrise de la langue.
    <p>
    Tu comprends dans le détail des textes complexes et des
    productions orales sur des sujets relatifs à la vie sociale et
    professionnelle.
    Tu t'exprimes avec assurance et précision sur des sujets
    variés.
    </p>
    <li>Niveau 4 : Maîtrise générale de la langue.
    <p>
    Tu comprends les informations détaillées des textes ou des
    productions orales traitant d'un sujet familier, concret ou
    abstrait.
    Tu t'exprimes clairement sur des sujets en relation avec
    ton
    domaine d'intérêt.
    </p>
    <li>Niveau 3 : Maîtrise limitée de la langue.
    <p>
    Tu comprends les informations significatives des textes et des
    productions orales se rapportant à des situations connues ou
    prévisibles.
    Tu t'exprimes de manière compréhensible sur des sujets de
    la vie quotidienne.
    </p>
    <li>Niveau 2 : Maîtrise des structures de base de la langue.
    <p>
    Tu comprends les informations pratiques de la vie courante
    dans les messages simples.
    Tu peux te faire comprendre dans des situations familières
    et prévisibles.
    </p>
    <li>Niveau 1 : Connaissance basique de la langue.
    <p>
    Tu comprends de courts énoncés s'ils sont connus et répétés.
    Tu sais exprimer des besoins élémentaires.
    </p>
    </ul>
    </p>
</div>

<a id="cv" name="cv"></a>
<div class="sstitre">
  Faut-il remplir le CV et comment ?
</div>
<div class="explication">
  <p>
    D'abord, le CV reste d'accès limité aux inscrits, il n'est pas possible de 
    l'afficher dans les recherches publiques. D'autre part, nous ne le transmettrons 
    jamais, à quiconque.
  </p>
  <p>
    Ton CV complet, si tu veux le mettre, a plutôt sa place sur ta page web et pas 
    sur Polytechnique.org. L'idée du CV ici, c'est surtout d'avoir des mots-clés qui 
    permettent de faire des recherches plus évoluées. Ainsi, les loisirs peuvent être 
    mis dans le champ CV aussi bien que des expériences professionnelles et autres.
    Un remplissage succinct comme ci-dessous est donc bien adapté au champ en question. 
    Néanmoins, la place dans nos bases de données n'est pas limitée et sois libre de 
    remplir ce champ avec toutes les informations que tu souhaites. A priori, tu ne 
    peux qu'y gagner.
  </p>
  <div class="center">
    <form action="">
      <textarea name="cv_example" rows="7" cols="34">
* internet e-commerce startup
1996-1999 Amazon.com USA Washington Ingénieur 
1999-2001 ... ... 

* loisirs
parapente cinéma styx ...

* ...
      </textarea>
    </form>
  </div>
</div>
<hr />
<a id="connect" name="connect"></a>
<div class="ssrubrique">
  Problèmes de connexion
</div>
<a id="config" name="config"></a>
<div class="sstitre">
  Quels sont les paramètres et la configuration nécessaires pour se connecter 
  correctement ?
</div>
<div class="explication">
  <p>
    Il faut un navigateur qui exécute le javascript. 
    Ce point est absolument nécessaire pour accéder au site sans problème. 
    Il y a de grandes chances que ton problème vienne de là, nous te conseillons de 
    vérifier déjà que ce paramètre est bien activé avant de continuer.
  </p>
</div>    
<a id="passe" name="passe"></a>
<div class="sstitre">
  J'ai perdu mon mot de passe, que faire ?
</div>
<div class="explication">
  <p>
    Rends toi sur la page "me connecter", là où tu aurais tapé ton mot de passe 
    si tu t'en souvenais. Il y a un lien "j'ai perdu mon mot de passe". Clique 
    dessus. Il te sera alors proposé une procédure de récupération automatique 
    de ton mot de passe !
  </p>
</div>
<a id="acces" name="acces"></a>
<div class="sstitre">
  Je n'arrive pas à me connecter ! Que faut-il essayer ?
</div>
<div class="explication">
  <p>
    Bon, il y a beaucoup de possibilités, on va les prendre dans l'ordre.
  </p>
  <p>
    As-tu déjà accédé au site ?
  </p>
  <p>
    Si oui, vérifie que tu rentres correctement ton login (début de ton adresse 
    en polytechnique.org sans @polytechnique.org) et ton mot de passe. Un 
    copier/coller avec un espace de trop est vite fait, un clavier qwerty au lieu 
    d'azerty ou l'inverse aussi, et la touche "CAPS LOCK" enfoncée n'arrange pas 
    non plus les choses.
  </p>
  <p>
    Une fois que tu es sûr de ton mot de passe et de ton login, vérifie que ton 
    browser exécute correctement le javascript. Par exemple, la date est-elle 
    correctement affichée en haut de la page ? Le javascript est complètement 
    nécessaire, car ton mot de passe doit être crypté localement pour ne pas 
    passer en clair sur Internet. C'est à ça qu'il sert notamment pour la 
    connexion.
  </p>
  <p>
    Sinon, tu n'es peut-être pas inscrit (en es-tu vraiment sûr ?). Pour
    le savoir, vérifie que ton adresse en polytechnique.org répond. Si c'est le 
    cas, tu es inscrit, sinon rends-toi sur la page d'inscription : pour une 
    raison quelconque, ton inscription n'existe pas dans notre base. Tu viens de 
    t'inscrire et l'accès ne marche pas ? Attention, tu n'as pas dû confirmer ta 
    pré-inscription. Une inscription ce n'est pas juste un formulaire à remplir 
    et puis voilà. C'est un échange de mails ensuite, et enfin la visite d'une 
    page web bien précise reçue par e-mail. Si tu n'as rien reçu par e-mail, tu 
    t'es trompé dans ton adresse e-mail ou alors elle était en panne au moment 
    où le serveur t'a envoyé l'e-mail de demande de confirmation. Vérifie que tu 
    as reçu une confirmation par mail et que tu l'as bien effectuée. Ton login/mot 
    de passe n'est actif qu'après. Dans le cas contraire, refais une inscription, 
    de toute façon, les doublons ne peuvent pas exister.
  </p>
</div>
<hr />
<div class="ssrubrique">
  Utilisation post-connexion
</div>
<a id="ethique" name="ethique"></a>
<div class="sstitre">
  Quelle est l'éthique que vous privilégiez pour les mails collectifs ?
</div>
<div class="explication">
  <p>
    Nous ne faisons pas d'éthique. C'est à toi de dire ce que tu es prêt à recevoir 
    et pas à nous. Les règles imposées concernent avant tout le bon fonctionnement 
    du service, aussi bien du point de vue purement technique (surcharge) que 
    satisfaction des inscrits (publi-mailing).
  </p>
</div>
<a id="mails2" name="mails2"></a> 
<div class="sstitre">
  Puis-je envoyer un mail à des X et comment ?
</div>
<div class="explication">
  <p>
    Oui, bien sûr. Si tu as une information promo, envoie-la aux responsables web 
    de ta promo (qui ne sont pas forcément des kessiers) qui se chargeront de l'envoi 
    avec nous (ils jouent le rôle de filtre pour éviter que chaque personne ne 
    décide de son côté d'envoyer un mail promo). Cependant, nous faisons remarquer 
    que des outils de communication promo sont ou seront mis en place sur le site. 
    Comme le message au login, ou les forums. Evite les mails promo quand tu peux !
  </p>
  <p>
    Tu veux recruter des X pour des stages ou des embauches ? Il n'y a pas de règle 
    générale dans ce domaine, il faut nous contacter pour voir à qui on peut l'envoyer. 
    Si tu es inscrit, tu peux commencer par l'envoyer à une mailing list bien choisie 
    si elle existe déjà... Sinon, s'il faut tirer des profils de la base de données 
    correspndant à ta demande, car tu n'as pas accès aux champs d'autorisation de 
    mail emploi. Ne fais surtout pas ta sélection toi-même au moyen des recherches 
    puis un envoi massif, tu inclurais ainsi des gens qui ne sont pas d'accord pour 
    recevoir ce type de mail, en plus de ne pas respecter les conditions générales 
    du service.
  </p>
  <p>
    Tu as besoin d'envoyer un mail à 50 X assez souvent ? Malheureusement pour ton 
    mail, la configuration actuelle va bloquer au bout du 20ème mail. Ce besoin est 
    exactement celui d'une mailing list. Pour l'instant, coupe ton mail en plusieurs 
    envois.
  </p>
</div>
<a id="secu" name="secu"></a>
<div class="sstitre">
  Puis-je utiliser le même mot de passe qu'ailleurs ?
</div>
<div class="explication">
  <p>
    D'une manière générale, le système le mieux sécurisé pâtit de l'utilisation du
    même mot de passe dans un système moins sécurisé. En effet, le système sécurisé 
    ne craint pas normalement que ton mot de passe soit percé. Par contre, en 
    l'utilisant dans un autre système moins sécurisé, tu diminues d'autant la sécurité 
    du premier (puisqu'il suffit de trouver le mot de passe sur le second pour accéder 
    au premier). Le site www.polytechnique.org a actuellement une sécurité  très forte 
    pour ton mot de passe (plus forte qu'un site bancaire par exemple), vu que celui-ci 
    est crypté irréversiblement (contrairement à HTTPS qui est réversible). Ainsi, si 
    tu utilises le même mot de passe qu'ailleurs, c'est Polytechnique.org qui risque 
    d'en être victime. Mais d'un autre côté, nous avons forcé sur la sécurité alors que 
    les informations derrière sont finalement peu stratégiques (pas de mot de passe 
    visible, pas de numéro de carte bancaire). Donc, à notre avis, tu peux utiliser 
    le même mot de passe qu'ailleurs, le risque est limité pour nous et nul pour 
    l'autre système.
  </p>
</div>

<a id="secu2" name="secu2"></a>
<div class="sstitre">
  Quel est le niveau de sécurité de Polytechnique.org ?
</div>
<div class="explication">
  <p>
    Concernant le mot de passe de l'utilisateur : le plus élevé imaginable puisqu'il 
    circule de manière cryptée irréversible. En fait, avant même d'être envoyé sur 
    Internet, ton ordinateur le chiffre sur place irréversiblement grâce au javascript 
    (d'où son utilité pour se connecter). Puis il est mélangé à un challenge envoyé par 
    le serveur, et enfin seulement la réponse part sur le Web.
  </p>
  <p>
    Concernant la protection des informations du site en général, le niveau de sécurité 
    est correct par rapport au type d'information contenu. Il est possible de simuler 
    un accès à partir de la connaissance d'un mot de passe crypté et d'un challenge, 
    mais comme d'un autre côté de nombreuses informations sont publiques, y a-t-il 
    vraiment intérêt à développer toute cette ingénierie pour si peu ?
  </p>
</div>

<a id="panne" name="panne"></a>
<div class="sstitre">
  Vous tombez souvent en panne ?
</div>
<div class="explication">
  <p>
    En fait, il arrive au service d'être interrompu, bien que nous n'y puissions rien.
    En un an, on a dénombré quatre arrêts de deux jours dus à nos prestataires, et un 
    dû à notre changement important de configuration, serveur, scripts, etc.... Il faut 
    savoir que dans ce cas, le mail n'est en général pas perdu (quand il est perdu, 
    l'envoyeur est informé). Il arrive en retard de deux jours, ce qui peut être assez 
    gênant mais reste vraiment exceptionnel. Quant à l'accès web, il est maintenant 
    complètement indépendant de l'e-mail. Il se peut donc tout à fait que le site web 
    ne réponde pas tout en pouvant utiliser l'e-mail normalement. En tout cas, ces cas 
    de figure sont rares et ont toujours été prévus et prévenus (maintenance régulière). 
    Tous les développeurs de polytechnique.org ne peuvent plus se passer de leur adresse 
    sur ce site, et nous recevons tous largement une cinquantaine de mails par jour, donc 
    un jour d'arrêt vaut au moins 100 mails le suivant, sans compter les mails d'insulte 
    même si le problème ne nous est pas imputable (panne chez notre fournisseur
    d'accès à Internet par exemple...)
  </p>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
