{* $Id: doc_from.tpl,v 1.4 2004-08-30 12:18:40 x2000habouzit Exp $ *}

<div class="texte">
  <div class="rubrique">
    Polytechnique.org comme e-mail dans le champ FROM
  </div>
  <p>
  Comme pour toute aide à la configuration, la première étape
  consiste souvent à mettre à jour ses logiciels installés.
  En effet, la page suivante a été écrite pour la version
  5.5 d'Outlook Express qui est la dernière version actuellement
  disponible, elle marche correctement et nous recommandons la mise à
  jour pour tout type de configuration d'ordinateur.
  </p>
  <p>
  <a href="http://windowsupdate.microsoft.com/">Clique ici pour faire la mise à
    jour à partir du site de Microsoft.</a>
  </p>
  <p>
  La page suivante te propose de configurer différents comptes de
  messagerie sur un même ordinateur, afin de pouvoir choisir ton adresse
  d'envoi à chaque e-mail envoyé (polytechnique.org, m4x.org,
  ton entreprise, ton fournisseur d'accès, etc).
  </p>
  <hr />
  <table class="etape" summary="Premiere étape" cellpadding="5">
    <tr>
      <td>
        <p>
        Dans le menu principal d'Outlook Express, choisis le sous-menu
        <strong>&quot;Comptes&quot;</strong>.
        </p>
        <p>
        La fenêtre qui s'affiche à l'écran suivant montre la liste des
        comptes actuellement paramétrés.
        </p>
      </td>
      <td>
        <img src="{"images/docs_from1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
      </td>
    </tr>
  </table>
  <hr />
  <table class="etape" summary="Deuxième étape" cellpadding="5">
    <tr>
      <td>
        <p>
        Un compte est désigné par un nom, ici c'est <em>adupont@mail.com</em>
        qui désigne le compte utilisé dans l'exemple. Le plus souvent,
        la différence entre deux comptes est l'adresse e-mail d'envoi uniquement,
        mais parfois, les comptes se différencient aussi par les serveurs
        utilisés pour recevoir ou envoyer un e-mail.
        </p>
        <p>
        Nous allons créer un nouveau compte pour utiliser polytechnique.org
        comme adresse d'envoi. Clique sur <strong>&quot;Ajouter&quot;</strong>, puis
        <strong>&quot;Courrier...&quot;</strong>.
        </p>
      </td>
      <td>
        <img src="{"images/docs_from2.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
      </td>
    </tr>
  </table>
  <hr />
  <table class="etape" summary="Troisieme étape" cellpadding="5">
    <tr>
      <td>
        <p>
        Mettre Polytechnique.org dans le champ FROM (ou "De"), il faut bien
        comprendre que c'est une opération essentiellement formelle. Tes mails
        restent stockés sur le même serveur, et Outlook Express utilise le même
        autre serveur pour poster les mails sortants. En fait, on crée plutôt un
        compte virtuel qu'un véritable compte.
        </p>
        <p>
        Le <strong>&quot;Nom complet&quot;</strong>, c'est la façon dont ton identité
        apparaît dans le logiciel d'e-mail de tes destinataires. Tu peux
        taper absolument ce que tu veux, y compris un surnom pourquoi pas.
        </p>
        <p>
        Ensuite, clique sur <strong>&quot;Suivant &gt;&quot;</strong>.
        </p>
      </td>
      <td>
        <img src="{"images/docs_from3.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
      </td>
    </tr>
  </table>
  <hr />
  <table class="etape" cellpadding="5" summary="Quatrième étape">
    <tr>
      <td>
        <p>
        L'écran suivant te demande l'adresse e-mail que tu veux afficher
        dans tes correspondances.
        </p>
        <p>
        Tu peux créer autant de comptes que tu veux sur le même ordinateur
        avec tes différentes adresses e-mail, ce qui te permettra de choisir,
        au moment de composer un nouvel e-mail, quelle adresse tu veux utiliser.
        </p>
        <p>
        Ainsi, tu peux utiliser ton adresse professionnelle, ton adresse à
        la maison, ou ton adresse à vie. Nous te conseillons de refaire toute
        la procédure autant de fois que tu as d'adresses e-mail.
        </p>
        <p>
        Dans le cas présent, nous créons un compte qui envoie des e-mails sous
        l'identité<br />
        &quot;Alice DUPONT &lt;
        <a href="mailto:alice.dupont@m4x.org/">alice.dupont@m4x.org</a>&gt;&quot;.
        </p>
        <p>
        Clique sur <strong>&quot;Suivant &gt;&quot;</strong>.
        </p>
      </td>
      <td>
        <img src="{"images/docs_from4.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
      </td>
    </tr>
  </table>
  <hr />
  <table class="etape" cellpadding="5" summary="Cinquième étape">
    <tr>
      <td>
        <p>
        Cet écran te demande quels serveurs utiliser pour ce compte.
        </p>
        <p>
        <strong>Le serveur POP3</strong>, c'est celui sur lequel l'ordinateur va chercher ton
        mail. Si tu as ouvert une adresse e-mail gratuite sur www.netcourrier.com par
        exemple, ce serveur est pop.netcourrier.com. D'une manière générale,
        cette ligne n'a pas d'importance pour un compte formel (le mail reste au
        même endroit). En effet, comme le logiciel dispose déjà du compte habituel
        configuré avec l'indication du serveur POP3, ton ordinateur sait déjà où
        aller chercher ton mail. Tape donc n'importe quoi dans la case, ça marchera
        très bien. On va d'ailleurs dire plus loin de ne pas tenir compte de ce
        réglage.
        </p>
        <p>
        L'autre case est par contre très importante. Il s'agit du <strong>serveur
          SMTP</strong>, qui est utilisé pour envoyer un e-mail. En général, tu n'as
        pas le choix, il s'agit du serveur indiqué par ton fournisseur d'accès
        à Internet. Chez wanadoo, c'est smtp.wanadoo.fr, chez libertysurf,
        c'est smtp.libertysurf.fr. Au travail, le serveur SMTP appartient à
        l'entreprise et pour connaître son nom, il faut regarder sur le compte
        déjà configuré par défaut, ou alors demander à un administrateur système.
        Si tu utilises <strong>plusieurs fournisseurs d'accès</strong> ou que ton serveur
        SMTP refuse les champs From avec une adresse en polytechnique.org, utilise
        <strong>le serveur SMTP de polytechnique.org</strong>, dans ce cas,
        <a href="doc_smtp.php">regarde la configuration</a>.
        </p>
        <p>
        Clique ensuite sur <strong>&quot;Suivant &gt;&quot;</strong>.
        </p>
      </td>
      <td>
        <img src="{"images/docs_from5.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
      </td>
    </tr>
  </table>
  <p>
  L'écran suivant te demande un compte et un mot de passe pour rechercher
  ton mail. Laisse-le tomber pour la même raison que le serveur POP3.
  Tu as déjà un compte par défaut qui vérifie ton e-mail, il est inutile
  de reconfigurer le compte ici et d'aller vérifier une seconde fois tes
  mails sur le même serveur.
  </p>
  <p>
  -> Le seul cas où cet écran a de l'importance, c'est si ton mail est stocké à
  plusieurs endroits sur Internet. Exemple typique : ton e-mail au travail est
  stocké sur un serveur au travail, ton e-mail netcourrier est stocké sur
  pop.netcourrier.com. Dans ce cas, tu peux configurer un deuxième compte avec
  un autre accès POP3. Quand tu rechercheras ton mail, tu verras apparaître non
  seulement tes e-mails professionnels, mais bien s&ucirc;r aussi ceux personnels
  recherchés sur netcourrier.com. Ce cas est traité tout à la fin dans le
  paragraphe "configuration avancée".
  </p>
  <p>
  Clique ensuite sur <strong>&quot;Suivant &gt;&quot;</strong>, puis enfin
  <strong>&quot;Terminer&quot;</strong>.
  </p>
  <hr />
  <table class="etape" summary="Sixième étape" cellpadding="5">
    <tr>
      <td>
        <p>
        Voilà maintenant la physionomie de la liste des comptes.
        </p>
        <p>
        Le nouveau compte a été nommé du nom du serveur sur lequel les mails
        sont stockés (le serveur POP3) en l'occurrence dans notre exemple, il
        s'agit de pop.netcourrier.com (ou n'importe quoi si tu as rentré n'importe
        quoi plus haut).
        </p>
        <p>
        Sélectionne-le et clique sur <strong>&quot;Propriétés&quot;</strong>.
        </p>
      </td>
      <td>
        <img src="{"images/docs_from6.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
      </td>
    </tr>
  </table>
  <hr />
  <table class="etape" cellspacing="5" summary="Septième étape">
    <tr>
      <td>
        <p>
        Cet écran permet d'éditer directement tous les paramètres rentrés
        au fur et à mesure de l'assistant de création de nouveau compte
        détaillé ci-dessus.
        </p>
        <p>
        Dans l'onglet <strong>&quot;Général&quot;</strong>, on trouve l'adresse d'envoi
        du compte, et le <strong>&quot;Nom&quot;</strong> affiché.
        </p>
        <p>
        La petite case <strong>&quot;Inclure ce compte&quot; </strong>est importante.
        Si tu la coches, cela veut dire que ce compte est réel et pas seulement
        formel. En gros, si elle n'est pas cochée, le compte sert uniquement
        pour envoyer un mail avec l'adresse e-mail spécifiée, qui sera utilisée
        aussi pour la réponse, et si elle est cochée, Outlook Express va aller
        vérifier sur le serveur POP3 les e-mails. Dans l'étape de tout à l'heure,
        on avait tapé n'importe quoi pour le serveur POP3, pour être cohérent, il
        ne faut pas cocher la case ici, sinon une erreur sera affichée à chaque
        vérification de mail. En d'autres termes, le compte crée dans cet exemple
        n'est pas un compte de réception mais un compte d'envoi, on ne va donc pas
        l'inclure en réception !
        </p>
      </td>
      <td>
        <img src="{"images/docs_from7.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
      </td>
    </tr>
  </table>
  <hr />
  <table class="etape" cellspacing="5" summary="Huitième étape">
    <tr>
      <td>
        <p>
        Et voilà le résultat ! Quand tu composes un nouveau mail, Outlook Express
        te donne le choix de l'adresse que ton destinataire va voir et à laquelle
        il te répondra.
        </p>
        <p>
        Tu peux donc écrire à qui tu veux avec une adresse en polytechnique.org
        ou en m4x.org. Pour créer autant de comptes virtuels que d'adresses e-mail,
        il suffit de recommencer au début.
        </p>
      </td>
      <td>
        <img src="{"images/docs_from8.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
      </td>
    </tr>
  </table>
  <hr />
  <div class="rubrique">
    Configuration avancée
  </div>
  <p>
  Prenons un exemple. Tu travailles chez MyCompany où tu as un e-mail
  prenom.nom@mycompany.fr et d'autre part, tu as ouvert un email chez netcourrier.com
  de la forme truc@netcourrier.com.
  </p>
  <p>
  De plus, tu reroutes polytechnique.org vers ton adresse e-mail personnelle
  chez netcourrier.com mais pas sur ton adresse professionnelle pour des
  raisons éthiques/de sécurité/de confidentialité.
  </p>
  <p>
  Cependant, tu souhaiterais pouvoir accéder à ton e-mail en polytechnique.org à
  ton travail ou sur ton portable par exemple. C'est en effet toujours très
  ennuyeux de lire son e-mail à des endroits différents.
  </p>
  <p>
  Dans ce cas, la bonne configuration est la suivante.
  </p>
  <p class="sstitre">
  Premier compte :
  </p>
  <p>
  <strong>E-mail:</strong> prenom.nom@mycompany.fr<br />
  <strong>Serveur POP3:</strong>
  pop.mycompany.fr (vérifier le nom au cas par cas)<br />
  <strong>Serveur SMTP:</strong>
  smtp.mycompany.fr (même remarque)<br />
  <strong>Case &quot;inclure ce compte&quot;:</strong> cochée
  </p>
  <p class="sstitre">
  Deuxième compte :
  </p>
  <p>
  <strong>E-mail:</strong> prenom.nom@polytechnique.org<br />
  <strong>Serveur POP3:</strong> pop.netcourrier.com<br />
  <strong>Serveur SMTP:</strong> smtp.mycompany.fr<br />
  <strong>Case &quot;inclure ce compte&quot;:</strong> cochée<br />
  </p>
  <p>
  A supposer maintenant que tu es réellement sur un portable. Quand
  tu le ramènes chez toi, que tu te connectes via libertysurf, tu
  écris un e-mail avec le compte polytechnique.org ci-dessus, et quand tu l'envoies,
  une erreur s'affiche dont le message est du style &quot;Relaying
  denied&quot;.<br /><br />
  </p>
  <p>
  <strong>Explication:</strong> chez toi, tu es relié directement aux serveurs de libertysurf
  avant même d'atteindre Internet. Lorsque tu envoies un e-mail, libertysurf refuse
  d'être uniquement un transit vers le serveur utilisé pour envoyer l'email, en
  l'occurrence smtp.mycompany.fr pour le compte polytechnique.org ci-dessus
  (le serveur SMTP).
  </p>
  <p>
  Pourquoi ce refus de transmettre ton mail au serveur de smtp.mycompany.fr
  pour l'envoi ?
  </p>
  <p>
  Car un FAI (founisseur d'accès à Internet) a une responsabilité lorsqu'il
  fournit l'accès à Internet à une personne. S'il autorisait le relaying, tu
  pourrais tout à fait envoyer un spam en utilisant un serveur de mycompany en
  étant connecté par libertysurf, ou bien aussi des attaques e-mail (virus
  notamment) via toujours ce serveur de mycompany. Or, seul ton FAI sait exactement
  qui tu es, à la fois techniquement et en terme de responsabilité. Si un virus
  mondial part grâce au serveur de mycompany, et que mycompany est incapable
  d'identifier l'envoyeur (il sait juste qu'il a transité par libertysurf juste
  avant d'arriver chez lui, rien de plus), on imagine bien les conséquences sur
  l'entreprise MyCompany.
  </p>
  <p>
  <strong>Conclusion:</strong> le serveur SMTP est toujours celui du prestataire le plus
  proche de toi sur le réseau (celui qui te relie à Internet en clair). Quand tu
  es au travail, connecté par le réseau de ton entreprise, tu envoies un email
  grâce au serveur SMTP smtp.mycompany.fr, quand tu es à la maison, connecté à
  Internet par l'intermédiaire des serveurs de Libertysurf, tu dois utiliser
  smtp.libertysurf.fr pour envoyer un e-mail. Et ainsi de suite.
  </p>
  <p>
  Pour résoudre le problème initial du portable à la maison, il faut donc créer
  un nouveau compte, cette fois-ci "formel", car il ne va pas aller chercher de
  mails ailleurs que les deux premiers, il va juste servir à envoyer du mail par
  un serveur différent des deux premiers comptes qui utilisent tous les deux
  smtp.mycompany.fr.
  </p>
  <p class="sstitre">
  Troisième compte :
  </p>
  <p>
  <strong>E-mail:</strong> prenom.nom@polytechnique.org<br />
  <strong>Serveur POP3:</strong> <em>peu importe, inutilisé</em><br />
  <strong>Serveur SMTP:</strong> smtp.libertysurf.fr (ce compte sert
  quand le premier serveur rencontré sur le réseau est un serveur libertysurf)<br />
  <strong>Case &quot;inclure ce compte&quot;:</strong> non cochée (en effet,
  ce compte sert à envoyer un email en étant connecté à Libertysurf, pas à en
  réceptionner)
  </p>
  <p>
  Voilà, le principe est simple une fois qu'on a saisi les petites
  subtilités de vocabulaire. Une personne avec deux adresses e-mails
  qu'elle veut pouvoir utiliser à la maison et au bureau sur la même
  machine a donc quatre comptes configurés. Et si en plus on rajoute
  la possibilité d'utiliser des alias comme m4x.org, il faut rajouter
  autant de comptes nécessaires. Avec l'habitude, créer un
  nouveau compte ou en éditer un prend à peine quelques minutes. Allez,
  un peu de pratique!
  </p>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
