{* $Id: doc_nntp.tpl,v 1.1 2004-01-27 16:34:50 x2000habouzit Exp $ *}

<div class="rubrique">Les forums de Polytechnique.org</div>
<p class="normal">
  Polytechnique.org propose un serveur de news (forums de discussion)
  sécurisé <strong>ouvert à tous les inscrits</strong> qui ont
  <strong><a href="{"acces_smtp.php"|url}">activé leur compte SMTP/NNTP</a></strong>.
</p>

<div class="ssrubrique">A quoi sert le serveur de forums ?</div>
<p class="normal">
  Le serveur de forums te permet de consulter les forums de discussion de
  Polytechnique.org depuis ton logiciel de courrier électronique (Outlook
  Express, Netscape...).
</p>

<div class="ssrubrique">Quel logiciel utiliser ?</div>
<p class="normal">
  Nous avons testé les logiciels capables de lire les forums de discussion les plus courants.  La colonne <strong>O</strong> indique les logiciels qui fonctionnent, la colonne <strong>N</strong> ceux qui ne fonctionnent pas.
</p>
<p class="normal"><em><small>
  La colonne <strong>S</strong> s'adresse aux utilisateurs aguerris et indique la possibilité de faire fonctionner le logiciel de news avec stunnel. La <a href="#stunnel">procédure</a> est décrite plus bas.</small></em>
</p>

<table class="bicol" summary="liste des clients" align="center"cellpadding="4">
<tr>
  <th>Logiciel</th>
  <th>O</th>
  <th>N</th>
  <th>S</th>
</tr>
<tr class="impair">
  <td>Gnus</td><td>x</td><td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr class="pair">
  <td>Gravity / SuperGravity</td><td>&nbsp;</td><td>&nbsp;</td><td>x</td>
</tr>
<tr class="impair">
  <td>KNode</td><td>&nbsp;</td><td>&nbsp;</td><td>x</td>
</tr>
<tr class="pair">
  <td>Lotus Notes 5/6</td><td>x</td><td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr class="impair">
  <td>Microsoft Outlook Express 4.x/5.x/6.x</td><td>x</td><td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr class="pair">
  <td>Mozilla 1.x</td><td>x</td><td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr class="impair">
  <td>Netscape Communicator 4.x</td><td>x</td><td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr class="pair">
  <td>Netscape 6.x/7.x</td><td>x</td><td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr class="impair">
  <td>Opera 6.x</td><td>x</td><td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr class="pair">
  <td>Pan</td><td>&nbsp;</td><td>&nbsp;</td><td>x</td>
</tr>
<tr class="impair">
  <td>slrn</td><td>x</td><td>&nbsp;</td><td>x</td>
</tr>
<tr class="pair">
  <td>sylpheed / sylpheed-claws</td><td>x</td><td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr class="impair">
  <td>Xnews</td><td>&nbsp;</td><td>&nbsp;</td><td>x</td>
</tr>
</table>

<br />
<div class="ssrubrique">Comment me connecter ?</div>
<p class="normal">
  Avant de configurer ton lecteur, il faut avoir accepté le certificat SSL de
  Polytechnique.org. <em><a href="{"docs/doc_ssl.php"|url}">Comment faire ?</a></em>.
</p>
<p class="normal">
  Pour te connecter, tu as besoin des param&egrave;tres suivants:
</p>
<ul>
  <li><u>Serveur</u> : <code>ssl.polytechnique.org</code></li>
  <li>Utiliser une connexion <u>s&eacute;curis&eacute;e</u> (SSL, port 563)</li>
  <li style="text-align:justify;">Ce serveur demande &agrave; ce que tu t'identifies : utilise ton identifiant (prenom.nom) et le mot de passe que tu as choisi pour le service SMTP/NNTP.</li>
</ul>
<p class="normal">
  La configuration pas à pas, images à l'appui :
</p>
<ul>
  <li><a href="{"docs/doc_oe.php?doc=nntp"|url}">Outlook Express</a></li>
  <li><a href="{"docs/doc_nn.php?doc=nntp"|url}">Netscape</a></li>
</ul>

<div class="ssrubrique"><a name="stunnel">Utiliser stunnel</a></div>
<p class="normal">
  Certains logiciels de news sont capables de s'authentifier mais ne reconnaisent pas les connexions sécurisées de type SSL.  Il est possible de faire fonctionner ces logiciels à l'aide de <a href="http://www.stunnel.org/">stunnel</a> qui gère la couche sécurisée.
</p>
<table class="bicol" summary="conf stunnel" width="95%" align="center">
<tr>
  <th>
    Pour les versions 3.x
  </th>
</tr>
<tr>
  <td>
  <p class="normal">Tu peux <a href="http://www.stunnel.org/">télécharger</a>
  stunnel et une fois celui-ci installé, taper la commande :<br />
  <code>stunnel -c -d localhost:119 -r ssl.polytechnique.org:563</code></p>
  </td>
</tr>
<tr>
  <th>
    Pour les versions 4.0x (GNU/linux)
  </th>
</tr>
<tr>
  <td>
    Il suffit d'éditer stunnel.conf et d'y ajouter les lignes suivantes :
<pre>    # location of pid file
    pid = /var/run/stunnel.pid
    # user to run as
    setuid = root
    setgid = root
    
    # Use it for client mode
    client = yes
    
    [nntps]
    accept  = localhost:119
    connect = ssl.polytechnique.org:563
    TIMEOUTclose = 0</pre>
    Ensuite, il suffit d'exécuter en tant que <em>root</em> la commande : <code>stunnel&nbsp;/etc/stunnel.conf</code>
    <br /><br />
    Il faut noter que la plupart des distributions utilisent stunnel, et ont créé un script de lancement
    automatique de stunnel par : <code>/etc/init.d/stunnel&nbsp;start</code>
  </td>
</tr>
<tr>
  <th>
    Pour les versions 4.0x (Windows)
  </th>
</tr>
<tr>
  <td>
    Il suffit d'éditer stunnel.conf et d'y ajouter les lignes suivantes :
    <pre>    # Use it for client mode
    client = yes
    
    [nntps]
    accept  = localhost:119
    connect = ssl.polytechnique.org:563
    TIMEOUTclose = 0</pre>
    <br />
    Si tu le souhaites, tu peux placer stunnel dans le groupe démarrage, 
    il sera lancé automatiquement.
  </td>
</tr>
</table>
<p class="normal">
Ceci met en place un "tunnel" entre ton port local 119 et le port de NNTP sécurisé de Polytechnique.org.
Il ne te reste alors plus qu'à indiquer à ton logiciel de forums que le serveur est "localhost" sur le port 119.
</p>

<div class="ssrubrique">slrn (GNU/linux)</div>
<p class="normal">
  Le cas de slrn est particulier. Il est par défaut compilé sans le support du ssl, et on peut utiliser la méthode indiquée ci-dessus.
</p>
<p class="normal">
	Mais il est aussi possible de compiler slrn avec le support du ssl, auquel
	cas il suffit d'indiquer à slrn : <code>snews://ssl.polytechnique.org/</code> comme
	serveur. Tout ceci est expliqué sur
  <a href="http://slrn.sourceforge.net/docs/README.SSL">la documentation officielle</a> (en anglais).
</p>

<div class="ssrubrique">Attention !</div>
<p class="normal">
  Les forums de discussion ne sont pas considérés comme un service prioritaire (contrairement aux adresses à vie) et donc pourra être interrompu pour de courtes périodes si nous ne pouvons pas faire autrement.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
