{* $Id: antispam.tpl,v 1.6 2004-08-26 14:44:43 x2000habouzit Exp $ *}

<div class="rubrique">
  Ton filtre anti-spam
</div>
<div class="ssrubrique">
  Qu'est-ce qu'un spam ? Comment m'en débarrasser ?
</div>
<p>
Un spam est un courrier électronique <strong>non sollicité</strong>. Ce peut-être un
message de publicité, une proposition commerciale, etc... qui t'est envoyé
par une personne que tu ne connais pas.<br />
Notre logiciel antispam tente de déterminer, parmi les courriers électroniques
que tu reçois, lesquels sont des spams, et lesquels n'en sont pas.
Trois réglages sont possibles :
<ul>
  <li>soit le logiciel est coupé et <strong>ne filtre pas du tout</strong> tes courriels,</li>
  <li>soit les spams détectés portent la mention [spam probable] dans leur
  objet, afin que tu puisses les <strong>identifier plus facilement</strong>,
  </li>
  <li>soit nous <strong>supprimons les courriels</strong> que tu reçois dont notre
  logiciel pense que ce sont des spams.
  </li>
</ul>
</p>
<form action="{$smarty.server.PHP_SELF}" method="post" name="filtre">
  <table class="tinybicol" summary="filtre anti-spam">
    <tr>
      <td>
        <strong>Choisis ton propre réglage :</strong><br />
        {dynamic}
        <input type='radio' name='statut_filtre' value='0' {if $filtre eq 0}checked="checked"{/if} />
        (1) le filtre anti-spam est coupé<br />
        <input type='radio' name='statut_filtre' value='1' {if $filtre eq 1}checked="checked"{/if} />
        (2) le filtre anti-spam est activé, et marque les mails<br />
        <input type='radio' name='statut_filtre' value='2' {if $filtre eq 2}checked="checked"{/if} />
        (3) le filtre anti-spam est activé, et élimine les mails détectés comme spams<br />
        {/dynamic}
      </td>
    </tr>
    <tr>
      <td class="center">
        <input type="submit" name="filtre" value="Valider le filtre anti-spam" />
      </td>
    </tr>
  </table>
</form>

<p>
Evidement, <strong>le système n'étant pas infaillible, il est possible qu'un
  message normal soit classé comme spam</strong>, auquel cas, si tu as choisi
l'option (3), tu perdras un message que tu aurais sans doute souhaité
recevoir.
Aussi, <em>nous te conseillons, au moins dans un premier temps, d'utiliser
  l'option (2)</em>, qui elle n'efface aucun message, et te donne juste une
indication visuelle des messages qui semblent être des spams.
<br />
Si après quelques temps d'utilisation de l'option (2), tu en es satisfait,
tu peux envisager d'opter pour l'option (3).
</p>

<div class="ssrubrique">
  Que faire si jamais je me rends compte que le filtre s'est trompé ?
</div>
<p>
Pour que le logiciel fonctionne bien, il est préférable de lui indiquer,
lorsqu'il s'est trompé, qu'il a fait une erreur ! Il est plutôt intelligent,
et en tirera une leçon si on lui signale ses fautes, pour moins se tromper
par la suite. L'aide de tous est donc la bienvenue.<br />
Si un courriel qui est un spam n'est pas détecté comme tel, réexpédie-le
à l'adresse <a href="mailto:spam@polytechnique.org">spam@polytechnique.org</a>
<strong>sous forme de pièce jointe</strong>.<br />
Inversement, si un message est considéré comme un spam alors que ce n'en est
pas un, il faut le réexpédier à l'adresse
<a href="mailto:nonspam@polytechnique.org">nonspam@polytechnique.org</a>
<strong>sous forme de pièce jointe</strong>.
Ainsi notre base de données de spams restera à jour, et, alors
que les spammers enverront des spams de plus en plus durs à détecter,
tous nos camarades bénéficieront d'un filtre anti-spam de meilleure qualité.
</p>
<h3 style="text-decoration: underline">Plus tu nous enverras tes spams, moins tu en recevras !!!</h3>

<div class="ssrubrique">
  Et techniquement, comment ça marche ?
</div>
<p>
Le filtre anti-spam tente de repérer les spams en fonction des mots
qu'il contiennent, il extrait donc les mots d'un message et les comparer
à deux ensembles de référence l'un contenant des spams, l'autre des
messages normaux. Il calcule ainsi une probabilité qu'un message soit
un spam et si cette probabilité est forte, ce courriel est considéré comme
un spam.
</p>
<p>
Le marquage est fait de deux manières :
<ul>
  <li>la chaîne "[spam probable]" est ajoutée au début du sujet pour permettre une reconnaissance visuelle facile des spams,</li>
  <li>un en-tête "X-Spam-Flag: YES" est ajouté au message pour permettre l'ajout d'un filtre dans ton lecteur de mail pour trier le spam dans une boîte indépendante, ce qui facilite la vérification que les spams marqués sont bien des spams.</li>
</ul>
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
