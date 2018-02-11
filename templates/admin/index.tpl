{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<h1>Administration Polytechnique.org</h1>

<table class="bicol" cellpadding="3" summary="Services">
  <tr><th colspan="2">{icon name=wrench} Services</th></tr>
  <tr class="impair">
    <td class="titre">Postfix</td>
    <td>
      <a href="admin/postfix/blacklist">Blacklist</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/postfix/whitelist">Whitelist</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/postfix/delayed">Retardés</a>
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">Accès au site</td>
    <td>
      <a href="admin/auth-groupes-x">Auth Groupes X</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/logger">Logs des sessions</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/logger/actions">Actions</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/ipwatch">IP surveillées</a>
    </td>
  </tr>
  <tr class="impair">
    <td class="titre">Emails</td>
    <td>
      <a href="admin/lists">MLs</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/aliases">aliases</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/mx/broken">MX défaillants</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/emails/lost">Perdus de vue</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/emails/watch">Surveillés</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/emails/broken">Pattes cassées</a>
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">Forums</td>
    <td>
      <a href="admin/forums">Gestion des mises au ban</a>
    </td>
  </tr>
  <tr class="impair">
    <td class="titre">Trésorerie</td>
    <td>
      <a href="admin/payments">Paiements</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/reconcile">Virements</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/payments/bankaccounts">RIBs</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/payments/methods">Méthodes de paiement</a>
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">Divers</td>
    <td>
      <a href="admin/downtime">Coupures</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/icons">Icônes</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="site_errors">Erreurs d'exécution</a>
    </td>
  </tr>

</table>

<br />

<table class="bicol" cellpadding="3" summary="Utilisateurs">
  <tr><th colspan="2">{icon name=user_suit} Utilisateurs</th></tr>
  <tr class="impair">
    <td class="titre">Comptes</td>
    <td>
      <a href="admin/account/types">Types de comptes</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/accounts">Gestion des comptes</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/homonyms">Homonymes</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/deaths">Décès</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/name">Noms</a>
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">Administration</td>
    <td>
      <a href="admin/dead-but-active">Décédés actifs</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/xnet_without_group">Comptes xnet sans groupe</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/account/watch">Administrateurs/Désactivations</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/googleapps">Google Apps</a>
    </td>
  </tr>
  <tr class="impair">
    <td class="titre">Formations</td>
    <td>
      <a href="admin/phd">Doctorants</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/add_secondary_edu">Ajout de formation</a>
    </td>
  </tr>

  <tr><th colspan="2">{icon name=user_gray} Champs</th></tr>
  <tr class="impair">
    <td class="titre">Pays / Langues</td>
    <td>
      <a href="admin/geocoding/country">Pays</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/geocoding/language">Langues</a>
    </td>
  </tr>
  <tr class="impair">
    <td class="titre">Formation</td>
    <td>
      <a href="admin/education">Formations</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/education_field">Domaines de formation</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/education_degree">Niveau de formation</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/education_degree_set">Niveau par formation</a>
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">Emploi</td>
    <td>
      <a href="admin/jobs">Entreprises</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/corps_enum">Corps</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/corps_rank">Grade</a>
    </td>
  </tr>
  <tr class="impair">
    <td class="titre">Profil</td>
    <td>
      <a href="admin/binets">Binets</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/medals">Décorations</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/sections">Sections</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/networking">Networking</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/profile">Modifications récentes</a>
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">Compte</td>
    <td>
      <a href="admin/skins">Skins</a>
    </td>
  </tr>
</table>

<br />

<table class="bicol" cellpadding="3" summary="Contenu éditorial">
  <tr><th colspan="2">{icon name=page_edit} Editorial</th></tr>
  <tr class="impair">
    <td class="titre">Page d'accueil</td>
    <td>
      <a href="admin/tips">Astuces</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/events">Événements</a>
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">Newsletters</td>
    <td>
      <a href="admin/nls">Liste des NLs groupes</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/newsletter/">NL de X.org</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="comletter/admin/">Lettre de la communauté</a>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <a href="admin/url">Raccourcisseur d'url</a>
    </td>
  </tr>
  <tr class="impair">
    <td class="titre">Wiki</td>
    <td>
      <a href="admin/wiki">Pages et permissions</a>
    </td>
  </tr>
  <tr class="pair">
    <td class="titre">Sondages</td>
    <td>
      <a href="survey/admin">Gestion des sondages</a>
    </td>
  </tr>
  <tr class="impair">
    <td class="titre">Validations</td>
    <td>
      <a href="admin/validate/answers">Réponses automatiques</a>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
