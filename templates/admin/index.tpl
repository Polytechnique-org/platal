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
        $Id: index.tpl,v 1.12 2004-10-24 14:41:12 x2000habouzit Exp $
 ***************************************************************************}


<h1>Administration Polytechnique.org</h1>

<table class="bicol" cellpadding="3" summary="Système">
  <tr><th>Syst&egrave;me</th></tr>
  <tr class="impair"><td>
      <strong>Postfix : </strong>
      <a href="postfix_blacklist.php">Blacklist</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="postfix_whitelist.php">Whitelist masspam</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="postfix_delayed.php">Retardés par masspam</a>
  </td></tr>
  <tr class="pair"><td>
      <strong>Statistiques : </strong>
      <a href="../stats/admin.html">Syst&egrave;me</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="../parselog.php">Logs Postfix</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="../webalizer/">Webalizer</a>
  </td></tr>
  <tr class="impair"><td>
      <strong>Sécurité : </strong>
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
      <a href="evenements.php">événements</a>
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
      <a href="newsletter.php">Liste</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="newsletter_cats.php">Catégories</a> &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="newsletter_pattecassee.php">Adresses en panne</a>  
  </td></tr>
  <tr class="impair"><td>
      <strong>Administrer : </strong>
      <a href="gerer_auth-groupex.php">Auth Groupes X</a>&nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="lists.php">Listes</a>
  </td></tr>
  <tr class="pair"><td>
      <strong>Valider demandes : </strong>
      <a href="valider.php">Valider</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="evenements.php">événements</a>
  </td></tr>
  <tr class="impair"><td>
      <strong>Trésorerie : </strong>
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
