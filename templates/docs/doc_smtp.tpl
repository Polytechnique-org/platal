{* $Id: doc_smtp.tpl,v 1.1 2004-01-27 16:34:50 x2000habouzit Exp $ *}

<div class="rubrique">
  Le serveur SMTP de Polytechnique.org
</div>
<p class="normal">
  Polytechnique.org propose un serveur SMTP ouvert à tous les inscrits 
  <a href="{"acces_smtp.php"|url}">qui en font la demande</a>.
</p>
<div class="ssrubrique">
  A quoi sert le serveur SMTP ?
</div>
<p class="normal">
  Le serveur <abbr title="Simple Mail Transfert Protocol">SMTP</abbr> est la
	machine sur laquelle se connecte ton logiciel de courrier électronique
	(Outlook Express, Netscape, Eudora...) pour envoyer le courrier. On l'appelle
	aussi <em>serveur de courrier sortant</em>.
	<br />C'est la première machine qui prend la responsabilité d'envoyer le
	message, elle doit donc être capable d'identifier l'émetteur du courrier en
	cas de problème, sinon c'est la porte ouverte au spam (pollution des boîtes
	aux lettres par envoi de courrier non sollicités).
      Ainsi, quand on utilise un ordinateur portable à la fois au bureau et à la maison, il faut sans cesse changer de serveur SMTP.
</p>
<div class="ssrubrique">
  Pourquoi un tel service ?
</div>
<ul>
  <li>
    <p class="normal">
      Afin d'éviter le spam, les serveurs SMTP sont généralement assez
			<strong>restrictifs</strong> sur les personnes autorisées à les utiliser,
			ainsi pour
			utiliser le serveur SMTP de LibertySurf pour envoyer des messages, il est
			nécessaire d'être connecté(e) à Internet par l'intermédiaire de
			LibertySurf. Si tu te connectes par un autre fournisseur d'accès, il
			faudra changer ta	configuration de ton logiciel de courrier, ce qui peut
			devenir ennuyeux si les changements sont fréquents.
    </p>
  </li>
  <li>
    <p class="normal">
      De plus, certains serveurs SMTP n'autorisent dans le champ d'expéditeur (
			<code>From:</code>) qu'une adresse mail se terminant par leur domaine, ce
			qui empêche l'envoi de courrier avec une adresse d'expéditeur en
			<code>@polytechnique.org</code>.
    </p>
  </li>
  <li>
    <p class="normal">
      Tu es dans une entreprise qui s'autorise la lecture des messages qui
			passent par son serveur SMTP et tu veux 
      envoyer un messsage qui ne pourra être intercepté par le service
			informatique de ton entreprise.
    </p>
  </li>
</ul>
<p class="normal">
  Pour toutes ces raisons (et d'autres moins parlantes),
  le serveur SMTP de Polytechnique.org apporte une bonne solution. 
  Pour des raisons d'identification, ce serveur te demandera un <i>login</i> 
  et un mot de passe, <a href="{"acces_smtp.php"|url}"><b>il faut
	donc activer ton compte</b></a> avant de continuer la configuration.
	&Eacute;videment, le SPAM est interdit en utilisant le serveur SMTP de
	Polytechnique.org, et si tu te rends coupable de spam, ton compte sera
	supprimé.
</p>

<div class="ssrubrique">Configuration</div>
<p class="normal">
  Avant toute chose, il faut avoir accepté le certificat SSL de
	Polytechnique.org.
  Si tu ne l'as jamais fait, avant de configurer ton logiciel de messagerie
  électronique, lis <a href="doc_ssl.php">ces instructions</a>.
</p>
<ul>
  <li><a href="{"docs/doc_oe.php?doc=smtp"|url}">Configuration sous Outlook Express</a> (page longue à charger)</li>
  <li><a href="{"docs/doc_nn.php?doc=smtp"|url}">Configuration sous Netscape</a> (page longue à charger)</li>
</ul>

<div class="ssrubrique">
  Attention !
</div>
<p class="normal">
  L'utilisation de <b>certains logiciels antivirus</b> (comme <i>Norton Antivirus</i>)
  nécessite un élément de configuration supplémentaire : il faut indiquer au
	logiciel de ne pas scanner le courrier sortant.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
