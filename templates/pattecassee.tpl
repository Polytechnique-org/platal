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


{dynamic on="0$erreur"}
<p class="erreur">{$erreur}</p>
{/dynamic}

<h1>
  Vérifier une patte cassée
</h1>

{dynamic}
{if $x && $x.nb_mails}
  <p class="erreur">
    Ton correspondant a à l'heure actuelle {$x.nb_mails} adresse(s) email(s) de redirection active(s)
    en dehors de celle que tu nous as communiquée. Cela ne veut pas forcément dire qu'il les avait
    déjà activées lorsque tu as envoyé ton email, mais c'est fort probable.
  </p>
  <p class="erreur">
    Nous pensons qu'il serait une bonne idée de le prévenir que cette adresse email ne fonctionne plus.
    Si tu veux que nous lui envoyions un mail automatique de ta part pour le prévenir,
    <a href="{$smarty.server.PHP_SELF}?email={$email}&amp;action=mail">clique sur ce lien</a>.
  </p>
{elseif $x}
  <p class="erreur">
    Désolé, mais ton correspondant, {$x.prenom} {$x.nom} (X{$x.promo}),
    n'a actuellement aucune adresse email de redirection active autre que celle que tu viens de rentrer.
    Nous t'invitons à prendre contact avec lui autrement que par email,
    l'idéal étant de l'informer si possible que sa patte Polytechnique.org est cassée...!
  </p>
{elseif $email}
<p class="erreur">
  Désolé mais plus personne n'utilise l'adresse {$email} comme adresse de redirection.
  Nous ne pouvons donc malheureusement te fournir aucune information...
</p>
{/if}
{/dynamic}

<p>
  <strong>Qu'est-ce qu'une patte cassée ?</strong>
</p>
<p>
    Cette page sert à <strong>analyser les messages d'erreur</strong> que tu reçois
    lorsque tu envoies un mail à des utilisateurs de Polytechnique.org. Plus
    précisément, si après avoir rédigé un email, tu reçois en retour un message
    t'indiquant que l'un des destinataires n'a pas eu ton message sur l'une de
    ses adresses de redirections, nous allons pouvoir te dire s'il a reçu ton
    email sur une autre adresse de redirection...!
</p>
<p>
    Pour plus d'explications concernant cette page, nous t'invitons à consulter
    la <a href="{"docs/doc_patte_cassee.php"|url}">documentation suivante</a>
</p>
<p>
    Rentre dans la zone de saisie ci-dessous l'adresse email à laquelle ton
    courrier n'a pas été distribué puis valide. Nous te dirons si le
    destinataire possède d'autres adresses de redirection grâce auxquelles il a
    tout de même eu ton message.
</p>
<br />
<div class="center">
  <form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="tinybicol" cellpadding="3" summary="Saisie email en panne">
    <tr>
      <th>Adresse email défectueuse</th>
    </tr>
    <tr>
      <td><input type="text" name="email" size="50" maxlength="50" /></td>
    </tr>
    <tr>
      <td class="center"><input type="submit" value="Ok" /></td>
    </tr>
  </table>
  </form>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
