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
 ***************************************************************************}



{if $smarty.request.txt}

<p>{$smarty.request.txt}</p>
<p><a href="#" onclick="self.close()">Fermer cette fenêtre</a></p>

{else}
<script language="JavaScript" type="text/javascript">
  <!--
  function textpopup(desc)
  {ldelim}
  var w = window.open("plan.php?txt="+desc, "textpopup" url="resizable,menubar=no,status=no,width=400,height=200");
  w.focus();
  return true;
  {rdelim}
  //-->
</script>


<ol>
  <li><a href="#perso">Informations personnelles</a>
  </li>
  <li><a href="#services">Services</a>
  </li>
  <li><a href="#communaute">Communauté X</a>
  </li>
  <li><a href="#general">Informations générales</a>
  </li>
  <li><a href="#doc">Documentation</a>
  </li>
  <li><a href="#asso">L'association Polytechnique.org</a>
  </li>
</ol>

<br/>

<h1>
  Informations personnelles
</h1>

<ul>
  {page title="Mon profil" url="profil.php"|url}
  Cette page permet de modifier les informations publiques te concernant, comme tes coordonnées url=ta formation, etc...
  {/page}

  {page title="Mon carnet" url="carnet/"|url}
  A travers cette page tu peux gérer la liste de tes contacts X.  Cela te permettra d'accéder
  plus facilement à ces personnes sur les pages de ce site. Cette liste est aussi disponible en
  format imprimable.
  {/page}

  {page title="Mes contacts en version imprimable (PDF)" url="carnet/mescontacts_pdf.php"|url}
  Cette page crée un document PDF imprimable avec la liste de tes contacts. Tu dois avoir Acrobat
  sur ton ordinateur pour pouvoir bénéficier de ce service. Si tu n'as pas ce logiciel, tu peux
  le télécharger gratuitement sur le site d'Adobe.
  {/page}

  {page title="Mes préférences" url="preferences.php"|url}
  Sur cette page tu pourra configurer le site Polytechnique.org pour qu'il s'adapte à tes
  besoins.
  {/page}

  <li>
  <ul>
    {page title="Mes adresses de redirection" url="emails.php"|url}
    Cette page te permet de configurer les adresses mails sur lesquelles pointent tes adresses
    Polytechnique.org.
    {/page}

    {page title="Mon alias mail @melix.net/.org" url="alias.php"|url}
    Tu peux bénéficier d'adresses mail conviviales @melix.net ou @melix.org, pour cela il te
    suffit de faire une demande sur cette page.
    {/page}

    {page title="Ma redirection de page WEB" url="acces_redirect.php"|url}
    De la même manière que pour la redirection d'adresse mail, tu peux bénéficier de la
    redirection à vie de site web.  Cette page est là pour t'y aider.
    {/page}

    {page title="Apparence du site (skins)" url="skins.php"|url}
    Que tu aimes les pingouins ou que tu préfères le site sous une apparence plus traditionnelle url=tu trouvera de quoi mettre le site Polytechnique.org à ton gout !
    {/page}

    {page title="Changer mon mot de passe pour le site" url="motdepassemd5.php"|url}
    Grâce à cette page, tu peux changer le mot de passe qui te permet d'accéder au site
    Polytechnique.org ainsi que les groupes X associés.
    {/page}

    {page title="Activer l'accès SMTP et NNTP" url="acces_smtp.php"|url}
    Pour pouvoir envoyer des mails en utilisant le serveur de Polytechnique.org, ou pour pouvoir
    lire les forums avec Outlook Express (ou ton logiciel de news préféré), tu dois d'abord
    activer ton compte smtp/nntp.
    {/page}

    {page title="Attribuer un cookie d'authentification permanente" url="AccesPermanentOn.php"|url}
    Cette page te permettra de te passer d'authentification pour une utilisation restreinte du
    site à partir de ton ordinateur uniquement. A utiliser avec précautions !
    {/page}

    {page title="Supprimer l'accès permanent" url="AccesPermanentOff.php"|url}
    Cette page te permet de supprimer l'accès permanent que tu pouvais avoir demandé.
    {/page}
  </ul>

  </li>
</ul>

<br/>

<h1>
  Services
</h1>

<ul>
  {page title="Envoyer un mail" url="emqils/send.php"|url}
  Tu peux rédiger tes mails directement par le site web de Polytechnique.org.
  {/page}

  {page title="Forums et petites annonces" url="banana/"|url}
  Les forums sont des lieux de rencontre et de convivialité entre personnes partageant de même
  centres d'intérêts.  Rejoins nous vite !
  {/page}

  <li>
  <ul>
    {page title="Configuration de Banana" url="confbanana.php"|url}
    Pour configurer les paramètres de l'interface web Banana de lecture de forums.
    {/page}

    {page title="Abonnements aux forums" url="banana/subscribe.php"|url}
    Cette page permet de choisir les forums que tu souhaites lire.
    {/page}
  </ul>
  </li>
  {page title="Listes de diffusion (inscriptions, créations, listes promo et newsletter)"
  url="listes/"|url}
  Les listes de diffusion te permettent de recevoir ou de communiquer par mail des informations
  avec d'autres camarades partageant un même centre d'intérêt. Sur cette page tu peux choisir
  quelles sont celles que tu souhaites recevoir.
  {/page}

  {page title="Télépaiement" url="paiement/"|url}
  Un outil bien pratique pour l'organisation de manifestation ou le paiement de cotisations entre
  autres. Grâce à lui, tu pourra payer en ligne pour participer à diverses manifestations.
  {/page}

  {page title="Patte cassée" url="pattecassee.php"|url}
  Si tu remarques que l'adresse d'un camarade génère des erreurs, tu peux vérifier si il a reçu
  le mail sur un autre adresse.  Nous en profiterons pour lui signaler l'erreur en question.
  {/page}

  {page title="Gérer un sondage" url="sondage/accueil.php"|url}
  Cette page t'aide à créer un sondage en ligne qui s'adressera à la communauté internaute des X.
  {/page}
</ul>

<br/>

<h1>
  Communauté X
</h1>

<ul>
  {page title="Annuaire" url="recherche.php"|url}{/page}

  {page title="Trombinoscope promo" url="trombipromo.php"|url}
  Pour se revoir de temps à autres la tête de ses cocons !
  {/page}

  {page title="Portail des associations polytechniciennes" url="http://www.polytechnique.net/"}
  Site regroupant des liens vers de nombreux sites d'associations d'X.
  {/page}
</ul>

<br/>

<h1>
  Informations générales
</h1>

<ul>
  {page title="Accueil et évènements" url="login.php"|url}{/page}
  {page title="Proposition d'information événementielle" url="evenements.php"|url}{/page}
  {page title="Lettre de Polytechnique.org" url="newsletter.php"|url}{/page}
  {page title="Page de l'emploi" url="emploi.php"|url}{/page}
  {page title="Forum emploi" url="banana/thread.php?group=xorg.pa.emploi"|url}{/page}
</ul>

<br/>
<h1>
  Documentation
</h1>

<ul>
  {page title="Foire aux questions" url="docs/index.php"|url}{/page}
  <li>Configuration générale
  <ul>
    {page title="Utiliser le SMTP sécurisé et le NNTP sécurisé avec Outlook Express"
    url="docs/doc_oe.php"|url}{/page}
    {page title="Utiliser le SMTP sécurisé et le NNTP sécurisé avec Netscape (ou Mozilla)"
    url="docs/doc_nn.php"|url}{/page}
    {page title="Certificat de sécurité" url="docs/doc_ssl.php"|url}{/page}
  </ul>

  </li>
  <li>Services de mails
  <ul>
    {page title="Mes redirections d'adresses emails" url="docs/doc_emails.php"|url}{/page}
    {page title="Polytechnique.org comme e-mail dans le champ FROM"
    url="docs/FROM.php"|url}{/page}
    {page title="alias @melix.net" url="docs/doc_melix.php"|url}{/page}
    {page title="Pourquoi et comment choisir un e-mail gratuit"
    url="docs/doc_gratuits.php"|url}{/page}
    {page title="Patte cassée" url="docs/doc_patte_cassee.php"|url}{/page}
  </ul>

  </li>
  <li>Autres services internet
  <ul>
    {page title="Redirection de page WEB" url="docs/doc_carva.php"|url}{/page}
    {page title="Comment se connecter aux forums" url="docs/doc_nntp.php"|url}{/page}
  </ul>

  </li>
  <li>Chartes d'utilisation et éthique
  <ul>
    {page title="Charte" url="charte.php"|url}{/page}
    {page title="Services et éthique" url="ethique.php"|url}{/page}
    {page title="Règles générales d'utilisation des forums" url="docs/doc_forums.php"|url}{/page}
  </ul>

  </li>
</ul>


<br/>
<h1>
  L'association Polytechnique.org
</h1>

<ul>
  {page title="Le site de l'association" url="http://x-org.polytechnique.org"}{/page}
  {page title="A propos de l'association" url="apropos.php"|url}{/page}
  {page title="Nous contacter" url="contacts.php"|url}{/page}
  {page title="Faire un don à l'association" url="dons.php"|url}{/page}
  {page title="Interruptions de service" url="coupure.php"|url}{/page}
  {page title="Statistiques du site" url="stats.php"|url}{/page}
  {page title="Performance des serveurs de mails" url="parselogR.php"|url}{/page}
  </li>
</ul>

{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
