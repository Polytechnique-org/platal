{* $Id: index.tpl,v 1.2 2004-07-17 10:14:56 x2000habouzit Exp $ *}


<div class="rubrique">Marketing Polytechnique.org</div>

<table class="bicol" cellpadding="3" summary="Système">
  <tr>
    <th>actions disponibles</th>
  </tr>
  <tr class="impair">
    <td>
      <span class="item">Premier contact : </span>
      <a href="utilisateurs_marketing.php">Chercher un non inscrit</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="promo.php">Marketing promo</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="envoidirect.php">Sollicitations faites</a>
    </td>
  </tr>
  <tr class="pair">
    <td>
      <span class="item">Relances : </span>
      <a href="ins_confirmees.php">Inscriptions confirmées</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="relance.php">Relance des ½-inscrits</a>
    </td>
  </tr>
  <tr class="impair">
    <td>
      <span class="item">Emails : </span>
      <a href="marketing_volontaire.php">Utiliser les adresses données par les inscrits</a>
    </td>
  </tr>
</table>

<br />

{dynamic}
<p class="normal">
Nombre d'X vivants d'après notre base de données : {$stats.vivants}<br />
Nombre d'X vivants inscrits à Polytechnique.org : {$stats.inscrits}<br />
Soit un pourcentage d'inscrits de : {$stats.ins_rate} %<br />
</p>

<p class="normal">
Parmi ceux-ci :<br />
Nombre d'X vivants depuis 1972 d'après notre base de données : {$stats.vivants72}<br />
Nombre d'X vivants depuis 1972 inscrits à Polytechnique.org : {$stats.inscrits72}<br />
Soit un pourcentage d'inscrits de : {$stats.ins72_rate} % <br />
</p>

<p class="normal">
Nombre de Polytechniciennes vivantes : {$stats.vivantes}<br />
Nombre de Polytechniciennes vivantes et inscrites : {$stats.inscrites} <br />
Soit un pourcentage d'inscrites de : {$stats.inse_rate} % <br />
</p>

<p class="normal">
Nombre d'inscrits depuis le début de la semaine : {$nbInsSem} <br />
Nombre d'inscriptions en cours (2ème phase non terminée) : {$nbInsEnCours} <br />
Nombre d'envois marketing effectués n'ayant pas abouti : {$nbInsEnvDir}
</p>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
