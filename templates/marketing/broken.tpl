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


{if $sent}

<p>
  Merci de nous avoir communiqué cette information !
</p>

{elseif $user}

<h1>
  Recherche d'adresses pour {$user.nom} {$user.prenom} (X{$user.promo})
</h1>

{if !$user.email}
<p>
  Avec le temps, toutes les adresses de redirection de notre camarade sont devenues invalides et produisent
  des erreurs lorsqu'on lui envoie un mail. Nous sommes donc à la recherche d'adresses valides où nous pourrions
  contacter ce camarade.
</p>
{elseif $user.last}
<p>
  {$user.prenom} a encore des adresses de redirection actives malgré des pannes détectées sur certaines d'entre elles. Si
  tu es sûr{if $smarty.session.femme}e{/if} que son adresse Polytechnique.org est en panne, tu peux proposer une nouvelle
  adresse mail à ajouter à ses redirections. Merci d'ajouter un commentaire pour nous indiquer la raison de cette proposition.
</p>
{else}
<p>
  Nous n'avons actuellement enregistré aucune panne sur les adresses de redirection de {$user.prenom}. Si tu es 
  sûr{if $smarty.session.femme}e{/if} que son adresse de redirection actuelle est en panne, tu peux nous proposer
  une nouvelle adresse, accompagnée d'un commentaire nous expliquant les raisons exactes de cette proposition.
</p>
{/if}
<p>
  Les adresses email que tu pourras nous donner ne seront pas ajoutées directement aux redirections de {$user.prenom}.
  Nous allons d'abord prendre contact avec {if $user.sexe}elle{else}lui{/if} pour savoir {if $user.sexe}si elle{else}s'il{/if}
  accepte la mise à jour de sa redirection.
</p>
<p>
  Merci de ta participation active à l'amélioration de notre qualité de service.
</p>

<form method="post" action="{$platal->path}">
  <table class="bicol" summary="Fiche camarade">
    <tr><th colspan="2">Proposition d'adresse pour<br />{$user.nom} {$user.prenom} (X{$user.promo})</th></tr>
    <tr class="pair">
      <td>Adresse email :</td>
      <td>
        <input type="text" name="mail" size="30" maxlength="50" value="{$smarty.post.mail}" />
      </td>
    </tr>
    {if $user.email}
    <tr class="impair">
      <td>Explication :</td>
      <td><textarea name="comment" cols="50" rows="4">{$smarty.post.comment}</textarea></td>
    </tr>
    {/if}
  </table>
  <div class="center">
    <input type="submit" name="valide" value="Valider" />
  </div>
</form>
{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
