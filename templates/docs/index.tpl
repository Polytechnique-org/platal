{* $Id: index.tpl,v 1.6 2004-02-12 02:03:09 x2000habouzit Exp $ *}

<div class="rubrique">
  Documentations et Aides diverses
</div>

<table class="bicol" summary="Docs: Services" >
  <tr>
    <th colspan="2">Utilisation des services de Polytechnique.org
    </th>
  </tr>
  <tr class="impair">
    <td class="half">
      <div class="lien">
        <a href="doc_emails.php">Mes adresses de redirection</a>
      </div>
      <div class="explication">
        Comment les utiliser, à quoi servent elles, etc ...
      </div>
    </td>
    <td><div class="lien">
        <a href="doc_melix.php">Mon alias mail @melix.net</a>
      </div>
      <div class="explication">
        Quel intéret par rapport à mon adresse @polytechnique.org ?
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td>
      <div class="lien">
        <a href="doc_gratuits.php">Services d'emails gratuits</a>
      </div>
      <div class="explication">
        Pourquoi et comment choisir un service d'e-mail gratuit
      </div>
    </td>
    <td><div class="lien">
        <a href="doc_patte_cassee.php">Patte cassée</a>
      </div>
      <div class="explication">
        Détection des adresses de redirections en panne !
      </div>
    </td>
  </tr>
  <tr class="impair">
    <td>
      <div class="lien">
        <a href="doc_carva.php">Ma redirection de page WEB</a>
      </div>
      <div class="explication">
        Charte et utilisation de la redirection WEB http://www.carva.org/prenom.nom
      </div>
    </td>
    <td>
      <div class="lien">
        <a href="doc_forums.php">Utilisation des forums</a>
      </div>
      <div class="explication">
        Charte et règles de bon usage des forums de Polytechnique.org
      </div>
    </td>
  </tr>
</table>

<br />
<table class="bicol" summary="Docs: Services sécurisés">
  <tr>
    <th colspan="2">Utilisation des services <em>sécurisés</em> de Polytechnique.org
    </th>
  </tr>
  <tr class="impair">
    <td colspan="2">
      <div class="lien">
        <a href="doc_ssl.php">Certificat de sécurité</a>
      </div>
      <div class="explication">
        <strong>Avant toute chose</strong>, il faut configurer ton système pour accepter notre certificat de sécurité !
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td class="half">
      <div class="lien">
        <a href="doc_smtp.php">SMTP sécurisé</a>
      </div>
      <div class="explication">
        Le SMTP est la machine sur laquelle se connecte ton
        logiciel de courrier électronique pour envoyer le courrier. 
      </div>
    </td>
    <td><div class="lien">
        <a href="doc_nntp.php">NNTP sécurisé</a>
      </div>
      <div class="explication">
        Il permet de lire les <a href="{"banana/"|url}">forums</a> directement
        dans un logiciel comme Outlook Express ou Netscape.
      </div>
    </td>
  </tr>
</table>

<br />
<table class="bicol" summary="Docs: Services sécurisés">
  <tr>
    <th colspan="2">Utiliser des logiciels de courrier/news avec Polytechnique.org
    </th>
  </tr>
  <tr class="impair">
    <td class="half">
      <div class="lien">
        <a href="doc_oe.php">Outlook Express</a>
      </div>
      <div class="explication">
        Configurer Outlook Express pour utiliser le SMTP et le NNTP sécurisés de
        Polytechnique.org.
      </div>
    </td>
    <td><div class="lien">
        <a href="doc_nn.php">Netscape/Mozilla</a>
      </div>
      <div class="explication">
        Configurer Netscape ou Mozilla pour utiliser le SMTP et le NNTP sécurisés de
        Polytechnique.org.
      </div>
    </td>
  </tr>
</table>
<br />

{include file="docs/faq.tpl"}

{* vim:set et sw=2 sts=2 sws=2: *}
