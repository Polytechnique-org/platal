{* $Id: doc_nn.tpl,v 1.1 2004-01-27 16:34:50 x2000habouzit Exp $ *}

<p class="normal">
    [<a href="{"docs/doc_nn.php?doc=smtp"|url}">Configuration du smtp</a>]
    [<a href="{"docs/doc_nn.php?doc=nntp"|url}">Configuration du nntp</a>]
    [<a href="{"docs/doc_nn.php?doc=all"|url}">Doc. complète (gros)</a>]
</p>
<div class="rubrique">
    Utiliser le SMTP sécurisé et le NNTP sécurisé avec Netscape (ou Mozilla)
</div>
<div class="ssrubrique">
    Prérequis
</div>
<p class="normal">
    Cette page est valable pour Netscape Communicator 4.x. Les copies d'écran
		ont été réalisées avec la version 4.7 sous Windows, mais restent valables
		pour les autres versions de Netscape Communicator sous d'autres systèmes
		d'exploitation.
    Cette page est tout à fait transposable à Netscape 6/7 et Mozilla.
</p>
<p class="normal">
    Tous les services de polytechnique.org étant sécurisés, il faut  commencer
		par faire accepter à ton système d'exploitation les certificats de sécurités
		de polytechnique.org. Pour ceci, suis les instructions de la
		<a href="{"docs/doc_ssl.php"|url}">documentation ssl</a>.
</p>
<p class="normal">
    Il faut ensuite activer <a href="{"acces_smtp.php"|url}">ton compte SMTP/NNTP</a>.
    Dans la suite, ton <b>login</b> désigne le logine que tu as utilises pour te connecter au site,
    et <b>le mot de passe</b> celui que tu as indiqué lors de
    l'<a href="{"acces_smtp.php"|url}">activation de ton compte SMTP/NNTP</a>.
</p>
<div class="ssrubrique">
    SMTP, NNTP, qu'est-ce ?
</div>
<p class="normal">
    Le SMTP est la machine sur laquelle ton client de courrier électronique se connecte pour envoyer
    des mails. En général, ton fournisseur d'accès internet t'en propose un. Mais il arrive souvent
    que ces serveurs aient des limitations (notament sur l'adresse mail que tu veux mettre dans le
    champ expéditeur). Pour tous ses inscrits, Polytechnique.org en propose une version sécurisée,
    accessible depuis tout le web.
</p>
<p class="normal">
  Le NNTP est un autre nom pour désigner les <a href="{"banana/"|url}">forums</a> de
    discussions de Polytechnique.org. Il s'agit de les consulter depuis un logiciel comme Netscape,
    ce qui est tout de même bien plus pratique que le WebForum.
</p>
<div class="mef">
  Avant toute opération, <a href="{"acces_smtp.php"|url}">active ton compte SMTP/NNTP</a>.
</div>
<br />
{if $smarty.get.doc eq 'smtp' || $smarty.get.doc eq 'all'}
<div class="rubrique">
    Utiliser le SMTP sécurisé
</div>

<table class="etape" summary="Première étape" cellpadding="5" align="center" width="604">
<tr> 
  <td colspan="3">
    <img src="{"images/docs_confnetscape0.png"|url}" width="604" height="476" alt=" [ CAPTURE D'ECRAN ] ">
  </td>
</tr>
<tr valign="top">
  <td>
      1. Dans le menu principal de Netscape Messenger, choisis le sous-menu 
      <b>&quot;&Eacute;dition/Préférences&quot;</b>.
  </td>
  <td>
      2. Choisis alors l'onglet <b>Identité</b> dans <b>Courrier et Forums</b>.
      La fenêtre devrait alors correspondre à l'écran suivant.
  </td>
  <td>
      3. Remplis alors les champs <b>Nom</b> et <b>Adresse électronique</b>
      comme il convient avec ton adresse en polytechnique.org.
  </td>
</tr>
</table>

<hr class="mark">

<table class="etape" summary="Deuxième étape" cellpadding="5" width="604">
<tr>
  <td colspan="3">
    <img src="{"images/docs_confnetscape1.png"|url}" width="604" height="477" alt=" [ CAPTURE D'ECRAN ] ">
  </td>
</tr>
<tr valign="top">
  <td>
    <p class="normal">
      1. Clique alors sur l'onglet <b>Serveurs de courrier</b>, la fenêtre devrait
      correspondre à l'écran ci-contre.
		</p>
  </td>
  <td width="50%">
    <p class="normal">
      2. Dans la partie <strong>Serveur de courrier sortant</strong>, indique
			<code>ssl.polytechnique.org</code> dans le champ <strong>Serveur de
			courrier sortant (SMTP)</strong> puis ton <i>login</i> dans le champ 
			<strong>Utilisateur du serveur de courrier sortant</strong>, et enfin
			coche <strong>Toujours</strong> dans la partie <strong>utiliser SSL ou
			TLS</strong>.
		</p>
  </td>
  <td>
    <p class="normal">
      3. <u>Important</u>, n'oublie pas de cocher <b>Toujours</b>, sinon ton
			mot de passe risque de ne pas être chiffré lors de l'envoi de courriels.
		</p>
  </td>
</tr>
</table>

<hr class="mark">

<table class="etape" summary="Troisème étape" cellpadding="5">
<tr> 
 <td>
   <p class="normal">
      Si tu envoyes un courriel, tu verras apparaître la fenêtre ci-contre.
      Tape le mot de passe que tu as indiqué lors de l'<a href="{"acces_smtp.php"|url}">activation de ton compte</a>.
    </p>
  </td>
  <td>
    <img src="{"images/docs_confnetscape2.png"|url}" width="382"
			height="179" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr class="mark">

Et maintenant quelques remarques :
<ul>
	<li>
		<p class="normal">
			Netscape Communicator ne permet pas de chosir le port du serveur SMTP.
			Il utilise par défaut le port 25.  Avec Netscape 6/7 ou Mozilla, il est
			recommandé d'utiliser le port 587, qui est le port dédié.
		</p>
	</li>
	<li>
		<p class="normal">
			Certaines <abbr title="direction des systèmes informatiques">DSI</abbr>
			locales interdisent l'utilisation de ports inférieurs à 1024. Il suffit
			alors de spécifier comme numéro de port SMTP non pas 587, mais 2525.
		</p>
	</li>
  </ul>
{/if}
{if $smarty.get.doc eq 'nntp' || $smarty.get.doc eq 'all'}
<br />
<div class="rubrique">
    Utiliser le NNTP sécurisé
</div>

<table class="etape" summary="Première étape" cellpadding="5" align="center" width="603">
<tr> 
  <td colspan="3">
    <img src="{"images/docs_nntp_nn1.png"|url}" width="603" height="475" alt=" [ CAPTURE D'ECRAN ] ">
  </td>
</tr>
<tr valign="top">
  <td>
      1. Dans le menu principal de Netscape Messenger, choisis le sous-menu 
      <b>&quot;Edition/Préférences&quot;</b>.
  </td>
  <td>
      2. Choisis alors l'onglet <b>Serveurs de forums</b> dans <b>Courrier et Forums</b>.
      clique alors sur le bouton <b>ajouter</b>.
      La fenêtre devrait alors correspondre à l'écran ci-dessus.
  </td>
  <td>
      3. Remplis alors les champs <b>Serveur</b> et <b>Port</b> comme montré sur la capture d'écran.
      N'oublie pas de cocher la case <b>Supporte les connections chiffrées (SSL)</b>.
      Tu peux alors tout valider.
  </td>
</tr>
</table>

<hr class="mark">

<table class="etape" summary="Deuxième étape" cellpadding="5" align="center" width="604">
<tr valign="top">
  <td>
      1. Dans ton client apparait maintenant une nouvelle ligne de serveur de forums appellée
      <b>ssl.polytechnique.org</b>. Clique avec le bouton droit de ta souris sur cette ligne, et
      demande de t'abonner à des forums.
  </td>
  <td>&nbsp;
  </td>
</tr>
<tr>
  <td>
      2. La boite ci contrea apparait alors, donne alors ton <b>identifiant</b> de la forme
      <em>prenom.nom</em>, puis valide.
  </td>
  <td>
    <img src="{"images/docs_nntp_nn2.png"|url}" width="384" height="183" alt=" [ CAPTURE D'ECRAN ] ">
  </td>
</tr>
<tr>
  <td>
      3. Netscape te demande alors de donner ton mot de passe, tape le mot de passe que tu as
      indiqué lors de <a href="{"smtp_acces.php"|url}">l'activation de ton compte</a>.
  </td>
  <td>
    <img src="{"images/docs_nntp_nn3.png"|url}" width="384" height="183" alt=" [ CAPTURE D'ECRAN ] ">
  </td>
</tr>
</table>

<hr class="mark">

<table class="etape" summary="Troisième étape" cellpadding="5" align="center" width="668">
<tr> 
  <td>
    <img src="{"images/docs_nntp_nn4.png"|url}" width="668" height="466" alt=" [ CAPTURE D'ECRAN ] ">
  </td>
</tr>
<tr>
  <td>
    Tu vois alors un écran proche de celui ci-dessus apparaitre, il ne te reste plus qu'à choisir
    les newsgroups qui t'intéressent, et à t'y abonner.
  </td>
</tr>
</table>
{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
