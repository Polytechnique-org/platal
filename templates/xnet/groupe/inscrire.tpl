{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>Demande d'inscription à {$asso.nom}</h1>

{if $smarty.request.u && $admin && $show_form}

<h2>
  Demande de la part de : {$prenom} {$nom} (X{$promo})
  <a href="https://www.polytechnique.org/profile/{$smarty.request.u}">Voir sa fiche</a>
</h2>
<form action="{$smarty.server.PHP_SELF}" method="post">
  <input type="hidden" name="u" value="{$smarty.request.u}" />
  <input type="submit" value="Accepter" name="accept" />
  <br />
  ou bien
  <br />
  <input type="submit" value="Refuser avec le motif ci-dessous" name="refuse" />
  <textarea cols="70" rows="8" name="motif"></textarea>
  <br />
</form>

{elseif $smarty.post.inscrire}

<p class="descr">
<strong>Ta demande d'inscription a bien été envoyée !</strong> Tu seras averti par email de la suite qui lui sera donnée.
<p>
<p class="descr">[<a href="asso.php">Retour à la page d'accueil de {$asso.nom}</a>]</p>

{else}

<p class="descr">
Pour t'inscrire à {$asso.nom}, il te faut en demander l'autorisation aux animateurs du groupe via le
formulaire ci-dessous. Vérifie et corrige au besoin les différents champs, puis clique sur
[&nbsp;m'inscrire&nbsp;]
</p>
<form action="{$smarty.server.PHP_SELF}" method="post">
  <p class="descr">
  <strong>OUI, je souhaite être inscrit au groupe {$asso.nom}</strong>
  </p>
  <p class="descr">
  Indique ci-après <strong>tes motivations</strong> qui seront communiquées aux animateurs du groupe :
  </p> <textarea cols=80 rows=12 name="message">
Chers Camarades,

Je souhaite m'inscrire à {$asso.nom}.

Merci d'avance d'avoir la gentillesse de valider mon inscription.

Bien cordialement,
{$smarty.session.prenom} {$smarty.session.nom} (X{$smarty.session.promo})

--
Ma fiche sur Polytechnique.org :
https://www.polytechnique.org/profile/{$smarty.session.forlife}
</textarea>
  <div class="center">
    <input type="submit" name="inscrire" value="M'inscrire !" />
    &nbsp;
    <input type="reset" value="Annuler" />
  </div>
</form>

{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
