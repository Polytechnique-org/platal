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
        $Id: newsletter_pattecassee.tpl,v 1.3 2004-08-31 11:25:39 x2000habouzit Exp $
 ***************************************************************************}


<div class="rubrique">
  Vérifier une patte cassée suite à l'envoi de la newsletter
</div>

{dynamic}

{$erreur}

{if $smarty.post.email}

{if $no_more}
  <p class="erreur">
  Désolé mais plus personne n'utilise l'adresse {$email} comme adresse de redirection.
  Il est donc probable que ce problème de redirection ait été corrigé.
  </p>
{elseif $nb_emails}
  <p class="erreur">
  <a href="{"x.php?x=$username"|url}">{"$prenom $nom (X$promo)"}</a>
  a à l'heure actuelle {$nb_emails} adresse(s) email(s) de redirection active(s)
  en dehors de celle que tu as indiquée.
  Cela ne veut pas forcément dire qu'il les avait déjà activées
  lorsque la newsletter a été envoyée, mais c'est fort probable.
  </p>
  <p class="erreur">
  Pour lui envoyer un mail qui l'avertira de son adresse en panne,
  <a href="{$smarty.server.PHP_SELF}?email={$email}&amp;action=mail">il suffit de cliquer !</a>
  </p>
{else}
  <p class="erreur">
  Désolé, mais <a href="{"x.php?x=$username"|url}">{"$prenom $nom (X$promo)"}</a>,
  n'a actuellement aucune adresse email de redirection active
  autre que celle que tu viens de rentrer.
  L'idéal serait de contacter son kessier de promo pour l'en avertir
  et essayer de retrouver la trace de ce camarade !!
  </p>
{/if}

{/if}

{/dynamic}

<p>
Rentre dans la zone de saisie ci-dessous l'adresse email qui est revenue
en erreur suite à la distribution de la newsletter :
</p>
<br />
<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="tinybicol" cellpadding="3" summary="Saisie email en panne">
    <tr><th>Adresse email défectueuse</th></tr>
    <tr><td class="center"><input type="text" name="email" size="40" maxlength="70" /></td></tr>
    <tr><td class="center"><input type="submit" value="Ok" /></td></tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
