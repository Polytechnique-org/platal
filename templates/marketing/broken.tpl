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


{if $sent}

<p>
  Merci de nous avoir communiqué cette information !
</p>

{elseif $user}

<h1>
  Recherche d'adresses pour {$user.nom} {$user.prenom} (X{$user.promo})
</h1>

<p>
  Avec le temps toutes les adresses de redirection de notre camarade sont devenues invalides et produisent
  des erreurs lorsqu'on lui envoie un mail. Nous sommes donc à la recherche d'adresses valides où nous pourrions
  contacter ce camarade.
</p>
<p>
  Les adresses emails que tu pourras nous donner ne seront pas ajoutées directement aux redirections de {$user.prenom}.
</p>
<p>
  Merci de participer à cette recherche.
</p>

<form method="post" action="{$platal->path}">
  <table class="bicol" summary="Fiche camarade">
    <tr class="impair"><td>Nom :</td><td>{$user.nom}</td></tr>
    <tr class="pair"><td>Prénom :</td><td>{$user.prenom}</td></tr>
    <tr class="impair"><td>Promo :</td><td>{$user.promo}</td></tr>
    <tr class="pair">
      <td>Adresse email :</td>
      <td>
        <input type="text" name="mail" size="30" maxlength="50" />
      </td>
    </tr>
  </table>
  <div class="center">
    <input type="submit" name="valide" value="Valider" />
  </div>
</form>
{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
