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
 ***************************************************************************
        $Id: doc_nn.tpl,v 1.8 2004-08-31 11:25:40 x2000habouzit Exp $
 ***************************************************************************}


<p>
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
<p>
    Cette page est valable pour Netscape Communicator 4.x. Les copies d'écran
		ont été réalisées avec la version 4.7 sous Windows, mais restent valables
		pour les autres versions de Netscape Communicator sous d'autres systèmes
		d'exploitation.
    Cette page est tout à fait transposable à Netscape 6/7 et Mozilla.
</p>
<p>
    Tous les services de polytechnique.org étant sécurisés, il faut  commencer
		par faire accepter à ton système d'exploitation les certificats de sécurités
		de polytechnique.org. Pour ceci, suis les instructions de la
		<a href="{"docs/doc_ssl.php"|url}">documentation ssl</a>.
</p>
<p>
    Il faut ensuite activer <a href="{"acces_smtp.php"|url}">ton compte SMTP/NNTP</a>.
    Dans la suite, ton <strong>login</strong> désigne le logine que tu as utilises pour te connecter au site,
    et <strong>le mot de passe</strong> celui que tu as indiqué lors de
    l'<a href="{"acces_smtp.php"|url}">activation de ton compte SMTP/NNTP</a>.
</p>
<div class="ssrubrique">
    SMTP, NNTP, qu'est-ce ?
</div>
<p>
    Le SMTP est la machine sur laquelle ton client de courrier électronique se connecte pour envoyer
    des mails. En général, ton fournisseur d'accès internet t'en propose un. Mais il arrive souvent
    que ces serveurs aient des limitations (notament sur l'adresse mail que tu veux mettre dans le
    champ expéditeur). Pour tous ses inscrits, Polytechnique.org en propose une version sécurisée,
    accessible depuis tout le web.
</p>
<p>
  Le NNTP est un autre nom pour désigner les <a href="{"banana/"|url}">forums</a> de
    discussions de Polytechnique.org. Il s'agit de les consulter depuis un logiciel comme Netscape,
    ce qui est tout de même bien plus pratique que le WebForum.
</p>
<div class="center">
  <span class="erreur">
    Avant toute opération, <a href="{"acces_smtp.php"|url}">active ton compte SMTP/NNTP</a>.
  </span>
</div>
<br />
{if $smarty.get.doc eq 'smtp' || $smarty.get.doc eq 'all'}
<div class="rubrique">
    Utiliser le SMTP sécurisé
</div>

<table summary="Première étape" cellpadding="5">
<tr> 
  <td colspan="3">
    <img src="{"images/docs_confnetscape0.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
      1. Dans le menu principal de Netscape Messenger, choisis le sous-menu 
      <strong>&quot;&Eacute;dition/Préférences&quot;</strong>.
  </td>
  <td>
      2. Choisis alors l'onglet <strong>Identité</strong> dans <strong>Courrier et Forums</strong>.
      La fenêtre devrait alors correspondre à l'écran suivant.
  </td>
  <td>
      3. Remplis alors les champs <strong>Nom</strong> et <strong>Adresse électronique</strong>
      comme il convient avec ton adresse en polytechnique.org.
  </td>
</tr>
</table>

<hr />

<table summary="Deuxième étape" cellpadding="5">
<tr>
  <td colspan="3">
    <img src="{"images/docs_confnetscape1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
    <p>
      1. Clique alors sur l'onglet <strong>Serveurs de courrier</strong>, la fenêtre devrait
      correspondre à l'écran ci-contre.
		</p>
  </td>
  <td class="half">
    <p>
      2. Dans la partie <strong>Serveur de courrier sortant</strong>, indique
			<code>ssl.polytechnique.org</code> dans le champ <strong>Serveur de
			courrier sortant (SMTP)</strong> puis ton <em>login</em> dans le champ 
			<strong>Utilisateur du serveur de courrier sortant</strong>, et enfin
			coche <strong>Toujours</strong> dans la partie <strong>utiliser SSL ou
			TLS</strong>.
		</p>
  </td>
  <td>
    <p>
      3. <strong>Important</strong>, n'oublie pas de cocher <strong>Toujours</strong>, sinon ton
			mot de passe risque de ne pas être chiffré lors de l'envoi de courriels.
		</p>
  </td>
</tr>
</table>

<hr />

<table summary="Troisème étape" cellpadding="5">
<tr> 
 <td>
   <p>
      Si tu envoyes un courriel, tu verras apparaître la fenêtre ci-contre.
      Tape le mot de passe que tu as indiqué lors de l'<a href="{"acces_smtp.php"|url}">activation de ton compte</a>.
    </p>
  </td>
  <td>
    <img src="{"images/docs_confnetscape2.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr /> 

Et maintenant quelques remarques :
<ul>
	<li>
		<p>
			Netscape Communicator ne permet pas de chosir le port du serveur SMTP.
			Il utilise par défaut le port 25.  Avec Netscape 6/7 ou Mozilla, il est
			recommandé d'utiliser le port 587, qui est le port dédié.
		</p>
	</li>
	<li>
		<p>
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

<table summary="Première étape" cellpadding="5">
<tr> 
  <td colspan="3">
    <img src="{"images/docs_nntp_nn1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
      1. Dans le menu principal de Netscape Messenger, choisis le sous-menu 
      <strong>&quot;Edition/Préférences&quot;</strong>.
  </td>
  <td>
      2. Choisis alors l'onglet <strong>Serveurs de forums</strong> dans <strong>Courrier et Forums</strong>.
      clique alors sur le bouton <strong>ajouter</strong>.
      La fenêtre devrait alors correspondre à l'écran ci-dessus.
  </td>
  <td>
      3. Remplis alors les champs <strong>Serveur</strong> et <strong>Port</strong> comme montré sur la capture d'écran.
      N'oublie pas de cocher la case <strong>Supporte les connections chiffrées (SSL)</strong>.
      Tu peux alors tout valider.
  </td>
</tr>
</table>

<hr />

<table summary="Deuxième étape" cellpadding="5">
<tr>
  <td>
      1. Dans ton client apparait maintenant une nouvelle ligne de serveur de forums appellée
      <strong>ssl.polytechnique.org</strong>. Clique avec le bouton droit de ta souris sur cette ligne, et
      demande de t'abonner à des forums.
  </td>
  <td>&nbsp;
  </td>
</tr>
<tr>
  <td>
      2. La boite ci contrea apparait alors, donne alors ton <strong>identifiant</strong> de la forme
      <em>prenom.nom</em>, puis valide.
  </td>
  <td>
    <img src="{"images/docs_nntp_nn2.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
      3. Netscape te demande alors de donner ton mot de passe, tape le mot de passe que tu as
      indiqué lors de <a href="{"smtp_acces.php"|url}">l'activation de ton compte</a>.
  </td>
  <td>
    <img src="{"images/docs_nntp_nn3.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr />

<table summary="Troisième étape" cellpadding="5">
<tr> 
  <td>
    <img src="{"images/docs_nntp_nn4.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
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
