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


<h1>Mes Redirections d'adresses emails</h1>
<h2>En quoi ça consiste, comment ça marche ?</h2>
<p>
    Polytechnique.org te fournit un service de redirection de tes mails à vie. Toute ta vie, tu auras
    l'adresse <strong>{dyn s=$smarty.session.forlife}@polytechnique.org</strong>
    à ta disposition. Cependant, il s'agit d'une redirection, il faut donc que tu aies une boite au
    lettres fonctionnelles pour recevoir ton courrier (il en existe des
    <a href="{"docs/doc_gratuits.php"|url}">gratuites</a>).
</p>
<p>
    Ainsi, durant toute ta vie, et malgré d'eventuels nombreux changements d'adresses réelles, tous
    tes correspondants pourront toujours te contacter sur
    <strong>{dyn s=$smarty.session.forlife}@polytechnique.org</strong> et 
    <strong>{dyn s=$smarty.session.forlife}@m4x.org</strong>.
</p>
<p>
    L'utilisation de ce service est très simple. Sur <a href="{"emails.php"|url}">cette
    page</a>, tu trouveras un formulaire pour ajouter de nouvelles adresses de redirection. Tous
    les courries envoyés sur tes adresses @polytechnique.org et @m4x.org seront redirigés vers
    <strong>toutes</strong> les boites de redirection que tu auras spécifiées <strong>et</strong>
    activées.
</p>

<br />

<h2>
  <a href="{"docs/doc_from.php"|url}">Utiliser mon adresse @polytechnique.org dans FROM:</a> (150 Ko)
</h2>
<p>
    Ce lien t'explique comment configurer facilement plusieurs comptes e-mail dans Outlook Express.
    Quel intérêt? Tu peux écrire des e-mails en choisissant l'identité de toi qui envoie le mail. Tu
    peux ainsi envoyer des mails en tant que "prenom.nom@polytechnique.org", ou "prenom.nom@m4x.org"
    ou encore ton e-mail professionnel, ou encore ton e-mail personnel. Ceci te permet de contrôler
    aussi où les gens te répondront.
</p>
<p>
    Et choisir ton identité peut être intéressant, on n'a pas toujours envie de montrer qui on est
    par son adresse e-mail. 
</p>
<br />

<h2>Se protéger du courrier non solicité (SPAM)</h2>
<p>
    Beaucoup d'entre vous se plaignent de recevoir du courrier non solicité, communément appelé
    SPAM. Très souvent, celà vient d'un manque de prudence. En effet, il faut bien faire attention
    de ne pas laisser son adresse mails trop facilement compréhensible par des robots.
</p>
<p>
    Il existe plusieurs manières de se prémunir de l'indexation de ton adresse mail par des robots :
</p>
<ul>
    <li>Utiliser son adresse <strong>@m4x.org</strong> plutot que @polytechnique.org, elle est plus discrète
    </li>
    <li>rendre son adresse mail invalide : par exemple, sur les forums Usenet, il n'est pas rare de
    rencontrer des adresses mails du type &lt;j.dupont@_NOSPAM_wanadoo.fr&gt;. Tous les humains
    sauront enlever le <em>_NOSPAM_</em> qui a été ajouté, mais les robots non.
    </li>
    <li>Il est aussi possible d'utiliser des services comme ceux de
    <a href="http://marreduspam.com/">http://marreduspam.com/</a> qui reste de loin le moyen le plus
    sur de camoufler son adresse aux robots trop curieux
    </li>
</ul>
<br />

<h2>Une infinité d'alias e-mails en polytechnique.org et m4x.org</h2>
<p>
    Lorsque tu écris à <strong>destinataire+truc@polytechnique.org</strong> ou
    <strong>destinataire_truc@polytechnique.org</strong> tout ce qui se trouve derrière le + ou le _
    est ignoré.
</p>
<p>
    Autrement dit, c'est comme écrire à <strong>destinataire@polytechnique.org</strong>.
    Tu peux te servir de cette infinité d'alias pour un tas d'usages différents. Citons-en deux.
</p>
<ul>
    <li>
    Sur le web, tu t'exposes à recevoir des spams en communiquant ton adresse. Mais si tu fournis à
    Amazon une adresse prenom.nom+amazon@m4x.org, lorsque tu recevras un mail (spam) sur l'adresse
    prenom.nom+amazon@ tu pourras savoir d'où vient la fuite... Et même éventuellement bloquer
    spécifiquement l'adresse prenom.nom+amazon et pas l'adresse prenom.nom.<br />
    Tu peux utiliser cette technique conjointement à celles citées ci-dessus.
    </li>
    <li>
    En combinant cette fonction avec les comptes ci-dessus, tu peux même te créer des comptes
    d'envoi de mail du style prenom.nom+chose@. Si tu fais un mail où tu attends de nombreuses
    réponses, tu peux par exemple l'écrire depuis prenom.nom+sondage et filtrer les réponses en
    fonction de l'adresse de utilisée.
    </li>
</ul>
<p><em>
    <strong>NB:</strong> Le _ a été ajouté car certains sites web refusent le + dans une adresse
    email, qui est pourtant parfaitement valide d'après les RFCs...
</em></p>
{* vim:set et sw=2 sts=2 sws=2: *}
