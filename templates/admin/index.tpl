{* $Id: index.tpl,v 1.5 2004-08-30 09:24:24 x2000habouzit Exp $ *}

<div class="rubrique">Administration Polytechnique.org</div>

<table class="bicol" cellpadding="3" summary="Système">
  <tr><th>Syst&egrave;me</th></tr>
  <tr class="impair"><td>
      <strong>Postfix : </strong>
      <a href="postfix_blacklist.php">Blacklist</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="postfix_perm.php">Permissions</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="postfix_retardes.php">Retards</a>
  </td></tr>
  <tr class="pair"><td>
      <strong>Statistiques : </strong>
      <a href="../stats/admin.html">Syst&egrave;me</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="../parselog.php">Logs Postfix</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="../webalizer/">Webalizer</a>
  </td></tr>
  <tr class="impair"><td>
      <strong>S&eacute;curit&eacute; : </strong>
      <a href="logger.php">Logs des sessions</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="logger_actions.php">Actions</a>
  </td></tr>
</table>

<br />
<table class="bicol" cellpadding="3" summary="Système">
  <tr><th>Contenu du site</th></tr>
  <tr class="impair"><td>
      <strong>Utilisateurs : </strong>
      <a href="utilisateurs.php">Gestion/SU/Logs</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="homonymes.php">Homonymes</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="ax-xorg.php">AX/X.org</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="deces_promo.php">Décès</a>
  </td></tr>
  <tr class="pair"><td>
      <strong>Infos dynamiques : </strong>
      <a href="gerer_coupure.php">Coupures</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="evenements.php">&Eacute;v&eacute;nements</a>
  </td></tr>
  <tr class="impair"><td>
      <strong>Champs profil : </strong>
      <a href="gerer_applis.php">Formations</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="gerer_binets.php">Binets</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="gerer_groupesx.php">Groupes X</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="gerer_skins.php">Skins</a>
  </td></tr>
  <tr class="pair"><td>
      <strong>Newsletter : </strong>
      <a href="newsletter_prep.php">Pr&eacute;paration</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="newsletter_archi.php">Archives</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="newsletter_pattecassee.php">Adresses en panne</a>  
  </td></tr>
  <tr class="impair"><td>
      <strong>Administrer : </strong>
      <a href="gerer_auth-groupex.php">Auth Groupes X</a>&nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="../listes/gere_listes.php">Listes</a>
  </td></tr>
  <tr class="pair"><td>
      <strong>Valider demandes : </strong>
      <a href="valider.php">Valider</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="evenements.php">&Eacute;v&eacute;nements</a>
  </td></tr>
  <tr class="impair"><td>
      <strong>Tr&eacute;sorerie : </strong>
      <a href="../trezo/gere_operations.php">Comptes</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="gerer_paiement.php">Paiements</a>
  </td></tr>
</table>

<br />
<table class="bicol" cellpadding="3" summary="Système">
  <tr><th>Développement</th></tr>
  <tr class="impair">
    <td style="width:4em;"><strong>Trackers : </strong>
      <a href="http://trackers.polytechnique.org/">trackers</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="http://support.polytechnique.org/">tickets</a>
    </td>
  </tr>
  <tr class="pair">
    <td><strong>CVS : </strong>
      <a href="http://dev.m4x.org/cvs/">ViewCVS</a>
    </td>
  </tr>
</table>

<br />
<table class="bicol" cellpadding="3" summary="Système">
  <tr><th>Gestion et entretien</th></tr>
  <tr class="impair"><td>
      <strong>Reformatage Prenom NOM : </strong>
      <a href="FormatePrenomNOM.php">Table
        auth_user_md5</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="FormatePrenomNOM2.php">Table identification</a>
  </td></tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
