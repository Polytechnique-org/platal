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


<p>
    [<a href="{"docs/doc_nn.php?doc=smtp"|url}">Configuration du smtp</a>]
    [<a href="{"docs/doc_nn.php?doc=nntp"|url}">Configuration du nntp</a>]
    [<a href="{"docs/doc_nn.php?doc=all"|url}">Doc. complète (gros)</a>]
</p>
<h1>
  Utiliser le SMTP sécurisé et le NNTP sécurisé avec Mozilla (ou Netscape 7+)
</h1>
<h2>Prérequis</h2>
<p>
Les copies d'écran ont été réalisées avec la version 1.7.3 sous Windows, mais restent valables pour
les autres versions de Mozilla sous d'autres systèmes d'exploitation.
Cette page est tout à fait transposable à Netscape 6/7.
</p>
<p>
    Tous les services de polytechnique.org étant sécurisés, il faut  commencer par faire accepter à
    ton système d'exploitation les certificats de sécurités de polytechnique.org. Pour ceci, suis
    les instructions de la <a href="{"docs/doc_ssl.php"|url}">documentation ssl</a>.
</p>
<p>
    Il faut ensuite activer <a href="{"acces_smtp.php"|url}">ton compte SMTP/NNTP</a>.
    Dans la suite, ton <strong>login</strong> désigne le login que tu as utilise pour te connecter au site,
    et <strong>le mot de passe</strong> celui que tu as indiqué lors de
    l'<a href="{"acces_smtp.php"|url}">activation de ton compte SMTP/NNTP</a>.
</p>
<h2>SMTP, NNTP, qu'est-ce ?</h2>
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
<h1>
  Utiliser le SMTP sécurisé
</h1>

<table summary="Première étape" cellpadding="5">
<tr>
  <td colspan="2" class='center'>
    <img src="{"images/docs_moz1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
      1. Dans le module de courier de Mozilla, choisis le sous-menu 
      <strong>&quot;&Eacute;dition/Paramètres des comptes courriers et forums&quot;</strong>.
  </td>
  <td>
      2. Remplis ensuite les paramètres du <strong>Serveur sortant (SMTP)</strong> comme la copie
      d'écran ci-contre, en remplacant pierre.habouzit.2000 par ton alias à vie @polytechnique.org.
      <br />
  </td>
</tr>
</table>

<hr />

<table summary="Troisème étape" cellpadding="5">
<tr> 
  <td class='center'>
    <img src="{"images/docs_moz2.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr> 
  <td>
    Si tu envoyes un courriel, tu verras apparaître la fenêtre ci-dessus.
    Tape le mot de passe que tu as indiqué lors de l'<a href="{"acces_smtp.php"|url}">activation de ton compte</a>.
  </td>
</tr>
</table>

<hr /> 

Et maintenant quelques remarques :
<ul>
  <li>
    Il est possible d'utiliser le port 587 (en cochant l'option SSL au lieu de TLS).
  </li>
  <li>
    Certaines <abbr title="direction des systèmes informatiques">DSI</abbr>
    locales interdisent l'utilisation de ports inférieurs à 1024. Il suffit
    alors de spécifier comme numéro de port SMTP non pas 587, mais 2525.
  </li>
</ul>
{/if}
{if $smarty.get.doc eq 'nntp' || $smarty.get.doc eq 'all'}
<br />
<h1>
  Utiliser le NNTP sécurisé
</h1>

<table summary="Première étape" cellpadding="5">
<tr>
  <td colspan="3" class='center'>
    <img src="{"images/docs_moz_nntp1.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
    1. Dans le module de courier de Mozilla, choisis le sous-menu 
    <strong>&quot;&Eacute;dition/Paramètres des comptes courriers et forums&quot;</strong>.
  </td>
  <td>
    2. Clique sur <strong>'Ajouter un compte ...'</strong> et choisis alors <strong>Compte
      Forums</strong>.  clique ensuite sur <strong>suivant</strong>.
  </td>
  <td>
    3. Mozilla te demande ton nom et ton adresse mail, ce sont les coordonnées qui seront vu par les
    autres abonnés sur notre serveur.  Nous te conseillons donc d'utiliser ton adresse
    polytechnicienne !.  clique ensuite à nouveau sur <strong>suivant</strong>.
  </td>
</tr>
</table>

<table summary="Première étape" cellpadding="5">
<tr> 
  <td colspan="3" class='center'>
    <img src="{"images/docs_moz_nntp2.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
    1. Saisis alors le nom du serveur NNTP de Polytechnique.org :
    <strong>ssl.polytechnique.org</strong>, puis clique sur <strong>Suivant</strong>.
  </td>
  <td>
      2. Mozilla te demande alors comment tu veux nommer ce compte, tu peux laisser ce qu'il te
      propose par défaut et cliquer à nouveau sur <strong>Suivant</strong>.
  </td>
  <td>
    3. Un écran récapitule tes choix, tu n'as plus qu'à cliquer sur <strong>Terminer</strong>.
  </td>
</tr>
</table>

<hr />

<table summary="Deuxième étape" cellpadding="5">
<tr>
  <td class='center' colspan='2'>
    <img src="{"images/docs_moz_nntp3.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td>
    Il faut ensuite dire à mozilla que le serveur de Forum est sécurisé.  Pour ceci coche la case
    <strong>Utiliser une connexion sécurisée (SSL).</strong> comme sur la capture d'écran ci dessus.
    clique ensuite sur <strong>OK</strong>.
  </td>
  <td>
    En te positionnant sur la ligne <strong>ssl.polytechnique.org</strong> alors créée, tu as la
    possibilité de <strong>Gérer les inscriptions aux groupes de discussions</strong>.  Suis ce
    lien.
  </td>
</tr>
<tr>
  <td class='center' colspan='2'>
    <img src="{"images/docs_moz_nntp4.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td colspan='2'>
    La boite ci dessus apparait alors, donne alors un de tes <strong>alias</strong>
    @polytechnique.org, puis valide.
  </td>
</tr>
<tr>
  <td class='center' colspan='2'>
    <img src="{"images/docs_moz_nntp5.png"|url}" alt=" [ CAPTURE D'ECRAN ] " />
  </td>
</tr>
<tr>
  <td colspan='2'>
    La boite ci dessus apparait enfin, tape le mot de passe que tu as indiqué lors de
    l'<a href="{"acces_smtp.php"|url}">activation de ton compte</a>.
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
