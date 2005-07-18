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


<p>
  [<a href="{"docs/doc_pocketpc.php?doc=smtp"|url}">Configuration du smtp</a>]
</p>

<h1>
  Utiliser le SMTP sécurisé avec Microsoft Windows Pocket PC
</h1>

<h2>Prérequis</h2>

<p>
  Cette documentation a été écrite pour Windows CE 4.20.0.
</p>

<p>
  Cependant, les principes de cette configuration sont toujours les mêmes
  dans les autres versions du logiciel et il est simple de leur transposer
  cette explication.
</p>

<p>
  Il faut ensuite activer <a href="{"acces_smtp.php"|url}">ton compte SMTP/NNTP</a> dans Polytechnique.org.
  Par la suite, ton <strong>login</strong> désigne l'identifiant que tu utilises pour te connecter au site,
  et <strong>le mot de passe</strong> celui que tu as indiqué lors de
  l'<a href="{"acces_smtp.php"|url}">activation de ton compte SMTP/NNTP</a>.
</p>

<h2>SMTP, NNTP, qu'est-ce ?</h2>
<p>
  Le serveur SMTP est la machine sur laquelle ton client de courrier électronique se
  connecte pour envoyer des mails. En général, ton fournisseur d'accès
  internet t'en propose un. Mais il arrive souvent que ces serveurs aient des
  limitations (notamment sur l'adresse mail que tu veux mettre dans le champ
  expéditeur). Pour tous ses inscrits, Polytechnique.org propose un serveur
  sécurisé, accessible depuis tout internet.
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
  <td class="middle">
    <p>
      Dans le menu démarrer, choisis la <strong>&quot;Boîte de Réception&quot;</strong>. Puis dans le menu <strong>Comptes</strong>, choisis <strong>&quot;Nouveau compte...&quot;</strong>.
    </p>
    <p>
      La procédure de création du compte se déroule en cinq étapes plus trois étapes d'options (nécessaires pour ce qui nous concerne).
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc1.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr />

<table summary="Deuxième étape" cellpadding="5">
<tr> 
  <td class="middle">
    <p>
      1. Dans la première étape de configuration de la messagerie, tu peux indiquer l'adresse mail qui sera indiquée dans les mails que tu envoies.
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc2.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr />

<table summary="Troisième étape" cellpadding="5">
<tr>
  <td class="middle">
    <p>
      2. On te propose alors de configurer automatiquement la messagerie. Accepte et passe à l'étape suivante.
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc3.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr />

<table summary="Quatrième étape" cellpadding="5">
<tr>
  <td class="middle">
    <p>
      3. La troisième étape te demande ton nom ainsi que ton nom d'utilisateur. Ce nom d'utilisateur doit obligatoirement être ton <strong>login</strong>. De même ton mot de passe doit être celui que tu as choisi lors de l'activation de ton compte SMTP sécurisé.
    </p>
    <p>
      Malheureusement cela t'oblige à avoir le même login pour ton compte mail que celui de ton compte polytechnique.org. Il faut également le même mot de passe.
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc4.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr />

<table summary="Cinquième étape" cellpadding="5">
<tr>
  <td class="middle">
    <p>
      4. L'étape quatre te permet de choisir le type de compte POP3 ou IMAP4. Tu peux choisir le type en fonction de ton hébergeur de mail : il s'agit d'un paramètre de réception et non d'envoi. Si tu ne sais pas quoi choisir, la plupart du temps il faut mettre POP3.
    </p>
    <p>
      Le nom du compte, n'apparaîtra nulle part dans les communications mais te permet simplement d'identifier ces paramètres sur ton PDA par rapport à d'autres comptes.
    </p>
    <p>
      Remarque : tu peux configurer plusieurs comptes mail sur ton PDA, mais tu ne peux te connecter qu'à un seul à la fois. Pour changer de compte, il faut d'abord se déconnecter du compte en cours.
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc5.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr />

<table summary="Sixième étape" cellpadding="5">
<tr> 
  <td class="middle">
    <p>
      5. Le serveur de courrier entrant correspond à ton hébergeur de mail. Par contre le serveur sortant doit être <strong>ssl.polytechnique.org</strong>.
    </p>
    <p>
      Clique maintenant sur Options pour paramétrer les paramètres de sécurité.
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc6.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

<hr />

<table summary="Septième étape" cellpadding="5">
<tr> 
  <td>
    <p>
      Nous te déconseillons de récupérer automatiquement les mails, notamment parce que ton PDA essaiera alors très régulièremet de se connecter à Internet, même si tu n'as pas démarré ta boîte de réception, ce qui peut est très désagréable.
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc7.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>
<hr />
<table summary="Huitième étape" cellpadding="5">
<tr> 
  <td class="middle">
    <p>
      Ces deux cases sont importantes à cocher. La <strong>connexion SSL</strong> établira un dialogue sécurisé entre le PDA et notre serveur pour ne pas envoyer ton mot de passe en clair.
      Comme pour le login la méthode de connexion est commune au courrier entrant et sortant. Mais dans le cas d'un PDA, il est important de récupérer ses mails de manière crypté également.
    </p>
    <p>
      Coche également la case pour l'<strong>authentification</strong>.
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc8.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>
<hr />
<table summary="Neufième étape" cellpadding="5">
<tr>
  <td class="middle">
    <p>
      Enfin, pour terminer tu peux choisir de ne récupérer que les en-têtes des mails que tu reçois. Ce qui évite de surcharger ton PDA. Tu pourras ensuite, au cas par cas, choisir de récupérer la totalité des messages qui t'intéressent.
    </p>
  </td>
  <td>
    <img src="{rel}/images/docs_pocketpc9.png" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
</table>

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
  </td>
</tr>
</table>
<br />
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
