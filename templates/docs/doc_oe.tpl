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
        $Id: doc_oe.tpl,v 1.9 2004-10-24 14:41:12 x2000habouzit Exp $
 ***************************************************************************}


<p>
  [<a href="{"docs/doc_oe.php?doc=smtp"|url}">Configuration du smtp</a>]
  [<a href="{"docs/doc_oe.php?doc=nntp"|url}">Configuration du nntp</a>]
  [<a href="{"docs/doc_oe.php?doc=all"|url}">Doc. complète (gros)</a>]
</p>

<h1>
  Utiliser le SMTP sécurisé et le NNTP sécurisé avec Outlook Express
</h1>

<div class="ssrubrique">
  Prérequis
</div>

<p>
  Comme pour toute aide à la configuration, la première étape consiste
  souvent à mettre à jour ses logiciels installés. En effet, la présente page
  a été écrite pour la version 5.5 d'Outlook Express qui est une version déja
  ancienne, mais tu peux t'en inspirer pour configurer la dernière version
  disponible (Outlook Express 6), elle marche correctement et nous
  recommandons la mise à jour pour tout type de configuration d'ordinateur.
</p>

<p>
  Cependant, les principes de cette configuration sont toujours les mêmes
  dans les autres versions du logiciel et il est simple de leur transposer
  cette explication.
  Clique <a href="http://windowsupdate.microsoft.com/">ici</a> pour faire
  la mise à jour à partir du site de Microsoft.
</p>

<p>
  Tous les services de polytechnique.org sont sécurisés, il faut donc
  commencer par ajouter les certificats de sécurité de polytechnique.org au
  panier des certificats de Windows. Pour ce faire, suis les instructions de
  la <a href="{"docs/doc_ssl.php"|url}">documentation ssl</a>.
</p>
<p>
  Il faut ensuite activer <a href="{"acces_smtp.php"|url}">ton compte SMTP/NNTP</a>.
  Par la suite, ton <strong>login</strong> désigne l'identifiant que tu utilises pour te connecter au site,
  et <strong>le mot de passe</strong> celui que tu as indiqué lors de
  l'<a href="{"acces_smtp.php"|url}">activation de ton compte SMTP/NNTP</a>.
</p>

<div class="ssrubrique">
  SMTP, NNTP, qu'est-ce ?
</div>
<p>
  Le serveur SMTP est la machine sur laquelle ton client de courrier électronique se
  connecte pour envoyer des mails. En général, ton fournisseur d'accès
  internet t'en propose un. Mais il arrive souvent que ces serveurs aient des
  limitations (notament sur l'adresse mail que tu veux mettre dans le champ
  expéditeur). Pour tous ses inscrits, Polytechnique.org propose un serveur
  sécurisé, accessible depuis tout internet.
</p>
<p>
  Le NNTP est un autre nom pour désigner les <a href="{"banana/"|url}">forums</a> de
  discussions de Polytechnique.org. Il s'agit de les consulter depuis un
  logiciel comme Outlook Express,
  ce qui est plus configurable que la page web du site depuis laquelle tu 
  peux également les voir.
</p>
<div class="center">
  <span class="erreur">
    Avant toute opération, <a href="{"acces_smtp.php"|url}">il faut avoir activé ton compte SMTP/NNTP</a>.
  </span>
</div>
<br />

{if $smarty.get.doc eq 'smtp' || $smarty.get.doc eq 'all'}
<h1>
  La configuration pour utiliser le serveur SMTP de Polytechnique.org
</h1>

<table summary="Première étape" cellpadding="5">
<tr> 
  <td colspan="2">
    <img src="{"images/docs_compte1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
      1. Dans le menu principal d'Outlook Express, choisis le sous-menu 
      <strong>&quot;Comptes&quot;</strong>.
  </td>
  <td>
      2. La fenêtre qui s'affiche à l'écran suivant montre la liste des comptes 
      actuellement paramétrés.
  </td>
</tr>
</table>

<hr />

<table summary="Deuxième étape" cellpadding="5">
<tr> 
  <td>
    <img src="{"images/docs_compte2.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
      Un compte est désigné par un nom, ici c'est <em>adupont@mail.com</em> 
      qui désigne le compte utilisé dans l'exemple. Le plus souvent,
      la différence entre deux comptes est l'adresse e-mail d'envoi uniquement,
      mais parfois, les comptes se différencient aussi par les serveurs 
      utilisés pour recevoir ou envoyer un e-mail. C'est ce que nous allons faire 
      ici. <br /><br />
      Sélectionne le compte que tu utilises pour envoyer ton courrier puis 
      clique sur le bouton <strong>Propriétés</strong>.
  </td>
</tr>
</table>

<hr />

<table summary="Troisième étape" cellpadding="5">
<tr> 
  <td>
    <p>
      Cet écran permet d'éditer directement tous les paramètres du compte.
    </p>
    <p>
      Dans l'onglet <strong>&quot;Général&quot;</strong>, on trouve l'adresse 
      d'envoi du compte, et le <strong>&quot;Nom&quot;</strong> affich&eacute;.
    </p>
    <p>
      La petite case <strong>&quot;Inclure ce compte&quot; </strong>est importante. 
      Si tu la coches, cela veut dire que ce compte est réel et pas 
      seulement formel et Outlook Express va aller vérifier la présence 
      de messages sur le serveur POP3 (courrier entrant). Si elle n'est 
      pas cochée, le compte sert uniquement pour envoyer un mail avec 
      l'adresse e-mail spécifiée, qui sera utilisée aussi pour la réponse.
    </p>
  </td>
  <td>
    <img src="{"images/docs_smtp1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr />

<table summary="Quatrième étape" cellpadding="5">
<tr> 
  <td class="middle">
    <p>
      1. Dans l'onglet <strong>&quot;Serveurs&quot;</strong>, indique 
      <strong>ssl.polytechnique.org</strong> comme serveur SMTP
      et coche la case <strong>&quot;Mon serveur nécessite une 
      authentification&quot;</strong>.
    </p>
    <p>
      2. Dans la case <strong>&quot;Courrier entrant (POP3)&quot;</strong>
      indique le serveur POP du compte mail où tu redirige ton
      courrier (par exemple le serveur de courier entrant de ton
      fournisseur d'accès Internet).
    </p>
    <p>
      3. Tu peux alors cliquer sur le bouton <strong>&quot;Paramètres...&quot;</strong>
    </p>
  </td>
  <td>
    <img src="{"images/docs_smtp2.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>
<hr />
<table summary="Cinquième étape" cellpadding="5">
<tr> 
  <td>
    <p>
      La boîte ci-contre s'affiche alors. Indique ton <em>login</em> 
      et ton mot de passe,
    </p>
    <p>
      puis clique sur <strong>&quot;OK&quot;</strong>
    </p>
  </td>
  <td>
    <img src="{"images/docs_smtp3.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>
<hr />
<table summary="Sixième étape" cellpadding="5">
<tr> 
  <td>
    <p>
      Enfin, dans l'onglet <strong>&quot;Avancée&quot;</strong>, spécifie le port <strong>465</strong>
      pour le <strong>Courrier sortant (SMTP)</strong> et coche la case 
      <strong>&quot;Ce serveur utilise une connexion SSL&quot;</strong>.
    </p>
    <p class="erreur">
      Cette dernière étape est indispensable, sinon ton mot de passe 
      risque de ne pas être chiffré lors de l'envoi de courriels.
    </p>
  </td>
  <td>
    <img src="{"images/docs_smtp4.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>
<hr />

Et maintenant quelques remarques :
<ul>
	<li>
		<p>
			Le port de communication avec le serveur SMTP est officiellement le port
			587. Cependant, certaines versions d'Outlook Express ne fonctionnent
			qu'avec le port 465. L'équipe de Polytechnique.org ne peut qu'insister
			sur le fait que les mises à jour sont importantes et doivent être
			effectuées.
		</p>
	</li>
	<li>
		<p>
			Certaines <abbr title="direction des systèmes informatiques">DSI</abbr>
			locales interdisent l'utilisation de ports inférieurs à 1024. Il faut
			alors spécifier comme numéro de port non pas 587 ou 465, mais 2525 (ne
			fonctionne pas avec les anciennes versions de MSOE).
		</p>
	</li>
</ul>

<hr />
<table summary="Conclusion" cellpadding="5">
<tr> 
  <td>
    <p>
      Voilà, c'est terminé, tes messages sont maintenant envoyés par 
      Polytechnique.org, la connexion est authentifiée et chiffrée jusqu'à 
      notre serveur, donc ni ton mot de passe ni ton mail ne passe en clair
      entre toi et nous.
    </p>
    <p>
      La première fois que tu enverras un mail par notre serveur tu auras 
      certainement un message t'expliquant que notre certificat n'est pas signé 
      par une autorité de confiance, c'est normal. Nous allons essayer de changer 
      cela mais de toute façon cela n'influe pas sur la sécurité du système. 
      Indique que tu fais confiance à notre certificat.
    </p>
  </td>
</tr>
</table>
<br />
{/if}
{if $smarty.get.doc eq 'nntp' || $smarty.get.doc eq 'all'}
<h1>
  <a id="nntp">La configuration pour utiliser le serveur NNTP de Polytechnique.org</a>
</h1>

<table summary="Première étape" cellpadding="5">
<tr> 
  <td colspan="2">
    <img src="{"images/docs_compte1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td class="half">
      1. Dans le menu principal d'Outlook Express, choisis le sous-menu 
      <strong>&quot;Comptes&quot;</strong>.
  </td>
  <td>
      2. La fenêtre qui s'affiche à l'écran suivant montre la liste des comptes 
      actuellement paramétrés.
  </td>
</tr>
</table>
<hr />
<table summary="Deuxième étape" cellpadding="5">
<tr> 
  <td colspan="2">
    <img src="{"images/docs_news1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td class="half">
      1. Un compte est désigné par un nom de serveur. Il est possible que tu aies une liste
      vide la première fois que tu ouvres cette boite.
      Ici tu vois à quoi tu devras arriver en fin de configuration.
  </td>
  <td>
      2. Choisis d'ajouter un nouveau serveur de news comme montré sur l'image,
      en choisissant <strong>Ajouter</strong>, puis <strong>News</strong>
  </td>
</tr>
</table>

<hr />

<table summary="Troisième étape" cellpadding="5">
<tr> 
  <td colspan="2">
    <img src="{"images/docs_news2.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td class="half">
      1. Tu vas alors arriver à l'écran de configuration suivant
      (après avoir éventuellement du cliquer plusieurs fois sur <strong>suivant</strong>).
  </td>
  <td>
      2. Choisis <strong>ssl.polytechnique.org</strong> comme serveur puis clique autant de fois que nécessaire
      sur <strong>Suivant</strong>, en remplissant les champs qui te seront demandés. Valide par <strong>Terminer</strong>
      à la fin.
  </td>
</tr>
</table>
<hr />
<table summary="Quatrième étape" cellpadding="5">
<tr> 
  <td>
    <p>
      Il faut ensuite aller changer quelques options pour pouvoir utiliser les forums.
      Retourne dans le menu <strong>"Outils/Comptes"</strong> du début, puis choisis de modifier
      les <strong>"Propriétés"</strong> du compte de News que tu viens de créer.
    </p>
    <p>
      Choisis alors l'onglet <strong>"Serveur"</strong> et remplis le comme sur la capture
      d'écran. Le <em>login</em> est ton identifiant <em>prenom.nom</em> et le mot
      de passe, le <a href="{"acces_smtp.php"|url}">mot de passe de ton compte NNTP/SMTP</a>.
    </p>
  </td>
  <td>
    <img src="{"images/docs_news3.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>
<hr />
<table summary="Cinquième étape" cellpadding="5">
<tr> 
  <td>
    <p>
      Dernière étape, choisis l'onglet <strong>"Avancé"</strong> et coche la case
      <strong>ce serveur nécessite une connexion sécurisée (SSL)</strong>,
      puis clique sur <strong>&quot;OK&quot;</strong>.
      Tu es alors prêt à utiliser les news de polytechnique.org.<br />
      Bonne lecture !
    </p>
  </td>
  <td>
    <img src="{"images/docs_news4.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
