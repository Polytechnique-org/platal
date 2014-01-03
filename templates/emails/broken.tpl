{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

{if $neuneu}
<h2 class='erreur'>Erreur&nbsp;!</h2>

<p>
Tu as entré une adresse sur un de nos domaines ({#globals.mail.domain#}, {#globals.mail.domain2#}, {#globals.mail.alias_dom#},
{#globals.mail.alias_dom2#}) ce qui est invalide.
</p>

<p>
En effet, il faut nous donner l'adresse qui se cache derrière l'adresse polytechnicienne de ton
correspondant si tu veux que nous puissions te répondre.
</p>
{elseif t($user) && $user.nb_mails && $active}
<h2>Patte cassée</h2>
  <p>
    Ton correspondant a à l'heure actuelle <span class="erreur">{$user.nb_mails} adresse(s) email(s) de redirection active(s)
    en dehors de celle que tu nous as communiquée</span>. Cela ne veut pas forcément dire qu'il les avait
    déjà activées lorsque tu as envoyé ton email, mais c'est fort probable.
  </p>
  <p>
    Nous pensons qu'il serait une bonne idée de le prévenir que cette adresse email ne fonctionne plus.
    Si tu veux que nous lui envoyions un email automatique de ta part pour le prévenir,
    <a href="emails/broken/warn/{$email}?token={xsrf_token}">clique sur ce lien</a>.
  </p>
{elseif t($user) && $user.nb_mails && !$active}
<h2>Patte cassée</h2>
  <p>
    L'adresse email que tu as entrée a <span class="erreur">déjà été signalée comme cassée</span>. Ton correspondant a
    cependant actuellement <span class="erreur">{$user.nb_mails} adresse(s) email(s) de redirection active(s)</span>.
    Cela ne veut pas forcément dire qu'il les avait déjà activées lorsque tu as envoyé ton email, mais c'est fort probable.
  </p>
  <p>
    Nous pensons qu'il serait une bonne idée de le prévenir que cette adresse email ne fonctionne plus.
    Si tu veux que nous lui envoyions un email automatique de ta part pour le prévenir,
    <a href="emails/broken/warn/{$email}?token={xsrf_token}">clique sur ce lien</a>.
  </p>
  <p>
    Par ailleurs, si tu connais une autre adresse email où le contacter, nous pouvons l'inviter à mettre à jour sa redirection
    Polytechnique.org. Pour ceci il suffit que tu remplisses <a href="marketing/broken/{$user.hruid}">ce formulaire</a>.
  </p>
{elseif $user}
<h2>Patte cassée</h2>
  <p>
    Désolé, mais ton correspondant, {$user.full_name},
    n'a actuellement <span class="erreur">aucune adresse email de redirection
      active autre que celle que tu viens de rentrer.</span>
    Nous t'invitons à prendre contact avec lui autrement que par email,
    l'idéal étant de l'informer si possible que sa patte Polytechnique.org est cassée&nbsp;!
  </p>
  <p>
    Si tu connais une autre adresse email où le contacter, nous pouvons l'inviter à mettre à jour sa redirection
    Polytechnique.org. Pour ceci il suffit que tu remplisses <a href="marketing/broken/{$user.hruid}">ce formulaire</a>.
  </p>
{elseif $email}
<p class="erreur">
  Désolé mais plus personne n'utilise l'adresse {$email} comme adresse de redirection.
  Nous ne pouvons donc malheureusement te fournir aucune information&hellip;
</p>
{/if}
<h2>Signaler une redirection en panne</h2>
<p>
  Lors de l'envoi d'un email à l'un de nos camarades, il se peut que tu reçoives
  en retour un message d'erreur t'informant que l'email n'avait pas pu être livré
  à une de ses redirections. Or un camarade peut avoir plusieurs redirections, et
  le destinataire de ton email peut donc l'avoir reçu sur une autre de ses redirections.
</p>
<p>
  Pour signaler que l'adresse pour laquelle tu as reçu une erreur est en panne, et
  pour savoir si ton correspondant a reçu l'email sur une autre redirection, il te
  suffit d'indiquer l'addresse email incriminée ci-dessous et que cliquer sur «&nbsp;Ok&nbsp;».
</p>
<div class="center">
  <form action="emails/broken" method="post">
  {xsrf_token_field}
  <table class="tinybicol" cellpadding="3" summary="Saisie email en panne" style="margin: auto">
    <tr>
      <th>Adresse email défectueuse</th>
    </tr>
    <tr>
      <td class='center'><input type="text" name="email" size="60" /></td>
    </tr>
    <tr>
      <td class="center"><input type="submit" value="Ok" /></td>
    </tr>
  </table>
  </form>
</div>

{include wiki=Xorg.PatteCassee}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
