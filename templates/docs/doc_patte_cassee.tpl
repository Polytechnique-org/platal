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
        $Id: doc_patte_cassee.tpl,v 1.5 2004/10/24 14:41:12 x2000habouzit Exp $
 ***************************************************************************}


<h1>
  Vérifier une patte cassée
</h1>
<p>
  <strong>Qu'est-ce qu'une patte cassée ?</strong>
</p>
<p>
    Tu peux choisir un nombre illimité d'adresses emails de redirection pour ton
    courrier reçu par Polytechnique.org : ces pattes de redirection peuvent parfois
    tomber en panne...! Par exemple, ton adresse de redirection @yahoo.fr ou
    @wanadoo.fr pourrait très bien s'arrêter de fonctionner temporairement ou
    définitivement (panne du fournisseur de boîte email, résililation de ton contrat
    avec ton fournisseur d'accès...).
</p>
<p>
    Nous t'aidons donc à <strong>analyser les messages</strong> d'erreurs que tu recois
    lorsque tu envoies un mail à des utilisateurs de Polytechnique.org. Plus
    précisément, si après avoir rédigé un email, tu reçois en retour un message
    t'indiquant que l'un des destinataires n'a pas eu ton message sur l'une de
    ses adresses de redirections, nous allons pouvoir te dire s'il a reçu ton
    email sur une autre adresse de redirection...!
</p>
<p>
    Nous pouvons t'aider si par exemple tu as envoyé un mail et l'un de tes
    correspondants a une adresse de redirection qui est devenue invalide. Tu
    veux alors sans doute savoir si le destinataire a tout de même reçu ton
    email sur une autre adresse de redirection.
</p>
<br />
<p>
  <strong>Comment se sert-on de ce service ?</strong>
</p>
<p>
    Rien ne vaut un exemple simple. imaginons que tu écrives à
    jean.dupont@polytechnique.org, et que tu recoives peu de temps après un mail
    du type :
</p>
<table summary="mail de bounce" class="bicol" cellspacing="0" cellpadding="10">
<tr class="pair">
<td>
<pre>
The original message was received at Thu, 23 Jan 2003 13:30:30 +0100 (MET)
from [129.104.218.132]

----- The following addresses had permanent fatal errors -----
&lt;jdupont@wanadoo.fr&gt;

----- Transcript of session follows -----
... while talking to smtp.wanadoo.fr.:
&gt;&gt;&gt; RCPT To:&lt;jdupont@wanadoo.fr&gt;
&lt;&lt;&lt; 550 RCPT TO:&lt;jdupont@wanadoo.fr&gt; User unknown
550 &lt;jdupont@wanadoo.fr&gt;... User unknown
</pre>
</td>
</tr>
</table>
<p>
    J'imagine que tu veux savoir si Jean Dupont a effectivement recu ton
    courrier grâce à une autre adresse de redirection. Il te suffit de te
    rendre sur la page des  <a href="{"pattecassee.php"|url}">pattes cassées</a>
    et tu soumets <strong>l'adresse de redirection</strong> qui a posé un problème
    (dans notre exemple il s'agit de <strong>jdupont@wanadoo.fr</strong>).
    On te dira si ton interlocuteur a d'autres adresses de redirections actives.
    On te proposera aussi un lien pour signaler à ton interlocuteur
    qu'une de ses adresses de redirections a un problème.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
