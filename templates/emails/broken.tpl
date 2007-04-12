{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

<h1>Indiquer</h1>

{if $neuneu}
<h2 class='erreur'>Erreur !</h2>

<p>
Tu as entré une adresse @polytechnique.org, @m4x.org ou @melix, ce qui est invalide.
</p>

<p>
En effet, il faut nous donner l'adresse qui se cache derrière l'adresse polytechnicienne de ton
correspondant si tu veux que nous puissions te répondre.
</p>
{elseif $x && $x.nb_mails}
<h2>Patte Cassée</h2>
  <p>
    Ton correspondant a à l'heure actuelle <span class="erreur">{$x.nb_mails} adresse(s) email(s) de redirection active(s)
    en dehors de celle que tu nous as communiquée</span>. Cela ne veut pas forcément dire qu'il les avait
    déjà activées lorsque tu as envoyé ton email, mais c'est fort probable.
  </p>
  <p>
    Nous pensons qu'il serait une bonne idée de le prévenir que cette adresse email ne fonctionne plus.
    Si tu veux que nous lui envoyions un mail automatique de ta part pour le prévenir,
    <a href="emails/broken/warn/{$email}">clique sur ce lien</a>.
  </p>
{elseif $x}
<h2>Patte Cassée</h2>
  <p>
    Désolé, mais ton correspondant, {$x.prenom} {$x.nom} (X{$x.promo}),
    n'a actuellement <span class="erreur">aucune adresse email de redirection 
      active autre que celle que tu viens de rentrer.</span>
    Nous t'invitons à prendre contact avec lui autrement que par email,
    l'idéal étant de l'informer si possible que sa patte Polytechnique.org est cassée...!
  </p>
  <p>
    Si tu connais une autre adresse email où le contacter, nous pouvons l'inviter à mettre à jour sa redirection
    Polytechnique.org. Pour ceci il suffit que tu remplisses <a href="marketing/broken/{$x.forlife}">ce fomulaire</a>.
  </p>
{elseif $email}
<p class="erreur">
  Désolé mais plus personne n'utilise l'adresse {$email} comme adresse de redirection.
  Nous ne pouvons donc malheureusement te fournir aucune information...
</p>
{/if}
<br />
<div class="center">
  <form action="emails/broken" method="post">
  <table class="tinybicol" cellpadding="3" summary="Saisie email en panne">
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

{include file=../spool/wiki.d/cache_Xorg.PatteCassée.tpl included=1}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
